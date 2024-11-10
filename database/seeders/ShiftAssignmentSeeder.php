<?php

namespace Database\Seeders;

use App\Models\ShiftAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vardiya atamaları
        ShiftAssignment::create([
            'shift_id' => 1, // Sabah vardiyası (shift_id: 1)
            'user_id' => 1,  // Ahmet
        ]);


    }
}
