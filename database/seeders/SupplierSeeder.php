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
                'customer_id' => 1,
                'supplied_product' => 'Plastic Granules',
                'supplied_product_quantity' => '1500 kg',
                'supplied_product_price' => '3000 USD',
                'supply_date' => '2025-01-08',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_id' => 1,
                'supplied_product' => 'Recycled Paper',
                'supplied_product_quantity' => '2000 kg',
                'supplied_product_price' => '1000 USD',
                'supply_date' => '2025-01-07',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_id' => 1,
                'supplied_product' => 'Cardboard Boxes',
                'supplied_product_quantity' => '800 units',
                'supplied_product_price' => '1200 USD',
                'supply_date' => '2025-01-06',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_id' => 1,
                'supplied_product' => 'Glass Bottles',
                'supplied_product_quantity' => '1000 units',
                'supplied_product_price' => '2000 USD',
                'supply_date' => '2025-01-05',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'customer_id' => 1,
                'supplied_product' => 'Metal Sheets',
                'supplied_product_quantity' => '500 kg',
                'supplied_product_price' => '1500 USD',
                'supply_date' => '2025-01-04',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
