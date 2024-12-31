<?php

namespace App\Jobs;

use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftAssignment;
use App\Domains\Users\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RotateShifts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;


    /**
     * İşin yürütülmesi.
     */
    public function handle()
    {
        // Bugün için vardiya atamaları
        $today = now()->toDateString();

        // Shift tablosundaki vardiyaları al
        $shifts = Shift::all();

        // Eğer vardiyalar yoksa bir şey yapmayalım
        if ($shifts->isEmpty()) {
            return;
        }

        // Shift ID'lerini sırayla almak için array_map kullanarak elde edelim
        $shiftIds = $shifts->pluck('id')->toArray();

        // Kullanıcıları al
        $users = User::all();

        foreach ($users as $user) {
            // Kullanıcının mevcut vardiyasını al
            $currentAssignment = ShiftAssignment::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();

            if ($currentAssignment) {
                // Mevcut vardiyanızın ID'sine göre bir sonraki vardiyaya geçiş yapın
                $currentShiftId = $currentAssignment->shift_id;

                // Bir sonraki vardiya ID'sini hesapla
                $nextShiftId = $this->getNextShiftId($currentShiftId, $shiftIds);

                // Kullanıcıyı yeni vardiyaya ata
                $currentAssignment->update([
                    'shift_id' => $nextShiftId,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function getNextShiftId(int $currentShiftId, array $shiftIds): int
    {
        // Mevcut vardiyaya göre bir sonraki vardiyayı döndür
        $currentIndex = array_search($currentShiftId, $shiftIds);

        if ($currentIndex === false) {
            // Eğer mevcut vardiya ID'si hatalıysa (ID yoksa), ilk vardiyaya dön
            return $shiftIds[0];
        }

        // Son vardiyadan sonra tekrar ilk vardiyaya dönülecek şekilde döngü oluştur
        $nextIndex = ($currentIndex + 1) % count($shiftIds);

        return $shiftIds[$nextIndex];
    }
}
