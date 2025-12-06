<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesReportRequest;
use App\Domains\Sales\Services\Reports\DateRangeSalesReportService;
use App\Domains\Sales\Services\Reports\CustomerSalesReportService;
use App\Domains\Sales\Services\Reports\MonthlySalesReportService;
use App\Domains\Sales\Services\Reports\TrendSalesReportService;
use App\Domains\Sales\DTOs\Reports\SalesReportFilterDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalesReportController extends Controller
{
    public function __construct(
        private DateRangeSalesReportService $dateRangeService,
        private CustomerSalesReportService $customerService,
        private MonthlySalesReportService $monthlyService,
        private TrendSalesReportService $trendService,
    ) {}

    /**
     * Tarih Aralıklı Satış Raporu
     */
    public function dateRangeReport(Request $request): JsonResponse
    {
        try {
            $filter = SalesReportFilterDTO::fromRequest($request);
            $data = $this->dateRangeService->generate($filter);

            return response()->json([
                'success' => true,
                'data' => $data,
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
    public function monthlyReport(Request $request): JsonResponse
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('n'));

            $data = $this->monthlyService->generate((int) $year, (int) $month);

            return response()->json([
                'success' => true,
                'data' => $data,
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
    public function customerReport(Request $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->customerService->generate((int) $customerId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Müşteri Ürün Raporu (Eski API uyumluluğu için)
     */
    public function customerProductsReport(Request $request): JsonResponse
    {
        // customerReport ile aynı veriyi döndür
        return $this->customerReport($request);
    }

    /**
     * Müşteri Ödeme Raporu (Eski API uyumluluğu için)
     */
    public function customerPaymentsReport(Request $request): JsonResponse
    {
        // customerReport ile aynı veriyi döndür
        return $this->customerReport($request);
    }

    /**
     * Trend Raporu
     */
    public function trendsReport(): JsonResponse
    {
        try {
            $data = $this->trendService->generate();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
}
