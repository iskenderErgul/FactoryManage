<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Customer::create([
            'name' => 'Müşteri A',
            'email' => 'musteri_a@example.com',
            'phone' => '0123456789',
            'address' => 'Müşteri adresi',
        ]);
        // Diğer örnek müşterileri buraya ekleyebilirsiniz
    }
}
