<?php

namespace Database\Seeders;

use App\Models\CustomersLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomersLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        CustomersLog::create(['customer_id' => 1, 'action' => 'Added Customer', 'changes' => 'New customer added.', 'timestamp' => now()]);
    }
}
