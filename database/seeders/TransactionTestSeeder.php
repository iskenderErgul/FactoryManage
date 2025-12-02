<?php

namespace Database\Seeders;

use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TransactionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('tr_TR');

        // 1. Test Müşterisi Oluştur (veya varsa temizle)
        $customer = Customer::firstOrCreate(
            ['email' => 'test@musteri.com'],
            [
                'name' => 'Ahmet Yılmaz (Test)',
                'phone' => '0555 123 45 67',
                'address' => 'Organize Sanayi Bölgesi, 5. Cadde No:12, İstanbul',
                'debt' => 0
            ]
        );

        // Mevcut transactionları temizle (Temiz bir sayfa için)
        Transaction::where('customer_id', $customer->id)->delete();

        $startDate = Carbon::now()->subYear(); // 1 yıl öncesinden başla
        $currentBalance = 0;
        $transactions = [];

        // 2. 50 Adet İşlem Oluştur
        for ($i = 0; $i < 50; $i++) {
            // Tarihi her adımda biraz ilerlet (1-7 gün arası)
            $date = (clone $startDate)->addDays(rand(1, 7) + ($i * 7));
            
            // Geleceğe gitmesin
            if ($date->isFuture()) {
                break;
            }

            // %60 Borç (Mal Alımı), %40 Ödeme ihtimali
            $isDebt = rand(1, 100) <= 60;
            
            if ($isDebt) {
                // Mal Alımı (Borç)
                $amount = rand(1000, 50000); // 1.000 - 50.000 TL arası
                $type = 'Borç';
                $description = $faker->randomElement([
                    'PP Hammadde Alımı (100kg)',
                    'Plastik Enjeksiyon Ürünü',
                    'Koli Bant Alımı',
                    'Özel Üretim Plastik Kapak',
                    'Sevkiyat Bedeli',
                    'Fatura No: ' . rand(10000, 99999)
                ]);
                $currentBalance += $amount;
            } else {
                // Ödeme
                // Bakiye varsa ödeme yapsın, yoksa rastgele küçük bir ödeme (avans gibi)
                $maxPayment = $currentBalance > 0 ? $currentBalance : 5000;
                $amount = rand(1000, $maxPayment > 1000 ? $maxPayment : 2000);
                $type = 'Ödeme';
                $description = $faker->randomElement([
                    'Banka Havalesi',
                    'Nakit Tahsilat',
                    'Çek Ödemesi',
                    'Kredi Kartı Ödemesi'
                ]);
                $currentBalance -= $amount;
            }

            $transactions[] = [
                'customer_id' => $customer->id,
                'type' => $type,
                'amount' => $amount,
                'date' => $date->format('Y-m-d'),
                'description' => $description,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        // Toplu Ekleme
        Transaction::insert($transactions);

        // Müşteri bakiyesini güncelle
        $customer->update(['debt' => $currentBalance]);

        $this->command->info("Test müşterisi oluşturuldu: {$customer->name}");
        $this->command->info(count($transactions) . " adet işlem eklendi.");
        $this->command->info("Güncel Bakiye: " . number_format($currentBalance, 2) . " TL");
    }
}
