<?php

namespace App\Domains\Sales\Queries;

use Illuminate\Support\Facades\DB;

/**
 * SalesReportQuery - Satış raporları için özelleşmiş sorgular
 */
class SalesReportQuery extends SalesQuery
{
    public function dailyDistribution(string $startDate, string $endDate)
    {
        return DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sales.sale_date) as date'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total'),
                DB::raw('COUNT(DISTINCT sales.id) as count')
            )
            ->groupBy(DB::raw('DATE(sales.sale_date)'))
            ->orderBy('date');
    }

    public function topProducts(string $startDate, string $endDate, int $limit = 10)
    {
        return DB::table('sales_products')
            ->join('sales', 'sales_products.sales_id', '=', 'sales.id')
            ->join('products', 'sales_products.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id as product_id',
                'products.product_name',
                DB::raw('SUM(sales_products.quantity) as total_quantity'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_revenue')
            ->limit($limit);
    }

    public function paymentMethodDistribution(string $startDate, string $endDate)
    {
        return DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'sales.payment_type as payment_method',
                DB::raw('COUNT(DISTINCT sales.id) as count'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total')
            )
            ->groupBy('sales.payment_type');
    }

    public function monthlySales(int $year)
    {
        return DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereYear('sales.sale_date', $year)
            ->select(
                DB::raw('MONTH(sales.sale_date) as month'),
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total'),
                DB::raw('COUNT(DISTINCT sales.id) as count')
            )
            ->groupBy(DB::raw('MONTH(sales.sale_date)'))
            ->orderBy('month');
    }

    public function topCustomers(string $startDate, string $endDate, int $limit = 10)
    {
        return DB::table('sales')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'sales.customer_id',
                DB::raw('SUM(sales_products.quantity * sales_products.price) as total_spent'),
                DB::raw('COUNT(DISTINCT sales.id) as purchase_count')
            )
            ->groupBy('sales.customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit);
    }
}
