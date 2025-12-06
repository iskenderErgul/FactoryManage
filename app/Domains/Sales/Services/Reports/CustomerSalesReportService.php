<?php

namespace App\Domains\Sales\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Sales\Queries\SalesReportQuery;
use App\Domains\Sales\Models\Sales;
use App\Domains\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * CustomerSalesReportService - Müşteri bazlı satış raporları
 */
class CustomerSalesReportService
{
    use HasStatistics;

    public function __construct(
        private SalesReportQuery $query
    ) {}

    public function generate(int $customerId, ?string $startDate = null, ?string $endDate = null): array
    {
        $customer = Customer::find($customerId);
        
        if (!$customer) {
            throw new \Exception('Müşteri bulunamadı');
        }

        $startDate = $startDate ?? now()->subYear()->format('Y-m-d');
        $endDate = $endDate ?? now()->format('Y-m-d');

        $summary = $this->getCustomerSummary($customerId, $startDate, $endDate);
        $products = $this->getCustomerProducts($customerId, $startDate, $endDate);
        $spendingChart = $this->getSpendingChart($customerId);
        $purchaseHabits = $this->getPurchaseHabits($customerId);

        return [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'period' => [
                'start_date' => DateHelper::formatTurkish($startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($endDate, 'short'),
            ],
            'summary' => $summary,
            'products' => $products,
            'spending_chart' => $spendingChart,
            'purchase_habits' => $purchaseHabits,
        ];
    }

    private function getCustomerSummary(int $customerId, string $startDate, string $endDate): array
    {
        $sales = Sales::where('customer_id', $customerId)
            ->with('salesProducts')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $totalSpent = 0;
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalSpent += $product->quantity * $product->price;
            }
        }

        return [
            'total_spent' => $totalSpent,
            'total_purchases' => $sales->count(),
            'average_per_purchase' => $sales->count() > 0 
                ? round($totalSpent / $sales->count(), 2) 
                : 0,
        ];
    }

    private function getCustomerProducts(int $customerId, string $startDate, string $endDate): array
    {
        return DB::table('sales_products')
            ->join('sales', 'sales_products.sales_id', '=', 'sales.id')
            ->join('products', 'sales_products.product_id', '=', 'products.id')
            ->where('sales.customer_id', $customerId)
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.product_name',
                DB::raw('SUM(sales_products.quantity) as total_quantity'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total_spent')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_spent')
            ->get()
            ->toArray();
    }

    private function getSpendingChart(int $customerId): array
    {
        return DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->where('sales.customer_id', $customerId)
            ->select(
                DB::raw('YEAR(sales.sale_date) as year'),
                DB::raw('MONTH(sales.sale_date) as month'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total')
            )
            ->groupBy(DB::raw('YEAR(sales.sale_date)'), DB::raw('MONTH(sales.sale_date)'))
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

    private function getPurchaseHabits(int $customerId): array
    {
        $sales = Sales::where('customer_id', $customerId)->with('salesProducts')->get();
        
        if ($sales->isEmpty()) {
            return [
                'most_used_payment_method' => null,
                'average_purchase_value' => 0,
                'purchase_frequency' => 'Veri yok',
            ];
        }

        $paymentMethods = $sales->groupBy('payment_type')
            ->map(fn($group) => $group->count())
            ->sortDesc();

        $totalSpent = 0;
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalSpent += $product->quantity * $product->price;
            }
        }

        return [
            'most_used_payment_method' => $paymentMethods->keys()->first(),
            'average_purchase_value' => $sales->count() > 0 ? round($totalSpent / $sales->count(), 2) : 0,
            'purchase_frequency' => $this->calculateFrequency($sales),
        ];
    }

    private function calculateFrequency($sales): string
    {
        if ($sales->count() < 2) return 'Yetersiz veri';
        
        $firstDate = $sales->min('sale_date');
        $lastDate = $sales->max('sale_date');
        $daysDiff = \Carbon\Carbon::parse($firstDate)->diffInDays($lastDate);
        
        if ($daysDiff == 0) return 'Tek gün';
        
        $avgDays = $daysDiff / ($sales->count() - 1);
        
        if ($avgDays < 7) return 'Çok sık (Haftalık)';
        if ($avgDays < 30) return 'Sık (Aylık)';
        if ($avgDays < 90) return 'Orta (3 Ayda bir)';
        return 'Seyrek (Yılda birkaç kez)';
    }
}
