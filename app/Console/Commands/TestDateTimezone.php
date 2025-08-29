<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Domains\Customer\Models\Transaction;

class TestDateTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:date-timezone {date? : Test date in YYYY-MM-DD format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tarih timezone dönüşümlerini kontrol eder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testDate = $this->argument('date') ?? '2024-01-15';
        
        $this->info("🕐 Tarih Timezone Test Raporu");
        $this->info("=" . str_repeat("=", 50));
        
        $this->info("📅 Test Tarihi: {$testDate}");
        $this->newLine();
        
        // Farklı timezone dönüşümlerini test et
        $this->info("🌍 Timezone Dönüşümleri:");
        
        // 1. UTC olarak parse
        $utc = Carbon::parse($testDate)->utc();
        $this->line("UTC: " . $utc->format('Y-m-d H:i:s T'));
        
        // 2. Europe/Istanbul olarak parse
        $istanbul = Carbon::parse($testDate)->setTimezone('Europe/Istanbul');
        $this->line("Europe/Istanbul: " . $istanbul->format('Y-m-d H:i:s T'));
        
        // 3. Asia/Istanbul olarak parse (eski kullanım)
        $asia = Carbon::parse($testDate)->setTimezone('Asia/Istanbul');
        $this->line("Asia/Istanbul: " . $asia->format('Y-m-d H:i:s T'));
        
        $this->newLine();
        
        // Frontend'den gelen farklı formatları test et
        $this->info("📱 Frontend Format Testleri:");
        
        $frontendFormats = [
            $testDate,                           // YYYY-MM-DD
            $testDate . 'T00:00:00.000Z',       // ISO format
            $testDate . ' 00:00:00',            // YYYY-MM-DD HH:mm:ss
        ];
        
        foreach ($frontendFormats as $format) {
            $parsed = Carbon::parse($format)->setTimezone('Europe/Istanbul')->format('Y-m-d');
            $this->line("'{$format}' -> '{$parsed}'");
        }
        
        $this->newLine();
        
        // Database'de kayıtlı son 3 transaction'ın tarihlerini göster
        $this->info("💾 Database'deki Son Transaction Tarihleri:");
        
        $transactions = Transaction::orderBy('created_at', 'desc')->take(3)->get();
        
        if ($transactions->isEmpty()) {
            $this->warn("Henüz transaction kaydı bulunamadı.");
        } else {
            foreach ($transactions as $transaction) {
                $this->line("ID: {$transaction->id} | Tarih: {$transaction->date} | Oluşturulma: {$transaction->created_at}");
            }
        }
        
        $this->newLine();
        
        // Timezone offset bilgilerini göster
        $this->info("⏰ Timezone Offset Bilgileri:");
        $now = Carbon::now();
        $this->line("Şu anki UTC offset: " . $now->format('P'));
        $this->line("Türkiye saati: " . $now->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s T'));
        
        return 0;
    }
}
