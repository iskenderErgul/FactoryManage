<?php

namespace Database\Seeders;

use App\Models\PacsEntriesLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PacsEntriesLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        PacsEntriesLog::create([
            'pacs_entry_id' => 1,
            'user_id' => 1,
            'action' => 'create',
            'changes' => 'Yeni giriş oluşturuldu.',
        ]);
        // Diğer örnek logları buraya ekleyebilirsiniz
    }
}
