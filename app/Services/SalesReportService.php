<?php

namespace App\Services;

use App\Domains\Sales\Services\Reports\DateRangeSalesReportService;
use App\Domains\Sales\Services\Reports\CustomerSalesReportService;
use App\Domains\Sales\Services\Reports\TrendSalesReportService;
use App\Domains\Sales\Services\Reports\MonthlySalesReportService;
use App\Domains\Sales\DTOs\Reports\SalesReportFilterDTO;

/**
 * SalesReportService - Facade Pattern
 * 
 * @deprecated Bu sınıf backward compatibility için tutulmaktadır.
 *             Yeni kodlarda doğrudan Report Service'leri kullanın.
 */
class SalesReportService
{
    public function __construct(
        private DateRangeSalesReportService $dateRangeService,
        private CustomerSalesReportService $customerService,
        private TrendSalesReportService $trendService,
        private MonthlySalesReportService $monthlyService,
    ) {}

    /**
     * @deprecated Use DateRangeSalesReportService::generate()
     */
    public function getDateRangeSummary($startDate, $endDate)
    {
        $filter = new SalesReportFilterDTO($startDate, $endDate);
        return $this->dateRangeService->generate($filter);
    }

    /**
     * @deprecated Use DateRangeSalesReportService
     */
    public function getDailySalesDistribution($startDate, $endDate)
    {
        $filter = new SalesReportFilterDTO($startDate, $endDate);
        $result = $this->dateRangeService->generate($filter);
        return $result['daily_distribution'];
    }

    /**
     * @deprecated Use DateRangeSalesReportService
     */
    public function getTopProducts($startDate, $endDate, $limit = 10)
    {
        $filter = new SalesReportFilterDTO($startDate, $endDate);
        $result = $this->dateRangeService->generate($filter);
        return array_slice($result['top_products'], 0, $limit);
    }

    /**
     * @deprecated Use DateRangeSalesReportService
     */
    public function getPaymentMethodDistribution($startDate, $endDate)
    {
        $filter = new SalesReportFilterDTO($startDate, $endDate);
        $result = $this->dateRangeService->generate($filter);
        return $result['payment_methods'];
    }

    /**
     * @deprecated Use MonthlySalesReportService::generate()
     */
    public function getMonthlySummary($year, $month)
    {
        return $this->monthlyService->generate($year, $month);
    }

    /**
     * @deprecated Use MonthlySalesReportService
     */
    public function getTopCustomerForMonth($year, $month)
    {
        $result = $this->monthlyService->generate($year, $month);
        return $result['top_customer'];
    }

    /**
     * @deprecated Use MonthlySalesReportService
     */
    public function getTopProductForMonth($year, $month)
    {
        $result = $this->monthlyService->generate($year, $month);
        return $result['top_product'];
    }

    /**
     * @deprecated Use MonthlySalesReportService
     */
    public function getMonthlyTrend($year, $month)
    {
        $result = $this->monthlyService->generate($year, $month);
        return $result['daily_trend'];
    }

    /**
     * @deprecated Use CustomerSalesReportService::generate()
     */
    public function getCustomerSales($customerId, $startDate = null, $endDate = null)
    {
        return $this->customerService->generate($customerId, $startDate, $endDate);
    }

    /**
     * @deprecated Use CustomerSalesReportService
     */
    public function getCustomerProducts($customerId, $startDate = null, $endDate = null)
    {
        $result = $this->customerService->generate($customerId, $startDate, $endDate);
        return $result['products'];
    }

    /**
     * @deprecated Use CustomerSalesReportService
     */
    public function getCustomerSpendingChart($customerId)
    {
        $result = $this->customerService->generate($customerId);
        return $result['spending_chart'];
    }

    /**
     * @deprecated Use CustomerSalesReportService
     */
    public function getCustomerPurchaseHabits($customerId)
    {
        $result = $this->customerService->generate($customerId);
        return $result['purchase_habits'];
    }

    /**
     * @deprecated Use TrendSalesReportService::generate()
     */
    public function getSalesTrendAnalysis()
    {
        return $this->trendService->generate();
    }

    /**
     * @deprecated Use TrendSalesReportService
     */
    public function getLast12MonthsSales()
    {
        $result = $this->trendService->generate();
        return $result['last_12_months'];
    }

    /**
     * @deprecated Use TrendSalesReportService
     */
    public function getYearlyGrowthRate($year)
    {
        $result = $this->trendService->generate($year);
        return $result['yearly_growth'];
    }

    /**
     * @deprecated Use MonthlySalesReportService
     */
    public function getYearOverYearComparison($year, $month)
    {
        $result = $this->monthlyService->generate($year, $month);
        return $result['year_over_year'];
    }

    /**
     * @deprecated Hesaplama artık service'lerde yapılıyor
     */
    public function calculateTotalSales($startDate, $endDate)
    {
        $filter = new SalesReportFilterDTO($startDate, $endDate);
        $result = $this->dateRangeService->generate($filter);
        return $result['summary']['total_amount'];
    }
}
