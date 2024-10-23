<?php

namespace Database\Seeders;

use App\Models\ProductionLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ProductionLog::create(['production_id' => 1, 'action' => 'Created Production Entry', 'changes' => 'Production entry created.', 'timestamp' => now()]);
    }
}
