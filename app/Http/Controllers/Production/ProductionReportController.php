<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\ProductionReportRequest;
use App\Domains\Production\Services\Reports\DateRangeReportService;
use App\Domains\Production\Services\Reports\WorkerEfficiencyReportService;
use App\Domains\Production\Services\Reports\ProductAnalysisReportService;
use App\Domains\Production\Services\Reports\TrendAnalysisReportService;
use App\Domains\Production\Services\Reports\RealtimeDashboardService;
use App\Domains\Production\Services\Reports\ExecutiveSummaryService;
use App\Domains\Production\Services\Reports\WorkerDetailReportService;
use App\Domains\Production\DTOs\Reports\ReportFilterDTO;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * ProductionReportController - Üretim raporları controller'ı
 * 
 * Bu controller, yeni Report Service'leri kullanarak raporları sağlar.
 * Facade pattern yerine doğrudan dependency injection kullanılıyor.
 */
class ProductionReportController extends Controller
{
    public function __construct(
        private DateRangeReportService $dateRangeService,
        private WorkerEfficiencyReportService $workerEfficiencyService,
        private ProductAnalysisReportService $productAnalysisService,
        private TrendAnalysisReportService $trendAnalysisService,
        private RealtimeDashboardService $realtimeService,
        private ExecutiveSummaryService $executiveService,
        private WorkerDetailReportService $workerDetailService,
    ) {}

    /**
     * Tarih aralıklı üretim raporu
     * 
     * @param ProductionReportRequest $request
     * @return JsonResponse
     */
    public function dateRange(ProductionReportRequest $request): JsonResponse
    {
        try {
            $filter = ReportFilterDTO::fromRequest($request);
            
            // Eğer tarihler yoksa varsayılan değerler kullan
            if (!$request->has('start_date')) {
                $filter = new ReportFilterDTO(
                    startDate: Carbon::now()->subMonth()->format('Y-m-d'),
                    endDate: Carbon::now()->format('Y-m-d'),
                    productId: $request->input('product_id'),
                    userId: $request->input('user_id'),
                    machineId: $request->input('machine_id'),
                    shiftId: $request->input('shift_id'),
                );
            }
            
            $data = $this->dateRangeService->generate($filter);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * İşçi verimliliği raporu
     * 
     * @param ProductionReportRequest $request
     * @return JsonResponse
     */
    public function workerEfficiency(ProductionReportRequest $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $userId = $request->input('user_id');

            $data = $this->workerEfficiencyService->generate($startDate, $endDate, $userId);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ürün bazlı üretim raporu
     * 
     * @param ProductionReportRequest $request
     * @return JsonResponse
     */
    public function productAnalysis(ProductionReportRequest $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $productId = $request->input('product_id');

            $data = $this->productAnalysisService->generate($startDate, $endDate, $productId);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Üretim trend analizi
     * 
     * @param ProductionReportRequest $request
     * @return JsonResponse
     */
    public function trendAnalysis(ProductionReportRequest $request): JsonResponse
    {
        try {
            $year = $request->input('year');
            $month = $request->input('month');

            $data = $this->trendAnalysisService->generate($year, $month);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gerçek zamanlı üretim dashboard
     * 
     * @return JsonResponse
     */
    public function realtimeDashboard(): JsonResponse
    {
        try {
            $data = $this->realtimeService->generate();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dashboard verileri alınırken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kapsamlı özet rapor
     * 
     * @param ProductionReportRequest $request
     * @return JsonResponse
     */
    public function executiveSummary(ProductionReportRequest $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $data = $this->executiveService->generate($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * İşçi detay raporu
     * 
     * @param ProductionReportRequest $request
     * @param int $userId
     * @return JsonResponse
     */
    public function workerDetail(ProductionReportRequest $request, int $userId): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $data = $this->workerDetailService->generate($userId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapor oluşturulurken hata oluştu: ' . $e->getMessage(),
            ], $e->getMessage() === 'İşçi bulunamadı' ? 404 : 500);
        }
    }
}
