<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\Transaction;
use App\Domains\Product\Models\Product;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Mevcut test verilerini temizle (opsiyonel)
        // Sales::truncate();
        // SalesProduct::truncate();
        // Transaction::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Satış raporu test verileri oluşturuluyor...');

        // 1. Müşteriler oluştur (eğer yoksa)
        $this->command->info('👥 Müşteriler oluşturuluyor...');
        $customers = $this->createCustomers();

        // 2. Ürünler oluştur (eğer yoksa)
        $this->command->info('📦 Ürünler oluşturuluyor...');
        $products = $this->createProducts();

        // 3. Son 12 ay için satışlar oluştur
        $this->command->info('💰 Satışlar oluşturuluyor...');
        $this->createSales($customers, $products);

        // 4. Transaction kayıtları oluştur
        $this->command->info('💳 Ödeme kayıtları oluşturuluyor...');
        $this->createTransactions($customers);

        $this->command->info('✅ Test verileri başarıyla oluşturuldu!');
        $this->command->info('📊 Toplam Müşteri: ' . Customer::count());
        $this->command->info('📦 Toplam Ürün: ' . Product::count());
        $this->command->info('💰 Toplam Satış: ' . Sales::count());
        $this->command->info('🛒 Toplam Satış Kalemi: ' . SalesProduct::count());
        $this->command->info('💳 Toplam İşlem: ' . Transaction::count());
    }

    /**
     * Müşteriler oluştur
     */
    private function createCustomers()
    {
        $customerNames = [
            'Ahmet Yılmaz',
            'Mehmet Demir',
            'Ayşe Kaya',
            'Fatma Şahin',
            'Ali Çelik',
            'Zeynep Arslan',
            'Mustafa Öztürk',
            'Elif Yıldız',
            'Hüseyin Aydın',
            'Hatice Özdemir',
            'İbrahim Koç',
            'Merve Aksoy',
            'Osman Erdoğan',
            'Emine Yılmaz',
            'Ramazan Kurt',
        ];

        $customers = [];
        foreach ($customerNames as $name) {
            $customer = Customer::firstOrCreate(
                ['name' => $name],
                [
                    'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                    'phone' => '05' . rand(300000000, 599999999),
                    'address' => 'Test Adres, İstanbul',
                    'debt' => 0,
                ]
            );
            $customers[] = $customer;
        }

        return collect($customers);
    }

    /**
     * Ürünler oluştur
     */
    private function createProducts()
    {
        $productData = [
            ['name' => 'Plastik Kasa A', 'price' => 150.00],
            ['name' => 'Plastik Kasa B', 'price' => 200.00],
            ['name' => 'Plastik Kasa C', 'price' => 250.00],
            ['name' => 'Plastik Kutu Küçük', 'price' => 50.00],
            ['name' => 'Plastik Kutu Orta', 'price' => 75.00],
            ['name' => 'Plastik Kutu Büyük', 'price' => 100.00],
            ['name' => 'Plastik Sepet', 'price' => 120.00],
            ['name' => 'Plastik Kova', 'price' => 80.00],
            ['name' => 'Plastik Tabak Seti', 'price' => 45.00],
            ['name' => 'Plastik Bardak Seti', 'price' => 35.00],
            ['name' => 'Plastik Çatal Bıçak Seti', 'price' => 40.00],
            ['name' => 'Plastik Saklama Kabı', 'price' => 90.00],
            ['name' => 'Plastik Organizatör', 'price' => 110.00],
            ['name' => 'Plastik Çöp Kovası', 'price' => 130.00],
            ['name' => 'Plastik Lavabo', 'price' => 180.00],
        ];

        $products = [];
        foreach ($productData as $data) {
            $product = Product::firstOrCreate(
                ['product_name' => $data['name']],
                [
                    'product_type' => 'Plastik Ürün',
                    'stock_quantity' => rand(100, 1000),
                    'production_cost' => $data['price'],
                    'description' => 'Test ürün açıklaması',
                ]
            );
            $products[] = $product;
        }

        return collect($products);
    }

    /**
     * Son 12 ay için satışlar oluştur
     */
    private function createSales($customers, $products)
    {
        $paymentTypes = ['pesin', 'borc', 'kismi'];
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        $totalSales = 0;

        // Her ay için satışlar oluştur
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Her gün 2-8 arası satış oluştur
            $dailySalesCount = rand(2, 8);

            for ($i = 0; $i < $dailySalesCount; $i++) {
                $customer = $customers->random();
                $paymentType = $paymentTypes[array_rand($paymentTypes)];

                // Satış oluştur
                $sale = Sales::create([
                    'customer_id' => $customer->id,
                    'sale_date' => $date->format('Y-m-d'),
                    'payment_type' => $paymentType,
                    'paid_amount' => null,
                ]);

                // Her satışa 1-5 arası ürün ekle
                $productCount = rand(1, 5);
                $saleTotal = 0;

                for ($j = 0; $j < $productCount; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 10);
                    // Ürün fiyatını production_cost'tan al ve %10 indirim - %20 zam arası değişiklik yap
                    $basePrice = $product->production_cost ?? 100; // Varsayılan fiyat
                    $price = $basePrice * (1 + (rand(-10, 20) / 100));

                    SalesProduct::create([
                        'sales_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    $saleTotal += $quantity * $price;
                }

                // Kısmi ödeme için paid_amount belirle
                if ($paymentType === 'kismi') {
                    $sale->update([
                        'paid_amount' => $saleTotal * (rand(30, 70) / 100), // %30-70 arası ödeme
                    ]);
                }

                $totalSales++;
            }
        }

        $this->command->info("   ✓ {$totalSales} satış kaydı oluşturuldu");
    }

    /**
     * Transaction kayıtları oluştur
     */
    private function createTransactions($customers)
    {
        $totalTransactions = 0;

        foreach ($customers as $customer) {
            // Her müşteri için 5-15 arası transaction oluştur
            $transactionCount = rand(5, 15);

            for ($i = 0; $i < $transactionCount; $i++) {
                $type = rand(0, 1) === 0 ? 'borç' : 'ödeme';
                $amount = rand(100, 5000);
                $date = Carbon::now()->subDays(rand(1, 365));

                Transaction::create([
                    'customer_id' => $customer->id,
                    'type' => $type,
                    'amount' => $amount,
                    'date' => $date->format('Y-m-d'),
                    'description' => $type === 'borç' ? 'Satış borcu' : 'Borç ödemesi',
                ]);

                $totalTransactions++;
            }
        }

        $this->command->info("   ✓ {$totalTransactions} ödeme kaydı oluşturuldu");
    }
}
