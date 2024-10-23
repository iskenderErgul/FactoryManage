<?php

namespace Database\Seeders;

use App\Models\Sales;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Sales::create([
            'customer_id' => 1,
            'product_id' => 1,
            'quantity' => 5,
            'price' => 50.00,
            'sale_date' => now()->toDateString(),
        ]);
        // Diğer örnek satışları buraya ekleyebilirsiniz
    }
}
