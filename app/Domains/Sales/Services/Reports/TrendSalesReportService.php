<?php

namespace App\Domains\Sales\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Sales\Queries\SalesReportQuery;

/**
 * TrendSalesReportService - Satış trend analizi raporları
 */
class TrendSalesReportService
{
    use HasStatistics;

    public function __construct(
        private SalesReportQuery $query
    ) {}

    public function generate(?int $year = null): array
    {
        $year = $year ?? now()->year;

        $monthlyData = $this->getMonthlyData($year);
        $yearlyGrowth = $this->getYearlyGrowth($year);
        $trendAnalysis = $this->analyzeTrend($monthlyData);
        $last12Months = $this->getLast12Months();
        $yearOverYear = $this->getYearOverYearComparison();

        return [
            'year' => $year,
            'monthly_data' => $monthlyData,
            'yearly_growth' => $yearlyGrowth,
            'trend_analysis' => $trendAnalysis,
            'last_12_months' => $last12Months,
            'year_over_year' => $yearOverYear,
        ];
    }

    private function getYearOverYearComparison(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $currentMonthSales = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $currentYear)
            ->whereMonth('sales.sale_date', $currentMonth)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        $previousYearMonthSales = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $currentYear - 1)
            ->whereMonth('sales.sale_date', $currentMonth)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        $difference = $currentMonthSales - $previousYearMonthSales;
        $percentageChange = $previousYearMonthSales > 0 
            ? round(($difference / $previousYearMonthSales) * 100, 2) 
            : ($currentMonthSales > 0 ? 100 : 0);

        return [
            'current_month_sales' => (float) $currentMonthSales,
            'previous_year_month_sales' => (float) $previousYearMonthSales,
            'difference' => (float) $difference,
            'percentage_change' => $percentageChange,
        ];
    }

    private function getMonthlyData(int $year): array
    {
        return $this->query->monthlySales($year)
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'month_name' => DateHelper::getMonthNameTurkish($item->month),
                'total' => (float) $item->total,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getYearlyGrowth(int $year): array
    {
        $currentYear = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        $previousYear = \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year - 1)
            ->sum(\DB::raw('sales_products.quantity * sales_products.price'));

        return StatisticsHelper::growthRate($currentYear, $previousYear);
    }

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

    private function getLast12Months(): array
    {
        $endDate = now();
        $startDate = now()->subMonths(12);

        return \DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                \DB::raw('YEAR(sales.sale_date) as year'),
                \DB::raw('MONTH(sales.sale_date) as month'),
                \DB::raw('SUM(sales_products.quantity * sales_products.price) as total')
            )
            ->groupBy(\DB::raw('YEAR(sales.sale_date)'), \DB::raw('MONTH(sales.sale_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'year' => $item->year,
                'month' => $item->month,
                'month_name' => DateHelper::getMonthNameTurkish($item->month),
                'total' => (float) $item->total,
            ])
            ->toArray();
    }
}
