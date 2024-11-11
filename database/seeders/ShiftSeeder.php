<?php

namespace Database\Seeders;

use App\Domains\Shift\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $sabahiVardiya = Shift::create([
            'template_id' => 1, // Sabah vardiyası (template_id: 1)
            'date' => Carbon::create('2024', '11', '10'), // 10 Kasım 2024
        ]);

        $ogleVardiya = Shift::create([
            'template_id' => 2, // Öğlen vardiyası (template_id: 2)
            'date' => Carbon::create('2024', '11', '10'),
        ]);
    }
}
