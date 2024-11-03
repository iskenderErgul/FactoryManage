<?php

namespace Database\Seeders;

use App\Domains\Sales\Models\Sales;
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
            'sale_date' => now()->toDateString(),
        ]);
        // Diğer örnek satışları buraya ekleyebilirsiniz
    }
}
