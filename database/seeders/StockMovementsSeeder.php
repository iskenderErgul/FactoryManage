<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockMovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        StockMovement::create([
            'product_id' => 1,
            'movement_type' => 'giriş',
            'quantity' => 20,
            'related_process' => 'Üretim',
            'movement_date' => now()->toDateString(),
        ]);
        // Diğer örnek stok hareketlerini buraya ekleyebilirsiniz
    }
}
