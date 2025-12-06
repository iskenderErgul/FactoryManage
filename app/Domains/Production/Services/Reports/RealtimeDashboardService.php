<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;
use Carbon\Carbon;

/**
 * RealtimeDashboardService - Gerçek zamanlı dashboard verileri
 * 
 * Bu service, güncel üretim verilerini sağlar.
 * Bugünkü üretim, saatlik dağılım ve son üretimler.
 */
class RealtimeDashboardService
{
    use HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * Gerçek zamanlı dashboard verisi oluştur
     * 
     * @return array
     */
    public function generate(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $todayProductions = $this->getTodayProductions();
        $todayStats = $this->getTodayStats();
        $hourlyProduction = $this->getTodayHourlyProduction();
        $recentProductions = $this->getRecentProductions();
        $comparison = $this->getComparison($todayProductions);

        return [
            'timestamp' => now()->toIso8601String(),
            'today_date' => DateHelper::formatTurkish($today, 'long'),
            'today_summary' => [
                'total_quantity' => $todayStats['total_quantity'],
                'production_count' => $todayStats['total_count'],
                'active_workers' => $todayStats['unique_workers'],
                'hourly_average' => $todayStats['total_count'] > 0 
                    ? round($todayStats['total_quantity'] / max(1, now()->hour), 2)
                    : 0,
            ],
            'comparison' => $comparison,
            'hourly_production' => $hourlyProduction,
            'recent_productions' => $recentProductions,
            'total_today' => $todayProductions->sum('quantity'),
        ];
    }

    /**
     * Bugünkü üretimleri getir
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getTodayProductions()
    {
        return $this->query->today()->get();
    }

    /**
     * Bugünkü saatlik üretimi getir
     * 
     * @return array
     */
    private function getTodayHourlyProduction(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        
        return $this->query
            ->hourlyAnalysis($today, $today)
            ->get()
            ->map(fn($item) => [
                'hour' => (int) $item->hour,
                'hour_display' => sprintf('%02d:00', $item->hour),
                'total' => (int) $item->total,
                'count' => (int) $item->count,
            ])
            ->toArray();
    }

    /**
     * Son üretimleri getir
     * 
     * @param int $limit
     * @return array
     */
    private function getRecentProductions(int $limit = 10): array
    {
        return $this->query
            ->recent($limit)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product->product_name ?? 'Bilinmeyen',
                'user_name' => $item->user->name ?? 'Bilinmeyen',
                'machine_name' => $item->machine->machine_name ?? 'Bilinmeyen',
                'quantity' => $item->quantity,
                'production_date' => DateHelper::formatTurkish($item->production_date, 'short'),
                'created_at' => $item->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Bugünkü istatistikleri getir
     * 
     * @return array
     */
    private function getTodayStats(): array
    {
        $productions = $this->getTodayProductions();

        return [
            'total_quantity' => $productions->sum('quantity') ?? 0,
            'total_count' => $productions->count() ?? 0,
            'unique_products' => $productions->pluck('product_id')->unique()->count() ?? 0,
            'unique_workers' => $productions->pluck('user_id')->unique()->count() ?? 0,
            'unique_machines' => $productions->pluck('machine_id')->unique()->count() ?? 0,
            'average_per_production' => $productions->count() > 0 
                ? round($productions->sum('quantity') / $productions->count(), 2)
                : 0,
        ];
    }

    /**
     * Dünle karşılaştırma yap
     * 
     * @param \Illuminate\Support\Collection $todayProductions
     * @return array
     */
    private function getComparison($todayProductions): array
    {
        $todayTotal = $todayProductions->sum('quantity');
        
        $yesterday = \Carbon\Carbon::yesterday();
        $yesterdayTotal = $this->query->baseQuery()
            ->whereDate('production_date', $yesterday)
            ->sum('quantity') ?? 0;

        $changePercentage = $yesterdayTotal > 0 
            ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 2)
            : 0;

        return [
            'yesterday_total' => (int) $yesterdayTotal,
            'change_percentage' => $changePercentage,
            'trend' => $changePercentage >= 0 ? 'up' : 'down',
        ];
    }
}
