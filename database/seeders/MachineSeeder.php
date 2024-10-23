<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Machine::create(['machine_name' => 'Makine A']);
        Machine::create(['machine_name' => 'Makine B']);
        // Diğer örnek makineleri buraya ekleyebilirsiniz
    }
}
