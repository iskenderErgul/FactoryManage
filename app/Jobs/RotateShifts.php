<?php

namespace App\Jobs;

use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftAssignment;
use App\Domains\Shift\Models\ShiftTemplate;
use App\Domains\Users\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RotateShifts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function handle()
    {
        try {
            Log::info('Vardiya rotasyonu başlatıldı');

            // Yeni hafta için shift'leri oluştur
            $this->createWeeklyShifts();

            // Kullanıcıları otomatik ata
            $this->assignUsersToShifts();

            Log::info('Vardiya rotasyonu tamamlandı');
        } catch (\Exception $e) {
            Log::error('Vardiya rotasyonu hatası: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createWeeklyShifts()
    {
        $today = Carbon::now();
        $mondayOfWeek = $today->copy()->startOfWeek(); // Pazartesi

        // Bu hafta için shift'ler var mı kontrol et
        $existingShifts = Shift::whereBetween('date', [
            $mondayOfWeek->format('Y-m-d'),
            $mondayOfWeek->copy()->endOfWeek()->format('Y-m-d')
        ])->exists();

        if ($existingShifts) {
            Log::info('Bu hafta için shift\'ler zaten mevcut');
            return;
        }

        // Tüm shift template'lerini al (sıralı olarak)
        $shiftTemplates = ShiftTemplate::orderBy('id')->get();

        if ($shiftTemplates->isEmpty()) {
            Log::warning('Hiç shift template bulunamadı');
            return;
        }

        // Haftanın her günü için shift'ler oluştur
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $mondayOfWeek->copy()->addDays($i);

            foreach ($shiftTemplates as $template) {
                Shift::create([
                    'template_id' => $template->id,
                    'date' => $currentDate->format('Y-m-d'),
                ]);
            }
        }

        Log::info("Hafta için shift'ler oluşturuldu: " . $mondayOfWeek->format('Y-m-d'));
    }

    private function assignUsersToShifts()
    {
        $today = Carbon::now();
        $mondayOfWeek = $today->copy()->startOfWeek();

        // Bu haftanın shift'lerini al
        $weeklyShifts = Shift::whereBetween('date', [
            $mondayOfWeek->format('Y-m-d'),
            $mondayOfWeek->copy()->endOfWeek()->format('Y-m-d')
        ])->with('template')->orderBy('date')->orderBy('template_id')->get();

        if ($weeklyShifts->isEmpty()) {
            Log::warning('Bu hafta için shift bulunamadı');
            return;
        }

        // Tüm kullanıcıları al
        $users = User::all();

        if ($users->isEmpty()) {
            Log::warning('Atanacak kullanıcı bulunamadı');
            return;
        }

        // Shift template'lerini sıralı al
        $shiftTemplates = ShiftTemplate::orderBy('id')->get();
        $templateCount = $shiftTemplates->count();

        if ($templateCount === 0) {
            Log::warning('Hiç shift template bulunamadı');
            return;
        }

        foreach ($users as $user) {
            // Bu hafta için kullanıcının ataması var mı kontrol et
            $existingWeekAssignment = ShiftAssignment::whereHas('shift', function($query) use ($mondayOfWeek) {
                $query->whereBetween('date', [
                    $mondayOfWeek->format('Y-m-d'),
                    $mondayOfWeek->copy()->endOfWeek()->format('Y-m-d')
                ]);
            })->where('user_id', $user->id)->exists();

            if ($existingWeekAssignment) {
                Log::info("Kullanıcı {$user->id} bu hafta için zaten atanmış");
                continue; // Bu hafta için zaten atanmış
            }

            // Kullanıcının önceki hafta vardiya geçmişini al
            $previousWeekAssignment = $this->getUserLastWeekShift($user->id);

            // Bir sonraki vardiya template'ini hesapla
            $nextTemplateIndex = $this->calculateNextShiftTemplate(
                $previousWeekAssignment,
                $templateCount
            );

            $targetTemplate = $shiftTemplates[$nextTemplateIndex];

            Log::info("Kullanıcı {$user->id} için hedef template: {$targetTemplate->name} (index: {$nextTemplateIndex})");

            // Bu haftanın her günü için aynı vardiyaya ata
            $dailyShifts = $weeklyShifts->groupBy('date');

            foreach ($dailyShifts as $date => $shiftsForDay) {
                // Doğru template'e ait shift'i bul
                $targetShift = $shiftsForDay->where('template_id', $targetTemplate->id)->first();

                if ($targetShift) {
                    // Çakışma kontrolü (aynı gün başka vardiya)
                    if (!$this->hasShiftConflict($user->id, $date, $targetTemplate->start_time, $targetTemplate->end_time)) {
                        ShiftAssignment::create([
                            'user_id' => $user->id,
                            'shift_id' => $targetShift->id,
                        ]);

                        Log::info("Kullanıcı {$user->id} - {$targetTemplate->name} vardiyasına atandı ({$date})");
                    } else {
                        Log::warning("Kullanıcı {$user->id} için {$date} tarihinde çakışma var");
                    }
                }
            }
        }
    }

    private function getUserLastWeekShift($userId)
    {
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        return ShiftAssignment::whereHas('shift', function($query) use ($lastWeekStart, $lastWeekEnd) {
            $query->whereBetween('date', [$lastWeekStart->format('Y-m-d'), $lastWeekEnd->format('Y-m-d')]);
        })
            ->where('user_id', $userId)
            ->with('shift.template')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function calculateNextShiftTemplate($previousAssignment, $templateCount)
    {
        if (!$previousAssignment) {
            // İlk atama - ilk template'den başla
            return 0;
        }

        // Önceki template'in sırasını bul
        $shiftTemplates = ShiftTemplate::orderBy('id')->get();
        $previousTemplateIndex = $shiftTemplates->search(function($template) use ($previousAssignment) {
            return $template->id === $previousAssignment->shift->template->id;
        });

        if ($previousTemplateIndex === false) {
            return 0; // Bulunamazsa baştan başla
        }

        // Sıradaki template'e geç (döngüsel)
        return ($previousTemplateIndex + 1) % $templateCount;
    }

    private function hasShiftConflict($userId, $date, $startTime, $endTime): bool
    {
        return ShiftAssignment::where('user_id', $userId)
            ->whereHas('shift', function ($query) use ($date, $startTime, $endTime) {
                $query->where('date', $date)
                    ->whereHas('template', function ($q) use ($startTime, $endTime) {
                        $q->where(function ($subQuery) use ($startTime, $endTime) {
                            $subQuery->whereBetween('start_time', [$startTime, $endTime])
                                ->orWhere(function ($q) use ($startTime, $endTime) {
                                    $q->where('start_time', '<=', $startTime)
                                        ->where('end_time', '>=', $endTime);
                                });
                        });
                    });
            })
            ->exists();
    }
}
