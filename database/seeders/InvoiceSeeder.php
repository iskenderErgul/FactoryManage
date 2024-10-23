<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Invoice::create([
            'type' => 'Satış',
            'related_id' => 1,
            'related_process' => 'Satış işlemi',
            'amount' => 500.00,
            'invoice_date' => now()->toDateString(),
        ]);
        // Diğer örnek faturaları buraya ekleyebilirsiniz
    }
}
