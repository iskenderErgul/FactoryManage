<?php

namespace Database\Seeders;

use App\Domains\Recyclings\Models\Recycling;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecyclingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('recyclings')->insert([
            [
                'company_name' => 'Eco Plastics Inc.',
                'material_type' => 'Plastic',
                'recycling_date' => Carbon::create('2025', '01', '08')->toDateString(),
                'recycling_quantity' => 500,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_name' => 'Green Earth Ltd.',
                'material_type' => 'Glass',
                'recycling_date' => Carbon::create('2025', '01', '07')->toDateString(),
                'recycling_quantity' => 350,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_name' => 'Clean Energy Solutions',
                'material_type' => 'Metal',
                'recycling_date' => Carbon::create('2025', '01', '06')->toDateString(),
                'recycling_quantity' => 800,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_name' => 'Renewable Resources',
                'material_type' => 'Paper',
                'recycling_date' => Carbon::create('2025', '01', '05')->toDateString(),
                'recycling_quantity' => 1000,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_name' => 'Eco Recycling Corp.',
                'material_type' => 'Plastic',
                'recycling_date' => Carbon::create('2025', '01', '04')->toDateString(),
                'recycling_quantity' => 200,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);


    }
}
