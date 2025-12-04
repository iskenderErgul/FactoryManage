<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\ProductionReportRequest;
use App\Services\ProductionReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ProductionReportController extends Controller
{
    private ProductionReportService $reportService;

    public function __construct(ProductionReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Tarih aralıklı üretim raporu
     */
    public function dateRange(ProductionReportRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $filters = $request->only(['product_id', 'user_id', 'machine_id']);

        $data = $this->reportService->getDateRangeReport($startDate, $endDate, $filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * İşçi verimliliği raporu
     */
    public function workerEfficiency(ProductionReportRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $data = $this->reportService->getWorkerEfficiencyReport($startDate, $endDate, $userId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Ürün bazlı üretim raporu
     */
    public function productAnalysis(ProductionReportRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $productId = $request->input('product_id');

        $data = $this->reportService->getProductAnalysisReport($startDate, $endDate, $productId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Üretim trend analizi
     */
    public function trendAnalysis(ProductionReportRequest $request): JsonResponse
    {
        $year = $request->input('year');
        $month = $request->input('month');

        $data = $this->reportService->getTrendAnalysisReport($year, $month);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Gerçek zamanlı üretim dashboard
     */
    public function realtimeDashboard(): JsonResponse
    {
        $data = $this->reportService->getRealtimeDashboard();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Kapsamlı özet rapor
     */
    public function executiveSummary(ProductionReportRequest $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->reportService->getExecutiveSummary($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * İşçi detay raporu
     */
    public function workerDetail(ProductionReportRequest $request, int $userId): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = $this->reportService->getWorkerDetailReport($userId, $startDate, $endDate);

        if (isset($data['error'])) {
            return response()->json([
                'success' => false,
                'message' => $data['error'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
