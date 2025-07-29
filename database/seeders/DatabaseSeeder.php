<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "🚀 Veritabanı seeding başlıyor...\n\n";

        // Önce temel veriler
        $this->call([
            UserSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            ShiftSeeder::class,
            ShiftAssignmentSeeder::class,
            ShiftTemplateSeeder::class,
            MachineSeeder::class,


        ]);

        echo "\n📊 Üretim ve satış verileri oluşturuluyor...\n";

        // Sonra bağımlı veriler
        $this->call([
            ProductionSeeder::class,
            SalesSeeder::class,
        ]);

        echo "\n📦 Stok hareketleri oluşturuluyor...\n";

        // En son stok hareketleri (üretim ve satışlara bağlı)
        $this->call([
            StockMovementsSeeder::class,
        ]);

        echo "\n✅ Tüm veriler başarıyla oluşturuldu!\n";
        echo "📅 Tarih aralığı: " . now()->subMonth()->format('d/m/Y') . " - " . now()->addMonth()->format('d/m/Y') . "\n";
    }
}

