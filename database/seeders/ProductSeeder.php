<?php

namespace Database\Seeders;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;

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
