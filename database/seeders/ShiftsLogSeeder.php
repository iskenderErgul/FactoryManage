<?php

namespace Database\Seeders;

use App\Models\ShiftsLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftsLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ShiftsLog::create([
            'shift_id' => 1,
            'user_id' => 1,
            'action' => 'update',
            'changes' => 'Vardiya zamanı değiştirildi.',
        ]);
        // Diğer örnek logları buraya ekleyebilirsiniz
    }
}
