<?php

namespace App\Common\Traits;

use App\Common\Helpers\StatisticsHelper;

/**
 * HasStatistics Trait
 * 
 * Bu trait, istatistiksel hesaplamaları standartlaştırır.
 * Service sınıflarında kullanılabilir.
 */
trait HasStatistics
{
    /**
     * Ortalama hesaplar
     * 
     * @param array $values
     * @param int $decimals
     * @return float
     */
    protected function calculateAverage(array $values, int $decimals = 2): float
    {
        return StatisticsHelper::average($values, $decimals);
    }

    /**
     * Yüzde değişim hesaplar
     * 
     * @param float|int $oldValue
     * @param float|int $newValue
     * @param int $decimals
     * @return float
     */
    protected function calculatePercentageChange($oldValue, $newValue, int $decimals = 2): float
    {
        return StatisticsHelper::percentageChange($oldValue, $newValue, $decimals);
    }

    /**
     * Yüzde oran hesaplar
     * 
     * @param float|int $part
     * @param float|int $total
     * @param int $decimals
     * @return float
     */
    protected function calculatePercentage($part, $total, int $decimals = 2): float
    {
        return StatisticsHelper::percentage($part, $total, $decimals);
    }

    /**
     * Büyüme oranı hesaplar
     * 
     * @param float|int $currentValue
     * @param float|int $previousValue
     * @param int $decimals
     * @return array
     */
    protected function calculateGrowthRate($currentValue, $previousValue, int $decimals = 2): array
    {
        return StatisticsHelper::growthRate($currentValue, $previousValue, $decimals);
    }

    /**
     * Trend analizi yapar
     * 
     * @param array $values
     * @return array
     */
    protected function analyzeTrend(array $values): array
    {
        return StatisticsHelper::analyzeTrend($values);
    }

    /**
     * Günlük ortalama hesaplar
     * 
     * @param float|int $total
     * @param int $days
     * @param int $decimals
     * @return float
     */
    protected function calculateDailyAverage($total, int $days, int $decimals = 2): float
    {
        return StatisticsHelper::dailyAverage($total, $days, $decimals);
    }

    /**
     * Verimlilik hesaplar
     * 
     * @param float|int $actual
     * @param float|int $target
     * @param int $decimals
     * @return array
     */
    protected function calculateEfficiency($actual, $target, int $decimals = 2): array
    {
        return StatisticsHelper::efficiency($actual, $target, $decimals);
    }

    /**
     * Dağılım hesaplar
     * 
     * @param array $data
     * @param string $groupBy
     * @return array
     */
    protected function calculateDistribution(array $data, string $groupBy): array
    {
        return StatisticsHelper::distribution($data, $groupBy);
    }

    /**
     * Performans karşılaştırması yapar
     * 
     * @param array $current
     * @param array $previous
     * @return array
     */
    protected function comparePerformance(array $current, array $previous): array
    {
        return StatisticsHelper::comparePerformance($current, $previous);
    }

    /**
     * Top N değerleri döner
     * 
     * @param array $data
     * @param int $n
     * @param string $sortBy
     * @return array
     */
    protected function getTopN(array $data, int $n = 5, string $sortBy = 'value'): array
    {
        return StatisticsHelper::topN($data, $n, $sortBy);
    }

    /**
     * Özet istatistikler oluşturur
     * 
     * @param array $values
     * @return array
     */
    protected function generateSummaryStatistics(array $values): array
    {
        return [
            'total' => StatisticsHelper::sum($values),
            'average' => StatisticsHelper::average($values),
            'min' => StatisticsHelper::min($values),
            'max' => StatisticsHelper::max($values),
            'median' => StatisticsHelper::median($values),
            'std_dev' => StatisticsHelper::standardDeviation($values),
            'count' => count($values)
        ];
    }
}
