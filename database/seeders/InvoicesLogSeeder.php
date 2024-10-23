<?php

namespace Database\Seeders;

use App\Models\InvoicesLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoicesLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        InvoicesLog::create(['invoice_id' => 1, 'action' => 'Generated Invoice', 'changes' => 'Invoice generated for sale.', 'timestamp' => now()]);
    }
}
