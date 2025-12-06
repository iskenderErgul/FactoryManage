<?php

namespace App\Domains\Sales\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Sales\Queries\SalesReportQuery;

/**
 * MonthlySalesReportService - Aylık satış raporları
 */
class MonthlySalesReportService
{
    use HasStatistics;

    public function __construct(
        private SalesReportQuery $query
    ) {}

    public function generate(int $year, int $month): array
    {
        $summary = $this->getMonthlySummary($year, $month);
        $topCustomer = $this->getTopCustomer($year, $month);
        $topProduct = $this->getTopProduct($year, $month);
        $dailyTrend = $this->getDailyTrend($year, $month);
        $yearOverYear = $this->getYearOverYearComparison($year, $month);

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => DateHelper::getMonthNameTurkish($month),
            'summary' => $summary,
            'top_customer' => $topCustomer,
            'top_product' => $topProduct,
            'daily_trend' => $dailyTrend,
            'year_over_year' => $yearOverYear,
        ];
    }

    private function getMonthlySummary(int $year, int $month): array
    {
        $sales = $this->query->baseQuery()
            ->with('salesProducts')
            ->whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month)
            ->get();

        $totalAmount = 0;
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalAmount += $product->quantity * $product->price;
            }
        }

        return [
            'total_amount' => $totalAmount,
            'total_count' => $sales->count(),
            'average_per_sale' => $sales->count() > 0 
                ? round($totalAmount / $sales->count(), 2) 
                : 0,
        ];
    }

    private function getTopCustomer(int $year, int $month): ?array
    {
        $topCustomer = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year)
            ->whereMonth('sales.sale_date', $month)
            ->select('sales.customer_id', \DB::raw('SUM(sales_products.quantity * sales_products.price) as total'))
            ->groupBy('sales.customer_id')
            ->orderByDesc('total')
            ->first();

        if (!$topCustomer) return null;

        $customer = \App\Domains\Customer\Models\Customer::find($topCustomer->customer_id);

        return [
            'customer_id' => $topCustomer->customer_id,
            'customer_name' => $customer->name ?? 'Bilinmeyen',
            'total_spent' => (float) $topCustomer->total,
        ];
    }

    private function getTopProduct(int $year, int $month): ?array
    {
        $topProduct = \DB::table('sales_products')
            ->join('sales', 'sales_products.sales_id', '=', 'sales.id')
            ->join('products', 'sales_products.product_id', '=', 'products.id')
            ->whereYear('sales.sale_date', $year)
            ->whereMonth('sales.sale_date', $month)
            ->select(
                'products.id',
                'products.product_name',
                \DB::raw('SUM(sales_products.quantity * sales_products.price) as total')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total')
            ->first();

        return $topProduct ? [
            'product_id' => $topProduct->id,
            'product_name' => $topProduct->product_name,
            'total_revenue' => (float) $topProduct->total,
        ] : null;
    }

    private function getDailyTrend(int $year, int $month): array
    {
        return \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year)
            ->whereMonth('sales.sale_date', $month)
            ->select(
                \DB::raw('DAY(sales.sale_date) as day'),
                \DB::raw('SUM(sales_products.quantity * sales_products.price) as total')
            )
            ->groupBy(\DB::raw('DAY(sales.sale_date)'))
            ->orderBy('day')
            ->get()
            ->map(fn($item) => [
                'day' => $item->day,
                'total' => (float) $item->total,
            ])
            ->toArray();
    }

    private function getYearOverYearComparison(int $year, int $month): array
    {
        $currentYear = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year)
            ->whereMonth('sales.sale_date', $month)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        $previousYear = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year - 1)
            ->whereMonth('sales.sale_date', $month)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        return [
            'current_year' => [
                'year' => $year,
                'total' => (float) $currentYear,
            ],
            'previous_year' => [
                'year' => $year - 1,
                'total' => (float) $previousYear,
            ],
            'comparison' => StatisticsHelper::growthRate($currentYear, $previousYear),
        ];
    }
}
