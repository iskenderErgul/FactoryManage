<?php

namespace Database\Seeders;

use App\Domains\Machines\Models\Machine;
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
