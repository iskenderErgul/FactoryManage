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

        $this->call([
            UserSeeder::class,

        ]);
    }
}

