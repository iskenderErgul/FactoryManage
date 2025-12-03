<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesReportRequest;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesReportController extends Controller
{
    protected $salesReportService;

    public function __construct(SalesReportService $salesReportService)
    {
        $this->salesReportService = $salesReportService;
    }

    /**
     * Tarih Aralıklı Satış Raporu
     */
    public function dateRangeReport(SalesReportRequest $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $summary = $this->salesReportService->getDateRangeSummary($startDate, $endDate);
            $dailyDistribution = $this->salesReportService->getDailySalesDistribution($startDate, $endDate);
            $topProducts = $this->salesReportService->getTopProducts($startDate, $endDate);
            $paymentDistribution = $this->salesReportService->getPaymentMethodDistribution($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'daily_distribution' => $dailyDistribution,
                    'top_products' => $topProducts,
                    'payment_distribution' => $paymentDistribution,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aylık Satış Raporu
     */
    public function monthlyReport(SalesReportRequest $request): JsonResponse
    {
        try {
            $year = $request->input('year');
            $month = $request->input('month');

            $summary = $this->salesReportService->getMonthlySummary($year, $month);
            $topCustomer = $this->salesReportService->getTopCustomerForMonth($year, $month);
            $topProduct = $this->salesReportService->getTopProductForMonth($year, $month);
            $trend = $this->salesReportService->getMonthlyTrend($year, $month);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'top_customer' => $topCustomer,
                    'top_product' => $topProduct,
                    'trend' => $trend,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Müşteri Bazlı Satış Raporu
     */
    public function customerReport(SalesReportRequest $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $summary = $this->salesReportService->getCustomerSales($customerId, $startDate, $endDate);
            $products = $this->salesReportService->getCustomerProducts($customerId, $startDate, $endDate);
            $spendingChart = $this->salesReportService->getCustomerSpendingChart($customerId);
            $purchaseHabits = $this->salesReportService->getCustomerPurchaseHabits($customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'products' => $products,
                    'spending_chart' => $spendingChart,
                    'purchase_habits' => $purchaseHabits,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Müşteri Ürün Bazlı Satış Raporu
     */
    public function customerProductsReport(SalesReportRequest $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            
            $filter = [];
            if ($request->has('start_date') && $request->has('end_date')) {
                $filter['start_date'] = $request->input('start_date');
                $filter['end_date'] = $request->input('end_date');
            } elseif ($request->has('year') && $request->has('month')) {
                $filter['year'] = $request->input('year');
                $filter['month'] = $request->input('month');
            }

            $productSales = $this->salesReportService->getCustomerProductSales($customerId, $filter);
            $topProducts = $this->salesReportService->getTopCustomerProducts($customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'product_sales' => $productSales,
                    'top_products' => $topProducts,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Müşteri Ödeme Raporu
     */
    public function customerPaymentsReport(SalesReportRequest $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $paymentSummary = $this->salesReportService->getCustomerPaymentSummary($customerId, $startDate, $endDate);
            $paymentChart = $this->salesReportService->getCustomerPaymentChart($customerId);
            $paymentHabits = $this->salesReportService->getCustomerPaymentHabits($customerId);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_summary' => $paymentSummary,
                    'payment_chart' => $paymentChart,
                    'payment_habits' => $paymentHabits,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trend Raporu
     */
    public function trendsReport(): JsonResponse
    {
        try {
            $last12Months = $this->salesReportService->getLast12MonthsSales();
            $currentYear = date('Y');
            $yearlyGrowth = $this->salesReportService->getYearlyGrowthRate($currentYear);
            $currentMonth = date('n');
            $yearOverYear = $this->salesReportService->getYearOverYearComparison($currentYear, $currentMonth);
            $trendAnalysis = $this->salesReportService->getSalesTrendAnalysis();

            return response()->json([
                'success' => true,
                'data' => [
                    'last_12_months' => $last12Months,
                    'yearly_growth' => $yearlyGrowth,
                    'year_over_year' => $yearOverYear,
                    'trend_analysis' => $trendAnalysis,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PDF Export
     */
    public function exportPdf(SalesReportRequest $request)
    {
        try {
            // PDF export fonksiyonu daha sonra eklenecek
            return response()->json([
                'success' => false,
                'message' => 'PDF export özelliği yakında eklenecek.',
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PDF oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Excel Export
     */
    public function exportExcel(SalesReportRequest $request)
    {
        try {
            // Excel export fonksiyonu daha sonra eklenecek
            return response()->json([
                'success' => false,
                'message' => 'Excel export özelliği yakında eklenecek.',
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excel oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
}
