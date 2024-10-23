<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Product::create([
            'product_name' => 'Ürün A',
            'product_type' => 'Tip A',
            'product_photo' => 'url_to_photo',
            'description' => 'Açıklama',
            'production_cost' => 10.00,
            'stock_quantity' => 100,
        ]);
        // Diğer örnek ürünleri buraya ekleyebilirsiniz
    }
}
