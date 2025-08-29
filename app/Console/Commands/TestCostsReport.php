<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Costs\CostsController;
use Illuminate\Http\Request;

class TestCostsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:costs-report {period=1month : Period (1month, 2months, 3months, 6months, 1year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test dönemsel maliyet raporlarını gösterir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->argument('period');
        
        $this->info("📊 Dönemsel Maliyet Raporu - Periyot: {$period}");
        $this->info("=" . str_repeat("=", 70));
        
        // Controller'ı çağır
        $controller = app(CostsController::class);
        $request = new Request(['period' => $period]);
        $response = $controller->getPeriodicCosts($request);
        $data = json_decode($response->getContent(), true);
        
        if (!$data) {
            $this->error('Veri alınamadı!');
            return 1;
        }
        
        // Tarih aralığını göster
        $this->info("📅 Tarih Aralığı: {$data['dateRange']['start']} - {$data['dateRange']['end']}");
        $this->newLine();
        
        // Kaynak bazlı toplamları göster
        $this->info("💰 Kaynak Bazlı Toplamlar:");
        $this->table(
            ['Kaynak', 'Toplam (₺)'],
            collect($data['sourceTotals'])->map(function($item) {
                return [
                    $item['source'],
                    number_format($item['total'], 2, ',', '.')
                ];
            })->toArray()
        );
        
        // Detaylı maliyetleri göster
        $this->newLine();
        $this->info("📋 Detaylı Maliyet Kalemleri:");
        
        $costs = collect($data['costs']);
        
        // Kaynaklara göre grupla
        $groupedCosts = $costs->groupBy('source');
        
        foreach ($groupedCosts as $source => $items) {
            $this->newLine();
            $this->comment("▶ {$source}:");
            
            $tableData = $items->map(function($item) use ($data) {
                $percentage = $data['grandTotal'] > 0 
                    ? round(($item['amount'] / $data['grandTotal']) * 100, 1) 
                    : 0;
                    
                return [
                    $item['category'],
                    number_format($item['amount'], 2, ',', '.'),
                    "%{$percentage}"
                ];
            })->toArray();
            
            $this->table(
                ['Kategori', 'Tutar (₺)', 'Oran'],
                $tableData
            );
        }
        
        // Genel toplam
        $this->newLine();
        $this->info("=" . str_repeat("=", 70));
        $this->info("💵 GENEL TOPLAM: ₺" . number_format($data['grandTotal'], 2, ',', '.'));
        
        return 0;
    }
}
