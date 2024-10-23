<?php

namespace Database\Seeders;

use App\Models\Production;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Production::create([
            'user_id' => 1,
            'machine_id' => 1,
            'product_name' => 'Ürün A',
            'quantity' => 100,
            'shift_id' => 1,
            'production_date' => now()->toDateString(),
        ]);
        // Diğer örnek üretimleri buraya ekleyebilirsiniz
    }
}
