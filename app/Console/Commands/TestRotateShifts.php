<?php

namespace App\Console\Commands;

use App\Jobs\RotateShifts;
use Illuminate\Console\Command;

class TestRotateShifts extends Command
{
    protected $signature = 'shift:test-rotate {--force : Zorla çalıştır}';
    protected $description = 'Vardiya rotasyon job\'unu test eder';

    public function handle()
    {
        $this->info('🔄 Vardiya rotasyon job\'u test ediliyor...');

        if ($this->option('force')) {
            $this->warn('⚠️  Force modu aktif - mevcut atamalar üzerine yazılabilir');
        }

        try {
            // Job'u senkron olarak çalıştır
            $job = new RotateShifts();
            $job->handle();

            $this->info('✅ Job başarıyla tamamlandı!');

            // Sonuçları göster
            $this->showResults();

        } catch (\Exception $e) {
            $this->error('❌ Job çalışırken hata oluştu:');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    private function showResults()
    {
        $this->info('📊 Sonuçlar:');

        // Bu haftanın shift'lerini göster
        $today = now();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $shifts = \App\Domains\Shift\Models\Shift::whereBetween('date', [
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        ])->with(['template', 'shiftAssignments.user'])->get();

        // Haftalık rotasyon tablosu
        $this->info('📅 Haftalık Rotasyon Tablosu:');
        $this->info('Tarih Aralığı: ' . $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y'));

        // Kullanıcı bazında haftalık atama
        $users = \App\Domains\Users\Models\User::all();
        $weeklyUserAssignments = [];

        foreach ($users as $user) {
            $userAssignments = \App\Domains\Shift\Models\ShiftAssignment::whereHas('shift', function($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')]);
            })
                ->where('user_id', $user->id)
                ->with('shift.template')
                ->get();

            if ($userAssignments->isNotEmpty()) {
                $shiftNames = $userAssignments->pluck('shift.template.name')->unique();
                $weeklyUserAssignments[] = [
                    'Kullanıcı' => $user->name,
                    'Bu Hafta Vardiya' => $shiftNames->join(', '),
                    'Toplam Gün' => $userAssignments->count(),
                    'Saatler' => $userAssignments->first()->shift->template->start_time . ' - ' . $userAssignments->first()->shift->template->end_time
                ];
            } else {
                $weeklyUserAssignments[] = [
                    'Kullanıcı' => $user->name,
                    'Bu Hafta Vardiya' => 'Atanmamış',
                    'Toplam Gün' => 0,
                    'Saatler' => '-'
                ];
            }
        }

        $this->table(
            ['Kullanıcı', 'Bu Hafta Vardiya', 'Toplam Gün', 'Saatler'],
            $weeklyUserAssignments
        );

        // Günlük detay (sadece atama varsa)
        $dailyDetails = [];
        foreach ($shifts->groupBy('date') as $date => $dayShifts) {
            foreach ($dayShifts as $shift) {
                if ($shift->shiftAssignments->count() > 0) {
                    $dailyDetails[] = [
                        'Tarih' => \Carbon\Carbon::parse($date)->format('d/m/Y l'),
                        'Vardiya' => $shift->template->name,
                        'Saat' => $shift->template->start_time . ' - ' . $shift->template->end_time,
                        'Atananlar' => $shift->shiftAssignments->pluck('user.name')->join(', ')
                    ];
                }
            }
        }

        if (!empty($dailyDetails)) {
            $this->info('📋 Günlük Detaylar:');
            $this->table(
                ['Tarih', 'Vardiya', 'Saat', 'Atananlar'],
                $dailyDetails
            );
        }

        // Özet bilgiler
        $totalShifts = $shifts->count();
        $assignedShifts = $shifts->filter(fn($s) => $s->shiftAssignments->count() > 0)->count();
        $totalAssignments = $shifts->sum(fn($s) => $s->shiftAssignments->count());
        $totalUsers = \App\Domains\Users\Models\User::count();
        $templatesCount = \App\Domains\Shift\Models\ShiftTemplate::count();

        $this->info("📈 Özet:");
        $this->info("   Toplam Kullanıcı: {$totalUsers}");
        $this->info("   Toplam Template: {$templatesCount}");
        $this->info("   Bu Hafta Toplam Vardiya: {$totalShifts}");
        $this->info("   Atama Yapılan Vardiya: {$assignedShifts}");
        $this->info("   Toplam Atama: {$totalAssignments}");

        // Rotasyon kontrolü
        $this->info("🔄 Rotasyon Kontrolü:");
        foreach ($users as $user) {
            $lastWeek = $this->getUserPreviousWeekShift($user->id);
            $thisWeek = $this->getUserThisWeekShift($user->id);

            $lastWeekShift = $lastWeek ? $lastWeek->shift->template->name : 'Yok';
            $thisWeekShift = $thisWeek ? $thisWeek->shift->template->name : 'Yok';

            $this->info("   {$user->name}: {$lastWeekShift} → {$thisWeekShift}");
        }
    }

    private function getUserPreviousWeekShift($userId)
    {
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        return \App\Domains\Shift\Models\ShiftAssignment::whereHas('shift', function($query) use ($lastWeekStart, $lastWeekEnd) {
            $query->whereBetween('date', [$lastWeekStart->format('Y-m-d'), $lastWeekEnd->format('Y-m-d')]);
        })
            ->where('user_id', $userId)
            ->with('shift.template')
            ->first();
    }

    private function getUserThisWeekShift($userId)
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return \App\Domains\Shift\Models\ShiftAssignment::whereHas('shift', function($query) use ($weekStart, $weekEnd) {
            $query->whereBetween('date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')]);
        })
            ->where('user_id', $userId)
            ->with('shift.template')
            ->first();
    }
}
