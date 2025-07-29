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
            'id' => 2,
            'product_name' => 'Öz Ergül Plastik Büyük Boy Kiloluk',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Büyük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999949,
            'created_at' => '2025-02-08 11:16:23',
            'updated_at' => '2025-03-03 10:56:30',
        ]);

        Product::create([
            'id' => 3,
            'product_name' => 'Öz Ergül Plastik Orta Boy Kiloluk',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Orta Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:16:43',
            'updated_at' => '2025-03-03 10:56:30',
        ]);

        Product::create([
            'id' => 4,
            'product_name' => 'Öz Ergül Plastik Küçük  Boy Kiloluk',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Küçük  Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:17:00',
            'updated_at' => '2025-02-08 11:24:19',
        ]);

        Product::create([
            'id' => 7,
            'product_name' => 'M&R Ortak Boy Kiloluk',
            'product_type' => '2.Kalite',
            'product_photo' => null,
            'description' => 'M&R Ortak Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:20:51',
            'updated_at' => '2025-02-08 11:24:26',
        ]);

        Product::create([
            'id' => 5,
            'product_name' => 'Öz Ergül Plastik Mini Boy Kiloluk',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Mini Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:18:51',
            'updated_at' => '2025-02-08 11:26:13',
        ]);

        Product::create([
            'id' => 6,
            'product_name' => 'M&R Büyük Boy Kiloluk',
            'product_type' => '2.Kalite',
            'product_photo' => null,
            'description' => 'M&R Büyük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:20:12',
            'updated_at' => '2025-02-08 11:24:33',
        ]);

        Product::create([
            'id' => 8,
            'product_name' => 'M&R Küçük Boy Kiloluk',
            'product_type' => '2.Kalite',
            'product_photo' => null,
            'description' => 'M&R Küçük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:21:15',
            'updated_at' => '2025-02-08 11:21:15',
        ]);

        Product::create([
            'id' => 9,
            'product_name' => 'M&R Mini Boy Kiloluk',
            'product_type' => '2.Kalite',
            'product_photo' => null,
            'description' => 'M&R Mini Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:22:52',
            'updated_at' => '2025-02-08 11:22:52',
        ]);

        Product::create([
            'id' => 10,
            'product_name' => 'Lüx Öz Ergül Plastik Küçük Boy Kiloluk',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Küçük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:23:36',
            'updated_at' => '2025-02-08 11:51:20',
        ]);

        Product::create([
            'id' => 11,
            'product_name' => 'Lüx Öz Ergül Plastik Orta  Boy Kiloluk',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Orta  Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:24:49',
            'updated_at' => '2025-02-08 11:24:49',
        ]);

        Product::create([
            'id' => 12,
            'product_name' => 'Lüx Öz Ergül Plastik Büyük  Boy Kiloluk',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Büyük  Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:25:07',
            'updated_at' => '2025-02-08 11:51:20',
        ]);

        Product::create([
            'id' => 13,
            'product_name' => 'Lüx Öz Ergül Plastik Mini Boy Kiloluk',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Mini Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:25:34',
            'updated_at' => '2025-02-08 11:25:34',
        ]);

        Product::create([
            'id' => 14,
            'product_name' => 'Lüx Öz Ergül Plastik Küçük Boy Adetli 300 Adet',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Küçük Boy Adetli 300 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:34:12',
            'updated_at' => '2025-02-08 11:35:14',
        ]);

        Product::create([
            'id' => 15,
            'product_name' => 'Lüx Öz Ergül Plastik Orta Boy Adetli 200 Adet',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Orta Boy Adetli 200 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999782,
            'created_at' => '2025-02-08 11:34:34',
            'updated_at' => '2025-04-10 10:33:51',
        ]);

        Product::create([
            'id' => 16,
            'product_name' => 'Lüx Öz Ergül Plastik Büyük Boy Adetli 150 Adet',
            'product_type' => '1.Kalite',
            'product_photo' => null,
            'description' => 'Lüx Öz Ergül Plastik Büyük Boy Adetli 150 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:35:04',
            'updated_at' => '2025-02-08 11:35:04',
        ]);

        Product::create([
            'id' => 17,
            'product_name' => 'Öz Ergül Plastik Küçük Boy Adetli 300 Adet',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Küçük Boy Adetli 300 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:36:25',
            'updated_at' => '2025-02-08 11:36:25',
        ]);

        Product::create([
            'id' => 18,
            'product_name' => 'Öz Ergül Plastik Orta Boy Adetli 200 Adet',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Orta Boy Adetli 200 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:37:12',
            'updated_at' => '2025-02-08 11:37:12',
        ]);

        Product::create([
            'id' => 19,
            'product_name' => 'Öz Ergül Plastik Büyük  Boy Adetli 150 Adet',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Öz Ergül Plastik Büyük  Boy Adetli 150 Adet',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:37:36',
            'updated_at' => '2025-02-08 11:37:36',
        ]);

        Product::create([
            'id' => 20,
            'product_name' => 'New Plast Büyük Boy Kiloluk',
            'product_type' => '3.Kalite',
            'product_photo' => null,
            'description' => 'New Plast Büyük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:40:03',
            'updated_at' => '2025-02-08 11:51:19',
        ]);

        Product::create([
            'id' => 21,
            'product_name' => 'New Plast Orta Boy Kiloluk',
            'product_type' => '3.Kalite',
            'product_photo' => null,
            'description' => 'New Plast Orta Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:40:20',
            'updated_at' => '2025-02-08 11:40:20',
        ]);

        Product::create([
            'id' => 22,
            'product_name' => 'New Plast Küçük Boy Kiloluk',
            'product_type' => '3.Kalite',
            'product_photo' => null,
            'description' => 'New Plast Küçük Boy Kiloluk',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:40:44',
            'updated_at' => '2025-02-08 11:40:44',
        ]);

        Product::create([
            'id' => 23,
            'product_name' => 'Kelebek Büyük Boy 500gr',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Kelebek Büyük Boy 500gr',
            'production_cost' => 0.00,
            'stock_quantity' => 100000099,
            'created_at' => '2025-02-08 11:47:45',
            'updated_at' => '2025-02-08 13:26:45',
        ]);

        Product::create([
            'id' => 24,
            'product_name' => 'Kelebek Küçük Boy 500gr',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Kelebek Küçük Boy 500gr',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:47:56',
            'updated_at' => '2025-02-08 12:50:55',
        ]);

        Product::create([
            'id' => 25,
            'product_name' => 'Kelebek Orta Boy 500gr',
            'product_type' => 'Ara kalite',
            'product_photo' => null,
            'description' => 'Kelebek Orta Boy 500gr',
            'production_cost' => 0.00,
            'stock_quantity' => 99999999,
            'created_at' => '2025-02-08 11:48:10',
            'updated_at' => '2025-02-08 13:26:45',
        ]);
    }
}
