<?php

namespace Database\Seeders;

use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Tarih aralığı: 1 hafta önce - bugün
        $startDate = Carbon::now()->subWeek();
        $endDate = Carbon::now();

        // Müşteri ID'leri (CustomerSeeder'dan)
        $customerIds = [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 27, 28, 29, 33, 34, 35];

        // Ürün ID'leri ve fiyatları
        $productPrices = [
            2 => 18.50,   // Öz Ergül Plastik Büyük Boy
            3 => 15.00,   // Öz Ergül Plastik Orta Boy
            4 => 12.50,   // Öz Ergül Plastik Küçük Boy
            5 => 10.00,   // Öz Ergül Plastik Mini Boy
            6 => 16.00,   // M&R Büyük Boy
            7 => 13.50,   // M&R Orta Boy
            8 => 11.00,   // M&R Küçük Boy
            9 => 9.50,    // M&R Mini Boy
            10 => 22.00,  // Lüx Öz Ergül Küçük Boy
            11 => 25.00,  // Lüx Öz Ergül Orta Boy
            12 => 28.00,  // Lüx Öz Ergül Büyük Boy
            13 => 20.00,  // Lüx Öz Ergül Mini Boy
            14 => 85.00,  // Lüx Öz Ergül Küçük Boy Adetli (300 adet)
            15 => 92.00,  // Lüx Öz Ergül Orta Boy Adetli (200 adet)
            16 => 98.00,  // Lüx Öz Ergül Büyük Boy Adetli (150 adet)
            17 => 75.00,  // Öz Ergül Küçük Boy Adetli (300 adet)
            18 => 82.00,  // Öz Ergül Orta Boy Adetli (200 adet)
            19 => 88.00,  // Öz Ergül Büyük Boy Adetli (150 adet)
            20 => 14.00,  // New Plast Büyük Boy
            21 => 11.50,  // New Plast Orta Boy
            22 => 9.00,   // New Plast Küçük Boy
            23 => 8.50,   // Kelebek Büyük Boy 500gr
            24 => 7.00,   // Kelebek Küçük Boy 500gr
            25 => 7.50,   // Kelebek Orta Boy 500gr
        ];

        $paymentTypes = ['pesin', 'borc', 'kismi'];
        $sales = [];
        $salesProducts = [];
        $saleId = 1;

        // Her gün için satış verileri oluştur
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Hafta sonu daha az satış
            $isWeekend = $date->isWeekend();
            $dailySalesCount = $isWeekend ? rand(1, 2) : rand(2, 4);

            for ($i = 0; $i < $dailySalesCount; $i++) {
                $customerId = $customerIds[array_rand($customerIds)];
                $paymentType = $paymentTypes[array_rand($paymentTypes)];

                // Satış kaydı oluştur
                $saleDateTime = $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59));

                // Her satışta 1-5 farklı ürün
                $productCount = rand(1, 5);
                $productIds = array_rand($productPrices, $productCount);
                if (!is_array($productIds)) {
                    $productIds = [$productIds];
                }

                $totalAmount = 0;
                $saleProducts = [];

                // Ürün detayları
                foreach ($productIds as $productId) {
                    $quantity = $this->getSalesQuantity($productId);
                    $price = $productPrices[$productId];
                    $productTotal = $quantity * $price;
                    $totalAmount += $productTotal;

                    $saleProducts[] = [
                        'sales_id' => $saleId,  // sales_id kullan
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'created_at' => $saleDateTime,
                        'updated_at' => $saleDateTime,
                    ];
                }

                // Ödeme miktarını hesapla
                $paidAmount = $this->calculatePaidAmount($paymentType, $totalAmount);

                $sales[] = [
                    'id' => $saleId,
                    'customer_id' => $customerId,
                    'payment_type' => $paymentType,
                    'paid_amount' => $paidAmount,
                    'sale_date' => $date->toDateString(),
                    'created_at' => $saleDateTime,
                    'updated_at' => $saleDateTime,
                ];

                $salesProducts = array_merge($salesProducts, $saleProducts);
                $saleId++;
            }
        }

        // Batch insert for performance
        foreach (array_chunk($sales, 100) as $chunk) {
            Sales::insert($chunk);
        }

        foreach (array_chunk($salesProducts, 100) as $chunk) {
            SalesProduct::insert($chunk);
        }

        echo "✅ " . count($sales) . " satış kaydı eklendi\n";
        echo "✅ " . count($salesProducts) . " satış ürün detayı eklendi\n";
    }

    /**
     * Ürün ID'sine göre gerçekçi satış miktarı döndür
     */
    private function getSalesQuantity($productId)
    {
        switch ($productId) {
            case 2: case 6: case 12: case 20: // Büyük boy
            return rand(50, 200);

            case 3: case 7: case 11: case 21: // Orta boy
            return rand(80, 300);

            case 4: case 8: case 10: case 22: // Küçük boy
            return rand(100, 400);

            case 5: case 9: case 13: // Mini boy
            return rand(150, 500);

            case 14: case 17: // Adetli 300
            return rand(5, 25);

            case 15: case 18: // Adetli 200
            return rand(8, 30);

            case 16: case 19: // Adetli 150
            return rand(10, 35);

            case 23: case 24: case 25: // Kelebek 500gr
            return rand(20, 100);

            default:
                return rand(50, 250);
        }
    }

    /**
     * Ödeme tipine göre ödenen miktarı hesapla
     */
    private function calculatePaidAmount($paymentType, $totalAmount)
    {
        switch ($paymentType) {
            case 'pesin':
                return $totalAmount; // Tam ödeme

            case 'borc':
                return 0; // Borç

            case 'kismi':
                return round($totalAmount * (rand(30, 70) / 100), 2); // %30-70 arası ödeme

            default:
                return $totalAmount;
        }
    }
}
