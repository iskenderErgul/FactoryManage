<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;
use App\Domains\Product\Models\Product;

/**
 * ProductAnalysisReportService - Ürün bazlı analiz raporları
 * 
 * Bu service, ürünlerin üretim performansını analiz eder.
 * Ürün istatistikleri, trend ve dağılım analizi sağlar.
 */
class ProductAnalysisReportService
{
    use HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * Ürün analiz raporu oluştur
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $productId
     * @return array
     */
    public function generate(string $startDate, string $endDate, ?int $productId = null): array
    {
        $productStats = $this->getProductStats($startDate, $endDate, $productId);
        $productTrend = $this->getProductTrend($startDate, $endDate, $productId);
        $productDistribution = $this->getProductDistribution($productStats);

        return [
            'period' => [
                'start_date' => DateHelper::formatTurkish($startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($endDate, 'short'),
                'days' => DateHelper::getDaysBetween($startDate, $endDate),
            ],
            'product_stats' => $productStats->toArray(),
            'product_trend' => $productTrend,
            'product_distribution' => $productDistribution,
            'total_products' => $productStats->count(),
        ];
    }

    /**
     * Ürün istatistiklerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $productId
     * @return \Illuminate\Support\Collection
     */
    private function getProductStats(string $startDate, string $endDate, ?int $productId = null)
    {
        return $this->query
            ->productStats($startDate, $endDate, $productId)
            ->get()
            ->map(function ($stat) {
                $product = Product::find($stat->product_id);
                return [
                    'product_id' => $stat->product_id,
                    'product_name' => $product->product_name ?? 'Bilinmeyen',
                    'product_type' => $product->product_type ?? null,
                    'production_count' => $stat->production_count,
                    'total_quantity' => (int) $stat->total_quantity,
                    'average_quantity' => round($stat->average_quantity, 2),
                    'min_quantity' => (int) $stat->min_quantity,
                    'max_quantity' => (int) $stat->max_quantity,
                ];
            });
    }

    /**
     * Ürün trendini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $productId
     * @return array
     */
    private function getProductTrend(string $startDate, string $endDate, ?int $productId = null): array
    {
        $query = $this->query->baseQuery();
        $query = $this->query->byDateRange($query, $startDate, $endDate);
        
        if ($productId) {
            $query = $this->query->byProduct($query, $productId);
        }

        $trendData = $query
            ->selectRaw('DATE(production_date) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => DateHelper::formatTurkish($item->date, 'chart'),
                'total' => (int) $item->total,
            ])
            ->toArray();

        $values = array_column($trendData, 'total');
        $trend = StatisticsHelper::analyzeTrend($values);

        // Frontend direkt array bekliyor
        return $trendData;
    }

    /**
     * Ürün dağılımını hesapla
     * 
     * @param \Illuminate\Support\Collection $productStats
     * @return array
     */
    private function getProductDistribution($productStats): array
    {
        $total = $productStats->sum('total_quantity');

        return $productStats->map(function($stat) use ($total) {
            return [
                'product_id' => $stat['product_id'],
                'product_name' => $stat['product_name'],
                'total_quantity' => $stat['total_quantity'],
                'percentage' => $this->calculatePercentage($stat['total_quantity'], $total, 1),
            ];
        })->toArray();
    }
}
