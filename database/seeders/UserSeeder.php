<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'UserManagements User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('12345'),
            'photo' => 'default.jpg',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Worker User',
            'email' => 'worker@worker.com',
            'password' => bcrypt('12345'),
            'photo' => 'default.jpg',
            'role' => 'worker',
        ]);
    }
}
