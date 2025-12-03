<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Domains\Users\Models\User;
use App\Domains\Machines\Models\Machine;
use App\Domains\Product\Models\Product;
use App\Domains\Customer\Models\Customer;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftAssignment;
use App\Domains\Shift\Models\ShiftTemplate;
use App\Domains\Production\Models\Production;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesProduct;
use App\Domains\Costs\Models\Cost;

class SystemWideSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Temizlik (İsteğe bağlı, şimdilik kapalı)
        // $this->truncateTables();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Kapsamlı sistem verileri oluşturuluyor...');

        // 1. Kullanıcılar (İşçiler)
        $this->command->info('👷 İşçiler oluşturuluyor...');
        $workers = $this->createWorkers();

        // 2. Makineler
        $this->command->info('🏭 Makineler oluşturuluyor...');
        $machines = $this->createMachines();

        // 3. Ürünler
        $this->command->info('📦 Ürünler oluşturuluyor...');
        $products = $this->createProducts();

        // 4. Müşteriler ve Tedarikçiler
        $this->command->info('🤝 Müşteri ve Tedarikçiler oluşturuluyor...');
        $customers = $this->createCustomers();
        $this->createSuppliers();

        // 5. Vardiyalar ve Üretim (Son 3 ay)
        $this->command->info('📅 Vardiya ve Üretim verileri işleniyor (Son 90 gün)...');
        $this->createShiftsAndProduction($workers, $machines, $products);

        // 6. Satışlar (Son 3 ay)
        $this->command->info('💰 Satış verileri oluşturuluyor...');
        $this->createSales($customers, $products);

        // 7. Maliyetler
        $this->command->info('💸 Maliyet kayıtları oluşturuluyor...');
        $this->createCosts();

        $this->command->info('✅ TÜM SİSTEM VERİLERİ BAŞARIYLA OLUŞTURULDU!');
    }

    private function createWorkers()
    {
        $workers = [];
        $names = ['Ahmet', 'Mehmet', 'Ali', 'Veli', 'Ayşe', 'Fatma', 'Zeynep', 'Mustafa', 'Kemal', 'Hüseyin', 'İsmail', 'Yusuf', 'Ömer', 'Murat', 'İbrahim'];
        
        foreach ($names as $name) {
            $user = User::firstOrCreate(
                ['email' => strtolower($name) . '@factory.com'],
                [
                    'name' => $name . ' Yılmaz',
                    'password' => Hash::make('password'),
                    'role' => 'worker',
                ]
            );
            $workers[] = $user;
        }
        return collect($workers);
    }

    private function createMachines()
    {
        $machines = [];
        for ($i = 1; $i <= 10; $i++) {
            $machine = Machine::firstOrCreate(
                ['machine_name' => 'Enjeksiyon ' . $i],
                [
                    // 'model' => '202' . rand(0, 5), // Machine modelinde yok
                    // 'serial_number' => 'SN-' . rand(1000, 9999), // Machine modelinde yok
                    // 'status' => 'active', // Machine modelinde yok
                    // 'location' => 'Üretim Hattı A' // Machine modelinde yok
                ]
            );
            $machines[] = $machine;
        }
        return collect($machines);
    }

    private function createProducts()
    {
        $products = [];
        $types = [
            ['name' => 'Plastik Kasa', 'price' => 150],
            ['name' => 'Saksı', 'price' => 45],
            ['name' => 'Boru Bağlantı', 'price' => 12],
            ['name' => 'Oyuncak Araba', 'price' => 85],
            ['name' => 'Mutfak Kabı', 'price' => 65],
            ['name' => 'Askı', 'price' => 15],
            ['name' => 'Tabure', 'price' => 120],
            ['name' => 'Kova', 'price' => 55],
        ];

        foreach ($types as $type) {
            $product = Product::firstOrCreate(
                ['product_name' => $type['name']],
                [
                    'product_type' => 'Plastik',
                    'stock_quantity' => rand(1000, 5000),
                    'production_cost' => $type['price'] * 0.6, // %40 kar marjı varsayımı
                    'description' => 'Otomatik oluşturulan ürün'
                ]
            );
            $products[] = $product;
        }
        return collect($products);
    }

    private function createCustomers()
    {
        $customers = [];
        for ($i = 1; $i <= 20; $i++) {
            $customer = Customer::firstOrCreate(
                ['name' => 'Müşteri Firma ' . $i],
                [
                    'email' => 'musteri' . $i . '@example.com',
                    'phone' => '0555' . rand(1000000, 9999999),
                    'address' => 'Organize Sanayi Bölgesi No:' . $i,
                    'debt' => 0
                ]
            );
            $customers[] = $customer;
        }
        return collect($customers);
    }

    private function createSuppliers()
    {
        for ($i = 1; $i <= 5; $i++) {
            Supplier::firstOrCreate(
                ['supplier_name' => 'Hammaddeci ' . $i],
                [
                    'supplier_email' => 'tedarik' . $i . '@supplier.com',
                    'supplier_phone' => '0532' . rand(1000000, 9999999),
                    'supplier_address' => 'Organize Sanayi Bölgesi No:' . $i
                ]
            );
        }
    }

    private function createShiftsAndProduction($workers, $machines, $products)
    {
        // Son 1 yıl için vardiya ve üretim verisi
        $startDate = Carbon::now()->subDays(90);
        $endDate = Carbon::now();

        // Vardiya şablonlarını al (yoksa oluştur)
        $shiftTemplates = ShiftTemplate::all();

        if ($shiftTemplates->isEmpty()) {
            $defaultTemplates = [
                ['name' => 'Sabah', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'duration' => 480],
                ['name' => 'Akşam', 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'duration' => 480],
                ['name' => 'Gece', 'start_time' => '00:00:00', 'end_time' => '08:00:00', 'duration' => 480],
            ];

            foreach ($defaultTemplates as $tpl) {
                $shiftTemplates->push(
                    ShiftTemplate::firstOrCreate(
                        ['name' => $tpl['name']],
                        [
                            'start_time' => $tpl['start_time'],
                            'end_time' => $tpl['end_time'],
                            'duration' => $tpl['duration'],
                        ]
                    )
                );
            }
        }

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($shiftTemplates as $template) {
                // Her şablon + gün için vardiya kaydı (shifts tablosu şemasına uygun)
                $shift = Shift::firstOrCreate([
                    'template_id' => $template->id,
                    'date' => $date->format('Y-m-d'),
                ]);

                // Vardiyaya rastgele 3-7 işçi ata (bol veri)
                $workerCount = min($workers->count(), rand(3, 7));
                $shiftWorkers = $workers->random($workerCount);

                foreach ($shiftWorkers as $worker) {
                    ShiftAssignment::firstOrCreate([
                        'shift_id' => $shift->id,
                        'user_id' => $worker->id,
                    ]);

                    // Her işçi bir makinede üretim yapsın
                    $machine = $machines->random();
                    $product = $products->random();
                    $quantity = rand(150, 700);

                    Production::create([
                        'product_id' => $product->id,
                        'machine_id' => $machine->id,
                        'user_id' => $worker->id,
                        'shift_id' => $shift->id,
                        'quantity' => $quantity,
                        'production_date' => $date->format('Y-m-d'),
                    ]);
                }
            }
        }
    }

    private function createSales($customers, $products)
    {
        // Son 1 yıl için satış verisi
        $startDate = Carbon::now()->subDays(365);
        $endDate = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Günde 1-5 satış
            $dailySales = rand(1, 5);
            
            for ($i = 0; $i < $dailySales; $i++) {
                $customer = $customers->random();
                
                $sale = Sales::create([
                    'customer_id' => $customer->id,
                    'sale_date' => $date->format('Y-m-d'),
                    'payment_type' => ['pesin', 'borc', 'kismi'][rand(0, 2)],
                    'paid_amount' => 0 // Sonra hesaplanacak
                ]);

                $totalAmount = 0;
                // Her satışta 1-3 ürün çeşidi
                $saleProducts = $products->random(rand(1, 3));
                
                foreach ($saleProducts as $product) {
                    $qty = rand(10, 100);
                    $price = $product->production_cost * 1.5; // %50 kar
                    
                    SalesProduct::create([
                        'sales_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $price
                    ]);
                    
                    $totalAmount += $qty * $price;
                }

                // Ödeme güncelleme
                if ($sale->payment_type == 'pesin') {
                    $sale->update(['paid_amount' => $totalAmount]);
                } elseif ($sale->payment_type == 'kismi') {
                    $sale->update(['paid_amount' => $totalAmount * rand(30, 70) / 100]);
                }
            }
        }
    }

    private function createCosts()
    {
        $types = ['Elektrik', 'Su', 'Yemek', 'Servis', 'Bakım', 'Hammadde'];
        // Son 1 yıl için gider verisi
        $startDate = Carbon::now()->subDays(365);
        $endDate = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Günde 0-2 gider kaydı
            if (rand(0, 10) > 7) {
                Cost::create([
                    'cost_type' => $types[array_rand($types)],
                    'amount' => rand(500, 5000),
                    'cost_date' => $date->format('Y-m-d'),
                ]);
            }
        }
    }
}
