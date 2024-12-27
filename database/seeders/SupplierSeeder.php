<?php

namespace Database\Seeders;

use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Supplier::create([
            'name' => 'Tedarikçi A',
            'email' => 'tedarikci_a@example.com',
            'phone_number' => '0123456789',
            'address' => 'Tedarikçi adresi',
        ]);
        // Diğer örnek tedarikçileri buraya ekleyebilirsiniz
    }
}
