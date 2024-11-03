<?php

namespace Database\Seeders;

use App\Domains\Sales\Models\SalesLog;
use Illuminate\Database\Seeder;

class SalesLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SalesLog::create(['sale_id' => 1, 'action' => 'Recorded Sale', 'changes' => 'Sale recorded for customer.', 'timestamp' => now()]);
    }
}
