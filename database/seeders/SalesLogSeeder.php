<?php

namespace Database\Seeders;

use App\Models\SalesLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
