<?php

namespace Database\Seeders;

use App\Domains\Suppliers\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('suppliers')->insert([
            [
                'suppliers_name' => 'ABC Supply Co.',
                'suppliers_address' => '1234 Elm Street, Istanbul, Turkey',
                'supplied_product' => 'Plastic Granules',
                'supplied_product_quantity' => '1500 kg',
                'supplied_product_price' => '3000 USD',
                'supply_date' => Carbon::create('2025', '01', '08')->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'suppliers_name' => 'Green Materials Ltd.',
                'suppliers_address' => '4567 Oak Avenue, Ankara, Turkey',
                'supplied_product' => 'Recycled Paper',
                'supplied_product_quantity' => '2000 kg',
                'supplied_product_price' => '1000 USD',
                'supply_date' => Carbon::create('2025', '01', '07')->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'suppliers_name' => 'Eco Packaging Inc.',
                'suppliers_address' => '7890 Pine Road, Izmir, Turkey',
                'supplied_product' => 'Cardboard Boxes',
                'supplied_product_quantity' => '800 units',
                'supplied_product_price' => '1200 USD',
                'supply_date' => Carbon::create('2025', '01', '06')->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'suppliers_name' => 'Renewable Supplies',
                'suppliers_address' => '1011 Maple Street, Antalya, Turkey',
                'supplied_product' => 'Glass Bottles',
                'supplied_product_quantity' => '1000 units',
                'supplied_product_price' => '2000 USD',
                'supply_date' => Carbon::create('2025', '01', '05')->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'suppliers_name' => 'Sustainable Resources Ltd.',
                'suppliers_address' => '2022 Birch Avenue, Bursa, Turkey',
                'supplied_product' => 'Metal Sheets',
                'supplied_product_quantity' => '500 kg',
                'supplied_product_price' => '1500 USD',
                'supply_date' => Carbon::create('2025', '01', '04')->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
