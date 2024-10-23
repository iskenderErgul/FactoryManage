<?php

namespace Database\Seeders;

use App\Models\ShiftTemplatesLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftTemplatesLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ShiftTemplatesLog::create([
            'shift_template_id' => 1,
            'action' => 'create',
            'changes' => 'Yeni vardiya şablonu oluşturuldu.',
        ]);
        // Diğer örnek logları buraya ekleyebilirsiniz
    }
}
