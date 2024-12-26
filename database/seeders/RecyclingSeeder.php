<?php

namespace Database\Seeders;

use App\Domains\Recyclings\Models\Recycling;
use Illuminate\Database\Seeder;

class RecyclingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Recycling::create([
            'month' => 'Ocak',
            'material_type' => 'Plastik',
            'year' => 2024,
            'recycling_quantity' => 1000,
        ]);

        Recycling::create([
            'month' => 'Şubat',
            'material_type' => 'Kağıt',
            'year' => 2024,
            'recycling_quantity' => 800,
        ]);

        Recycling::create([
            'month' => 'Mart',
            'material_type' => 'Cam',
            'year' => 2024,
            'recycling_quantity' => 600,
        ]);


    }
}
