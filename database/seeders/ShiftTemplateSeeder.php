<?php

namespace Database\Seeders;

use App\Domains\Shift\Models\ShiftTemplate;
use Illuminate\Database\Seeder;

class ShiftTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        ShiftTemplate::create([
            'name' => 'Sabah',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'duration' => 480, // 8 saat
        ]);

        ShiftTemplate::create([
            'name' => 'Öğlen',
            'start_time' => '16:00:00',
            'end_time' => '00:00:00',
            'duration' => 480, // 8 saat
        ]);

        ShiftTemplate::create([
            'name' => 'Akşam',
            'start_time' => '00:00:00',
            'end_time' => '08:00:00',
            'duration' => 480, // 8 saat
        ]);
    }

}
