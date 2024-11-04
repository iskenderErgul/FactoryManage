<?php

namespace Database\Seeders;

use App\Domains\PacsEntry\Models\PacsEntry;
use Illuminate\Database\Seeder;

class PacsEntrySeeder extends Seeder
{

        public function run()
    {
        PacsEntry::create(['user_id' => 1, 'entry_type' => 'checkin']);
        PacsEntry::create(['user_id' => 1, 'entry_type' => 'checkout']);
    }

}
