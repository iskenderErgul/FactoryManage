<?php

namespace Database\Seeders;

use App\Domains\Production\Models\Production;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Tarih aralığı: 2 hafta önce - 2 hafta sonra (toplam 4 hafta)
        $startDate = Carbon::now()->subWeeks(2);
        $endDate = Carbon::now()->addWeeks(2);

        // Ürün ID'leri (SQL'den aldığım)
        $productIds = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];

        // Makine ID'leri
        $machineIds = [1, 2]; // Makine A ve Makine B

        // Kullanıcı ID'leri (işçiler)
        $userIds = [1, 2]; // Admin ve Worker

        $productions = [];

        // Her gün için üretim verileri oluştur
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $today = Carbon::now()->startOfDay();
            $isFuture = $date->gt($today);
            $isWeekend = $date->isWeekend();
            
            // Hafta sonu daha az üretim, gelecek için planlı üretim
            if ($isFuture) {
                // Gelecek için planlı üretim (hafta içi daha fazla)
                $dailyProductionCount = $isWeekend ? rand(1, 2) : rand(3, 5);
            } else {
                // Geçmiş için gerçekleşen üretim
                $dailyProductionCount = $isWeekend ? rand(1, 2) : rand(2, 4);
            }

            for ($i = 0; $i < $dailyProductionCount; $i++) {
                // Rastgele değerler seç
                $productId = $productIds[array_rand($productIds)];
                $machineId = $machineIds[array_rand($machineIds)];
                $userId = $userIds[array_rand($userIds)];

                // Ürün tipine göre üretim miktarları
                $quantity = $this->getProductionQuantity($productId, $isFuture);

                // Gelecek tarihler için created_at ve updated_at'i bugün yap
                if ($isFuture) {
                    $createdAt = $today->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59));
                    $updatedAt = $createdAt->copy();
                } else {
                    $createdAt = $date->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59));
                    $updatedAt = $date->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59));
                }

                $productions[] = [
                    'user_id' => $userId,
                    'machine_id' => $machineId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'shift_id' => 1, // Şimdilik default 1
                    'production_date' => $date->toDateString(),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }
        }

        // Batch insert for performance
        foreach (array_chunk($productions, 100) as $chunk) {
            Production::insert($chunk);
        }

        echo "✅ " . count($productions) . " üretim kaydı eklendi\n";
    }

    /**
     * Ürün ID'sine göre gerçekçi üretim miktarı döndür
     */
    private function getProductionQuantity($productId, $isFuture = false)
    {
        // Gelecek planlama için %10-20 daha yüksek miktarlar
        $multiplier = $isFuture ? rand(110, 120) / 100 : 1;
        
        // Ürün tipine göre farklı üretim miktarları
        switch ($productId) {
            case 2: // Öz Ergül Plastik Büyük Boy
            case 6: // M&R Büyük Boy
            case 12: // Lüx Öz Ergül Büyük Boy
            case 20: // New Plast Büyük Boy
                $base = rand(800, 1500); // Büyük boy ürünler
                return (int)($base * $multiplier);

            case 3: // Öz Ergül Plastik Orta Boy
            case 7: // M&R Orta Boy
            case 11: // Lüx Öz Ergül Orta Boy
            case 21: // New Plast Orta Boy
                $base = rand(1000, 1800); // Orta boy ürünler
                return (int)($base * $multiplier);

            case 4: // Öz Ergül Plastik Küçük Boy
            case 8: // M&R Küçük Boy
            case 10: // Lüx Öz Ergül Küçük Boy
            case 22: // New Plast Küçük Boy
                $base = rand(1200, 2000); // Küçük boy ürünler
                return (int)($base * $multiplier);

            case 5: // Mini boy ürünler
            case 9:
            case 13:
                $base = rand(1500, 2500);
                return (int)($base * $multiplier);

            case 14: // Adetli ürünler (300 adet)
            case 17:
                $base = rand(50, 150); // Paket bazında
                return (int)($base * $multiplier);

            case 15: // Adetli ürünler (200 adet)
            case 18:
                $base = rand(80, 200);
                return (int)($base * $multiplier);

            case 16: // Adetli ürünler (150 adet)
            case 19:
                $base = rand(100, 250);
                return (int)($base * $multiplier);

            case 23: // Kelebek serisi (500gr)
            case 24:
            case 25:
                $base = rand(300, 800);
                return (int)($base * $multiplier);

            default:
                $base = rand(500, 1200);
                return (int)($base * $multiplier);
        }
    }
}
