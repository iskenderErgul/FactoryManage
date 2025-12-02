<?php

namespace Database\Seeders;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SupplierTransactionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('tr_TR');

        // 1. Test Tedarikçisi Oluştur (veya varsa temizle)
        $supplier = Supplier::firstOrCreate(
            ['supplier_email' => 'test@tedarikci.com'],
            [
                'supplier_name' => 'Mehmet Demir (Test Tedarikçi)',
                'supplier_phone' => '0532 987 65 43',
                'supplier_address' => 'İkitelli OSB, Metal İşleri San. Sit. 12. Blok No:5, İstanbul',
                'debt' => 0
            ]
        );

        // Mevcut transactionları temizle (Temiz bir sayfa için)
        SupplierTransaction::where('supplier_id', $supplier->id)->delete();

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
                // Mal Alımı (Borç) -> Tedarikçiye borçlanıyoruz
                $amount = rand(1000, 50000); // 1.000 - 50.000 TL arası
                $type = 'borç';
                $description = $faker->randomElement([
                    'Hammadde Alımı',
                    'Yedek Parça',
                    'Kalıp Bakım Ücreti',
                    'Lojistik Hizmeti',
                    'Fatura No: ' . rand(10000, 99999)
                ]);
                $currentBalance += $amount;
            } else {
                // Ödeme -> Tedarikçiye ödeme yapıyoruz
                // Bakiye varsa ödeme yapsın, yoksa rastgele küçük bir ödeme (avans gibi)
                $maxPayment = $currentBalance > 0 ? $currentBalance : 5000;
                $amount = rand(1000, $maxPayment > 1000 ? $maxPayment : 2000);
                $type = 'ödeme';
                $description = $faker->randomElement([
                    'Havale ile Ödeme',
                    'Nakit Ödeme',
                    'Çek Çıkışı',
                    'Kredi Kartı Ödemesi'
                ]);
                $currentBalance -= $amount;
            }

            $transactions[] = [
                'supplier_id' => $supplier->id,
                'type' => $type,
                'amount' => $amount,
                'date' => $date->format('Y-m-d'),
                'description' => $description,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        // Toplu Ekleme
        SupplierTransaction::insert($transactions);

        // Tedarikçi bakiyesini güncelle
        $supplier->update(['debt' => $currentBalance]);

        $this->command->info("Test tedarikçisi oluşturuldu: {$supplier->supplier_name}");
        $this->command->info(count($transactions) . " adet işlem eklendi.");
        $this->command->info("Güncel Bakiye: " . number_format($currentBalance, 2) . " TL");
    }
}
