<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;

/**
 * ExecutiveSummaryService - Yönetici özet raporları
 * 
 * Bu service, üst düzey yöneticiler için kapsamlı özet raporlar sağlar.
 * Tüm önemli metrikleri ve karşılaştırmaları içerir.
 */
class ExecutiveSummaryService
{
    use HasStatistics;

    public function __construct(
        private ProductionReportQuery $query,
        private DateRangeReportService $dateRangeService,
        private WorkerEfficiencyReportService $workerService,
        private ProductAnalysisReportService $productService,
    ) {}

    /**
     * Yönetici özet raporu oluştur
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generate(string $startDate, string $endDate): array
    {
        $currentPeriodData = $this->getCurrentPeriodData($startDate, $endDate);
        $previousPeriodData = $this->getPreviousPeriodData($startDate, $endDate);
        $comparison = $this->comparePerformance($currentPeriodData, $previousPeriodData);
        $topPerformers = $this->getTopPerformers($startDate, $endDate);
        $keyMetrics = $this->getKeyMetrics($startDate, $endDate);

        return [
            'period' => [
                'current' => [
                    'start_date' => DateHelper::formatTurkish($startDate, 'short'),
                    'end_date' => DateHelper::formatTurkish($endDate, 'short'),
                    'days' => DateHelper::getDaysBetween($startDate, $endDate),
                ],
                'previous' => $this->getPreviousPeriodInfo($startDate, $endDate),
            ],
            'current_period' => $currentPeriodData,
            'previous_period' => $previousPeriodData,
            'comparison' => $comparison,
            'top_performers' => $topPerformers,
            'key_metrics' => $keyMetrics,
        ];
    }

    /**
     * Mevcut dönem verilerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getCurrentPeriodData(string $startDate, string $endDate): array
    {
        $productions = $this->query->baseQuery()
            ->whereBetween('production_date', [$startDate, $endDate])
            ->get();

        return [
            'total_quantity' => $productions->sum('quantity'),
            'total_count' => $productions->count(),
            'unique_products' => $productions->pluck('product_id')->unique()->count(),
            'unique_workers' => $productions->pluck('user_id')->unique()->count(),
            'unique_machines' => $productions->pluck('machine_id')->unique()->count(),
        ];
    }

    /**
     * Önceki dönem verilerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getPreviousPeriodData(string $startDate, string $endDate): array
    {
        $previousPeriod = DateHelper::getPreviousPeriodRange($startDate, $endDate);
        
        $productions = $this->query->baseQuery()
            ->whereBetween('production_date', [$previousPeriod['start'], $previousPeriod['end']])
            ->get();

        return [
            'total_quantity' => $productions->sum('quantity'),
            'total_count' => $productions->count(),
            'unique_products' => $productions->pluck('product_id')->unique()->count(),
            'unique_workers' => $productions->pluck('user_id')->unique()->count(),
            'unique_machines' => $productions->pluck('machine_id')->unique()->count(),
        ];
    }

    /**
     * Performans karşılaştırması yap
     * 
     * @param array $current
     * @param array $previous
     * @return array
     */
    private function comparePerformance(array $current, array $previous): array
    {
        return [
            'quantity' => StatisticsHelper::growthRate(
                $current['total_quantity'],
                $previous['total_quantity']
            ),
            'count' => StatisticsHelper::growthRate(
                $current['total_count'],
                $previous['total_count']
            ),
            'products' => StatisticsHelper::growthRate(
                $current['unique_products'],
                $previous['unique_products']
            ),
            'workers' => StatisticsHelper::growthRate(
                $current['unique_workers'],
                $previous['unique_workers']
            ),
        ];
    }

    /**
     * En iyi performans gösterenleri getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getTopPerformers(string $startDate, string $endDate): array
    {
        $topWorkers = $this->query->topWorkers($startDate, $endDate, 5)
            ->get()
            ->map(fn($item) => [
                'user_id' => $item->user_id,
                'user_name' => $item->user->name ?? 'Bilinmeyen',
                'total_quantity' => (int) $item->total_quantity,
            ])
            ->toArray();

        $topProducts = $this->query->topProducts($startDate, $endDate, 5)
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Bilinmeyen',
                'total_quantity' => (int) $item->total_quantity,
            ])
            ->toArray();

        return [
            'top_workers' => $topWorkers,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Anahtar metrikleri getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getKeyMetrics(string $startDate, string $endDate): array
    {
        $productions = $this->query->baseQuery()
            ->whereBetween('production_date', [$startDate, $endDate])
            ->get();

        $days = DateHelper::getDaysBetween($startDate, $endDate);
        $totalQuantity = $productions->sum('quantity');

        return [
            'daily_average' => $this->calculateDailyAverage($totalQuantity, $days),
            'average_per_production' => $productions->count() > 0
                ? round($totalQuantity / $productions->count(), 2)
                : 0,
            'production_rate' => $days > 0
                ? round($productions->count() / $days, 2)
                : 0,
        ];
    }

    /**
     * Önceki dönem bilgisini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getPreviousPeriodInfo(string $startDate, string $endDate): array
    {
        $previousPeriod = DateHelper::getPreviousPeriodRange($startDate, $endDate);
        
        return [
            'start_date' => DateHelper::formatTurkish($previousPeriod['start'], 'short'),
            'end_date' => DateHelper::formatTurkish($previousPeriod['end'], 'short'),
            'days' => DateHelper::getDaysBetween($previousPeriod['start'], $previousPeriod['end']),
        ];
    }
}
