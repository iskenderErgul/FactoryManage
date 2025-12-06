<?php

namespace App\Domains\Sales\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasDateFilters;
use App\Common\Traits\HasStatistics;
use App\Domains\Sales\Queries\SalesReportQuery;
use App\Domains\Sales\DTOs\Reports\SalesReportFilterDTO;

/**
 * DateRangeSalesReportService - Tarih aralıklı satış raporları
 */
class DateRangeSalesReportService
{
    use HasDateFilters, HasStatistics;

    public function __construct(
        private SalesReportQuery $query
    ) {}

    public function generate(SalesReportFilterDTO $filter): array
    {
        $summary = $this->getSummary($filter->startDate, $filter->endDate);
        $dailyDistribution = $this->getDailyDistribution($filter->startDate, $filter->endDate);
        $topProducts = $this->getTopProducts($filter->startDate, $filter->endDate);
        $paymentMethods = $this->getPaymentMethodDistribution($filter->startDate, $filter->endDate);

        return [
            'period' => [
                'start_date' => DateHelper::formatTurkish($filter->startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($filter->endDate, 'short'),
                'days' => DateHelper::getDaysBetween($filter->startDate, $filter->endDate),
            ],
            'summary' => $summary,
            'daily_distribution' => $dailyDistribution,
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
        ];
    }

    private function getSummary(string $startDate, string $endDate): array
    {
        $sales = $this->query->baseQuery()
            ->with('salesProducts')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $totalAmount = 0;
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalAmount += $product->quantity * $product->price;
            }
        }

        $totalCount = $sales->count();
        $days = DateHelper::getDaysBetween($startDate, $endDate);

        return [
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'daily_average' => $this->calculateDailyAverage($totalAmount, $days),
            'average_per_sale' => $totalCount > 0 ? round($totalAmount / $totalCount, 2) : 0,
            'unique_customers' => $sales->pluck('customer_id')->unique()->count(),
        ];
    }

    private function getDailyDistribution(string $startDate, string $endDate): array
    {
        return $this->query->dailyDistribution($startDate, $endDate)
            ->get()
            ->map(fn($item) => [
                'date' => DateHelper::formatTurkish($item->date, 'short'),
                'total' => (float) $item->total,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getTopProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        return $this->query->topProducts($startDate, $endDate, $limit)
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'total_quantity' => (int) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ])
            ->toArray();
    }

    private function getPaymentMethodDistribution(string $startDate, string $endDate): array
    {
        $data = $this->query->paymentMethodDistribution($startDate, $endDate)->get();
        $total = $data->sum('total');

        return $data->map(function($item) use ($total) {
            return [
                'payment_method' => $item->payment_method,
                'count' => $item->count,
                'total' => (float) $item->total,
                'percentage' => $this->calculatePercentage($item->total, $total, 1),
            ];
        })->toArray();
    }
}
