<?php

namespace Database\Seeders;

use App\Domains\Users\Models\UsersLog;
use Illuminate\Database\Seeder;

class UsersLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        UsersLog::create([
            'user_id' => 1,
            'action' => 'update',
            'changes' => 'Profil güncellendi.',
        ]);
        // Diğer örnek logları buraya ekleyebilirsiniz
    }
}
