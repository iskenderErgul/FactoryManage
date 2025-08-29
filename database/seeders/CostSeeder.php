<?php

namespace Database\Seeders;

use App\Domains\Costs\Models\Cost;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $costTypes = [
            'Elektrik',
            'Doğalgaz',
            'Su',
            'Personel',
            'Kira',
            'Bakım-Onarım',
            'Nakliye',
            'Sigorta',
            'Vergi',
            'Diğer'
        ];

        // Son 6 ay için rastgele maliyet verileri oluştur
        for ($month = 0; $month < 6; $month++) {
            $baseDate = Carbon::now()->subMonths($month);
            
            foreach ($costTypes as $type) {
                // Her ay için her maliyet tipinden 1-3 kayıt oluştur
                $recordCount = rand(1, 3);
                
                for ($i = 0; $i < $recordCount; $i++) {
                    $randomDay = rand(1, 28); // Güvenli bir gün aralığı
                    $costDate = $baseDate->copy()->day($randomDay);
                    
                    // Maliyet tipine göre tutar aralığı belirle
                    $amount = match($type) {
                        'Elektrik' => rand(5000, 15000) + rand(0, 99) / 100,
                        'Doğalgaz' => rand(3000, 10000) + rand(0, 99) / 100,
                        'Su' => rand(1000, 3000) + rand(0, 99) / 100,
                        'Personel' => rand(20000, 50000) + rand(0, 99) / 100,
                        'Kira' => rand(10000, 25000) + rand(0, 99) / 100,
                        'Bakım-Onarım' => rand(2000, 8000) + rand(0, 99) / 100,
                        'Nakliye' => rand(3000, 12000) + rand(0, 99) / 100,
                        'Sigorta' => rand(2000, 6000) + rand(0, 99) / 100,
                        'Vergi' => rand(5000, 20000) + rand(0, 99) / 100,
                        default => rand(1000, 5000) + rand(0, 99) / 100,
                    };
                    
                    Cost::create([
                        'cost_type' => $type,
                        'amount' => $amount,
                        'cost_date' => $costDate->toDateString(),
                    ]);
                }
            }
        }
    }
}
