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
        // Tarih aralığı: 1 ay önce - 1 ay sonra
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now()->addMonth();

        // Ürün ID'leri (SQL'den aldığım)
        $productIds = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];

        // Makine ID'leri
        $machineIds = [1, 2]; // Makine A ve Makine B

        // Kullanıcı ID'leri (işçiler)
        $userIds = [1, 2]; // Admin ve Worker

        $productions = [];

        // Her gün için üretim verileri oluştur
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Hafta sonu daha az üretim
            $isWeekend = $date->isWeekend();
            $dailyProductionCount = $isWeekend ? rand(3, 6) : rand(8, 15);

            for ($i = 0; $i < $dailyProductionCount; $i++) {
                // Rastgele değerler seç
                $productId = $productIds[array_rand($productIds)];
                $machineId = $machineIds[array_rand($machineIds)];
                $userId = $userIds[array_rand($userIds)];

                // Ürün tipine göre üretim miktarları
                $quantity = $this->getProductionQuantity($productId);

                $productions[] = [
                    'user_id' => $userId,
                    'machine_id' => $machineId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'shift_id' => 1, // Şimdilik default 1
                    'production_date' => $date->toDateString(),
                    'created_at' => $date->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
                    'updated_at' => $date->copy()->addHours(rand(8, 17))->addMinutes(rand(0, 59)),
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
    private function getProductionQuantity($productId)
    {
        // Ürün tipine göre farklı üretim miktarları
        switch ($productId) {
            case 2: // Öz Ergül Plastik Büyük Boy
            case 6: // M&R Büyük Boy
            case 12: // Lüx Öz Ergül Büyük Boy
            case 20: // New Plast Büyük Boy
                return rand(800, 1500); // Büyük boy ürünler

            case 3: // Öz Ergül Plastik Orta Boy
            case 7: // M&R Orta Boy
            case 11: // Lüx Öz Ergül Orta Boy
            case 21: // New Plast Orta Boy
                return rand(1000, 1800); // Orta boy ürünler

            case 4: // Öz Ergül Plastik Küçük Boy
            case 8: // M&R Küçük Boy
            case 10: // Lüx Öz Ergül Küçük Boy
            case 22: // New Plast Küçük Boy
                return rand(1200, 2000); // Küçük boy ürünler

            case 5: // Mini boy ürünler
            case 9:
            case 13:
                return rand(1500, 2500);

            case 14: // Adetli ürünler (300 adet)
            case 17:
                return rand(50, 150); // Paket bazında

            case 15: // Adetli ürünler (200 adet)
            case 18:
                return rand(80, 200);

            case 16: // Adetli ürünler (150 adet)
            case 19:
                return rand(100, 250);

            case 23: // Kelebek serisi (500gr)
            case 24:
            case 25:
                return rand(300, 800);

            default:
                return rand(500, 1200);
        }
    }
}
