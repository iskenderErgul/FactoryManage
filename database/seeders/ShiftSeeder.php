<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Shift::create([
            'template_id' => 1,
            'start_time' => '08:00:00',
            'user_id' => 1,
            'schedule_id' => 1,
            'date' => now()->toDateString(),
            'end_time' => '16:00:00',
        ]);
        // Diğer örnek vardiyaları buraya ekleyebilirsiniz
    }
}
