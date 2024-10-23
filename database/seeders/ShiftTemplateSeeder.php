<?php

namespace Database\Seeders;

use App\Models\ShiftTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        ShiftTemplate::create([
            'name' => 'Gündüz Vardiyası',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'duration' => 480,
        ]);
        // Diğer örnek vardiya şablonlarını buraya ekleyebilirsiniz
    }

}
