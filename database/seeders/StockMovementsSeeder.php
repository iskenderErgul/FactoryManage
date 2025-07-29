<?php

namespace Database\Seeders;

use App\Common\Models\StockMovement;
use App\Domains\Production\Models\Production;
use App\Domains\Sales\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockMovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $stockMovements = [];

        // 1. Üretimlerden gelen stok giriş hareketleri
        $productions = Production::with(['product'])
            ->whereBetween('production_date', [
                Carbon::now()->subMonth(),
                Carbon::now()->addMonth()
            ])
            ->get();

        foreach ($productions as $production) {
            $stockMovements[] = [
                'product_id' => $production->product_id,
                'movement_type' => 'giriş',
                'quantity' => $production->quantity,
                'related_process' => 'Üretim - ' . $production->product->product_name,
                'movement_date' => $production->production_date,
                'created_at' => $production->created_at,
                'updated_at' => $production->updated_at,
            ];
        }

        // 2. Satışlardan gelen stok çıkış hareketleri
        $salesProducts = SalesProduct::with(['product', 'sale'])
            ->whereHas('sale', function($query) {
                $query->whereBetween('sale_date', [
                    Carbon::now()->subMonth(),
                    Carbon::now()->addMonth()
                ]);
            })
            ->get();

        foreach ($salesProducts as $salesProduct) {
            $stockMovements[] = [
                'product_id' => $salesProduct->product_id,
                'movement_type' => 'çıkış',
                'quantity' => $salesProduct->quantity,
                'related_process' => 'Satış - ' . $salesProduct->product->product_name,
                'movement_date' => $salesProduct->sale->sale_date,
                'created_at' => $salesProduct->created_at,
                'updated_at' => $salesProduct->updated_at,
            ];
        }

        // 3. Ek stok hareketleri (fire, ayar, transfer vb.)
        $this->addAdditionalMovements($stockMovements);

        // Batch insert for performance
        foreach (array_chunk($stockMovements, 100) as $chunk) {
            StockMovement::insert($chunk);
        }

        echo "✅ " . count($stockMovements) . " stok hareketi eklendi\n";
    }

    /**
     * Ek stok hareketleri ekle (fire, ayar, transfer vb.)
     */
    private function addAdditionalMovements(&$stockMovements)
    {
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now()->addMonth();
        $productIds = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];

        // Her hafta birkaç ek hareket
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addWeek()) {
            $weeklyMovements = rand(3, 8);

            for ($i = 0; $i < $weeklyMovements; $i++) {
                $productId = $productIds[array_rand($productIds)];
                $movementType = rand(1, 10) <= 7 ? 'çıkış' : 'giriş'; // %70 çıkış, %30 giriş

                // Hareket tipine göre process ve miktar belirle
                [$process, $quantity] = $this->getMovementDetails($movementType, $productId);

                $movementDate = $date->copy()->addDays(rand(0, 6));

                $stockMovements[] = [
                    'product_id' => $productId,
                    'movement_type' => $movementType,
                    'quantity' => $quantity,
                    'related_process' => $process,
                    'movement_date' => $movementDate->toDateString(),
                    'created_at' => $movementDate->addHours(rand(8, 17)),
                    'updated_at' => $movementDate->copy(),
                ];
            }
        }
    }

    /**
     * Hareket tipine göre process ve miktar detayları
     */
    private function getMovementDetails($movementType, $productId)
    {
        if ($movementType === 'giriş') {
            $processes = [
                'Stok Sayım Fazlası',
                'İade - Müşteri',
                'Transfer Giriş',
                'Üretim Düzeltme',
                'Kalite Kontrol Geçti'
            ];
            $quantity = $this->getAdjustmentQuantity($productId, 'giriş');
        } else {
            $processes = [
                'Fire',
                'Kalite Kontrol Red',
                'Hasar',
                'Numune',
                'Transfer Çıkış',
                'Stok Sayım Eksiği',
                'Promosyon'
            ];
            $quantity = $this->getAdjustmentQuantity($productId, 'çıkış');
        }

        $process = $processes[array_rand($processes)];

        return [$process, $quantity];
    }

    /**
     * Ürün ve hareket tipine göre miktar hesapla
     */
    private function getAdjustmentQuantity($productId, $movementType)
    {
        // Temel miktarlar
        $baseQuantities = [
            'giriş' => [
                'büyük' => rand(20, 100),
                'orta' => rand(30, 150),
                'küçük' => rand(50, 200),
                'mini' => rand(80, 300),
                'adetli' => rand(2, 15),
                'kelebek' => rand(10, 50)
            ],
            'çıkış' => [
                'büyük' => rand(10, 50),
                'orta' => rand(15, 80),
                'küçük' => rand(25, 100),
                'mini' => rand(40, 150),
                'adetli' => rand(1, 8),
                'kelebek' => rand(5, 25)
            ]
        ];

        // Ürün tipini belirle
        if (in_array($productId, [2, 6, 12, 16, 19, 20, 23])) { // Büyük boy
            return $baseQuantities[$movementType]['büyük'];
        } elseif (in_array($productId, [3, 7, 11, 15, 18, 21, 25])) { // Orta boy
            return $baseQuantities[$movementType]['orta'];
        } elseif (in_array($productId, [4, 8, 10, 14, 17, 22, 24])) { // Küçük boy
            return $baseQuantities[$movementType]['küçük'];
        } elseif (in_array($productId, [5, 9, 13])) { // Mini boy
            return $baseQuantities[$movementType]['mini'];
        } elseif (in_array($productId, [14, 15, 16, 17, 18, 19])) { // Adetli
            return $baseQuantities[$movementType]['adetli'];
        } else { // Kelebek serisi
            return $baseQuantities[$movementType]['kelebek'];
        }
    }
}
