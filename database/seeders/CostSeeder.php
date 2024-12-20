<?php

namespace Database\Seeders;

use App\Domains\Costs\Models\Cost;
use Illuminate\Database\Seeder;

class CostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Cost::create([
            'cost_type' => 'Malzeme',
            'amount' => 200.00,
            'cost_date' => now()->toDateString(),
        ]);
        // Diğer örnek maliyetleri buraya ekleyebilirsiniz
    }
}
