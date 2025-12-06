<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasDateFilters;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;
use App\Domains\Production\DTOs\Reports\ReportFilterDTO;

/**
 * DateRangeReportService - Tarih aralıklı üretim raporları
 * 
 * Bu service, belirli bir tarih aralığındaki üretim verilerini raporlar.
 * Özet istatistikler, günlük dağılım ve top ürünler sağlar.
 */
class DateRangeReportService
{
    use HasDateFilters, HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * Tarih aralıklı rapor oluştur
     * 
     * @param ReportFilterDTO $filter
     * @return array
     */
    public function generate(ReportFilterDTO $filter): array
    {
        $productions = $this->query
            ->baseQuery()
            ->whereBetween('production_date', [$filter->startDate, $filter->endDate])
            ->get();

        $summary = $this->calculateSummary($productions, $filter);
        $dailyDistribution = $this->getDailyDistribution($filter);
        $topProducts = $this->getTopProducts($filter);
        $hourlyAnalysis = $this->getHourlyAnalysis($filter);

        return [
            'period' => [
                'start_date' => DateHelper::formatTurkish($filter->startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($filter->endDate, 'short'),
                'days' => DateHelper::getDaysBetween($filter->startDate, $filter->endDate),
            ],
            'summary' => $summary,
            'daily_distribution' => $dailyDistribution,
            'top_products' => $topProducts,
            'hourly_analysis' => $hourlyAnalysis,
        ];
    }

    /**
     * Özet istatistikleri hesapla
     * 
     * @param \Illuminate\Support\Collection $productions
     * @param ReportFilterDTO $filter
     * @return array
     */
    private function calculateSummary($productions, ReportFilterDTO $filter): array
    {
        $totalQuantity = $productions->sum('quantity');
        $days = DateHelper::getDaysBetween($filter->startDate, $filter->endDate);

        return [
            'total_quantity' => $totalQuantity,
            'total_count' => $productions->count(),
            'daily_average' => $this->calculateDailyAverage($totalQuantity, $days),
            'unique_products' => $productions->pluck('product_id')->unique()->count(),
            'unique_workers' => $productions->pluck('user_id')->unique()->count(),
            'unique_machines' => $productions->pluck('machine_id')->unique()->count(),
        ];
    }

    /**
     * Günlük dağılımı getir
     * 
     * @param ReportFilterDTO $filter
     * @return array
     */
    private function getDailyDistribution(ReportFilterDTO $filter): array
    {
        return $this->query
            ->dailyDistribution($filter->startDate, $filter->endDate, $filter->toArray())
            ->get()
            ->map(fn($item) => [
                'date' => DateHelper::formatTurkish($item->date, 'short'),
                'date_raw' => $item->date,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    /**
     * Top ürünleri getir
     * 
     * @param ReportFilterDTO $filter
     * @param int $limit
     * @return array
     */
    private function getTopProducts(ReportFilterDTO $filter, int $limit = 5): array
    {
        return $this->query
            ->topProducts($filter->startDate, $filter->endDate, $limit, $filter->toArray())
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Bilinmeyen',
                'product_type' => $item->product->product_type ?? null,
                'total_quantity' => (int) $item->total_quantity,
            ])
            ->toArray();
    }

    /**
     * Saatlik analizi getir
     * 
     * @param ReportFilterDTO $filter
     * @return array
     */
    private function getHourlyAnalysis(ReportFilterDTO $filter): array
    {
        return $this->query
            ->hourlyAnalysis($filter->startDate, $filter->endDate, $filter->toArray())
            ->get()
            ->map(fn($item) => [
                'hour' => (int) $item->hour,
                'total' => (int) $item->total,
                'count' => (int) $item->count,
                'average' => $item->count > 0 ? round($item->total / $item->count, 2) : 0,
            ])
            ->toArray();
    }
}
