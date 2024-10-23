<?php

namespace Database\Seeders;

use App\Models\WeeklySchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeeklyScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        WeeklySchedule::create([
            'week' => '2024-10-01',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);
        // Diğer örnek verileri buraya ekleyebilirsiniz
    }
}
