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
            'name' => 'Murat Ergül',
            'email' => 'muratergul@admin.com',
            'password' => bcrypt('murat12345'),
            'photo' => 'default.jpg',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Reşit Ergül',
            'email' => 'resitergul@admin.com',
            'password' => bcrypt('resit12345'),
            'photo' => 'default.jpg',
            'role' => 'admin',
        ]);
    }
}
