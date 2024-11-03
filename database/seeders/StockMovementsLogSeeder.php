<?php

namespace Database\Seeders;

use App\Common\Models\StockMovementsLog;
use Illuminate\Database\Seeder;

class StockMovementsLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        StockMovementsLog::create(['stock_movement_id' => 1, 'action' => 'Added Stock', 'changes' => 'Stock movement recorded.', 'timestamp' => now()]);
    }
}
