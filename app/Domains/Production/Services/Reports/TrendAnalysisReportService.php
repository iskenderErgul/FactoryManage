<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;
use Carbon\Carbon;

/**
 * TrendAnalysisReportService - Trend analizi raporları
 * 
 * Bu service, üretim trendlerini analiz eder.
 * Aylık, yıllık trendler ve yıl-yıl karşılaştırmalar sağlar.
 */
class TrendAnalysisReportService
{
    use HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * Trend analiz raporu oluştur
     * 
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function generate(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;

        $monthlyData = $this->getMonthlyData($year);
        $yearlyGrowth = $this->calculateYearlyGrowth($year);
        $trendAnalysis = $this->analyzeTrend($monthlyData);

        $result = [
            'year' => $year,
            'monthly_data' => $monthlyData,
            'yearly_growth' => $yearlyGrowth,
            'trend_analysis' => $trendAnalysis,
        ];

        // Eğer ay belirtilmişse, yıl-yıl karşılaştırma ekle
        if ($month) {
            $result['year_over_year'] = $this->getYearOverYearComparison($year, $month);
        }

        return $result;
    }

    /**
     * Aylık verileri getir
     * 
     * @param int $year
     * @return array
     */
    private function getMonthlyData(int $year): array
    {
        return $this->query
            ->monthlyTrend($year)
            ->get()
            ->map(fn($item) => [
                'month' => (int) $item->month,
                'month_name' => DateHelper::getMonthNameTurkish((int) $item->month),
                'total' => $item->total ? (int) $item->total : 0,
                'count' => $item->count ? (int) $item->count : 0,
                'average' => $item->average ? round((float) $item->average, 2) : 0,
            ])
            ->toArray();
    }

    /**
     * Yıllık büyümeyi hesapla
     * 
     * @param int $year
     * @return array
     */
    private function calculateYearlyGrowth(int $year): array
    {
        $currentYear = $this->query->baseQuery()
            ->whereYear('production_date', $year)
            ->sum('quantity') ?? 0;

        $previousYear = $this->query->baseQuery()
            ->whereYear('production_date', $year - 1)
            ->sum('quantity') ?? 0;

        return StatisticsHelper::growthRate($currentYear, $previousYear);
    }

    /**
     * Trend analizi yap
     * 
     * @param array $monthlyData
     * @return array
     */
    private function analyzeTrend(array $monthlyData): array
    {
        if (empty($monthlyData)) {
            return [
                'trend' => 'insufficient_data',
                'analysis' => 'Trend analizi için yeterli veri yok.',
            ];
        }

        $values = array_column($monthlyData, 'total');
        return StatisticsHelper::analyzeTrend($values);
    }

    /**
     * Yıl-yıl karşılaştırma
     * 
     * @param int $year
     * @param int $month
     * @return array
     */
    private function getYearOverYearComparison(int $year, int $month): array
    {
        $currentYearData = $this->query->baseQuery()
            ->whereYear('production_date', $year)
            ->whereMonth('production_date', $month)
            ->sum('quantity');

        $previousYearData = $this->query->baseQuery()
            ->whereYear('production_date', $year - 1)
            ->whereMonth('production_date', $month)
            ->sum('quantity');

        return [
            'current_year' => [
                'year' => $year,
                'month' => $month,
                'month_name' => DateHelper::getMonthNameTurkish($month),
                'total' => (int) $currentYearData,
            ],
            'previous_year' => [
                'year' => $year - 1,
                'month' => $month,
                'month_name' => DateHelper::getMonthNameTurkish($month),
                'total' => (int) $previousYearData,
            ],
            'comparison' => StatisticsHelper::growthRate($currentYearData, $previousYearData),
        ];
    }
}
