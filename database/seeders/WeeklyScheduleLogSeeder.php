<?php

namespace Database\Seeders;

use App\Models\WeeklyScheduleLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeeklyScheduleLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        WeeklyScheduleLog::create([
            'weekly_schedule_id' => 1,
            'action' => 'update',
            'changes' => 'Haftalık program güncellendi.',
        ]);
        // Diğer örnek logları buraya ekleyebilirsiniz
    }
}
