<?php

namespace Database\Seeders;

use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Admin admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin123'),
            'photo' => 'default.jpg',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Worker worker',
            'email' => 'worker@worker.com',
            'password' => bcrypt('worker123'),
            'photo' => 'default.jpg',
            'role' => 'worker',
        ]);
    }
}
