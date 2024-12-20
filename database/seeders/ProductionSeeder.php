<?php

namespace Database\Seeders;

use App\Domains\Production\Models\Production;
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
            'product_id' => '1',
            'quantity' => 100,
            'shift_id' => 1,
            'production_date' => now()->toDateString(),
        ]);
        // Diğer örnek üretimleri buraya ekleyebilirsiniz
    }
}
