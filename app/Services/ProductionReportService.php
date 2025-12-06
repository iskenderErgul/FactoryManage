<?php

namespace App\Services;

use App\Domains\Production\Services\Reports\DateRangeReportService;
use App\Domains\Production\Services\Reports\WorkerEfficiencyReportService;
use App\Domains\Production\Services\Reports\ProductAnalysisReportService;
use App\Domains\Production\Services\Reports\TrendAnalysisReportService;
use App\Domains\Production\Services\Reports\RealtimeDashboardService;
use App\Domains\Production\Services\Reports\ExecutiveSummaryService;
use App\Domains\Production\Services\Reports\WorkerDetailReportService;
use App\Domains\Production\DTOs\Reports\ReportFilterDTO;

/**
 * ProductionReportService - Facade Pattern
 * 
 * Bu sınıf, eski API'yi korumak için Facade pattern kullanır.
 * Tüm metodlar yeni Report Service'lere delegate edilir.
 * 
 * @deprecated Bu sınıf backward compatibility için tutulmaktadır.
 *             Yeni kodlarda doğrudan Report Service'leri kullanın.
 */
class ProductionReportService
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
     * @deprecated Use DateRangeReportService::generate() directly
     */
    public function getDateRangeReport(string $startDate, string $endDate, array $filters = []): array
    {
        $filter = new ReportFilterDTO(
            startDate: $startDate,
            endDate: $endDate,
            productId: $filters['product_id'] ?? null,
            userId: $filters['user_id'] ?? null,
            machineId: $filters['machine_id'] ?? null,
            shiftId: $filters['shift_id'] ?? null,
        );
        
        return $this->dateRangeService->generate($filter);
    }

    /**
     * İşçi verimliliği raporu
     * 
     * @deprecated Use WorkerEfficiencyReportService::generate() directly
     */
    public function getWorkerEfficiencyReport(string $startDate, string $endDate, ?int $userId = null): array
    {
        return $this->workerEfficiencyService->generate($startDate, $endDate, $userId);
    }

    /**
     * Ürün bazlı üretim raporu
     * 
     * @deprecated Use ProductAnalysisReportService::generate() directly
     */
    public function getProductAnalysisReport(string $startDate, string $endDate, ?int $productId = null): array
    {
        return $this->productAnalysisService->generate($startDate, $endDate, $productId);
    }

    /**
     * Üretim trend analizi
     * 
     * @deprecated Use TrendAnalysisReportService::generate() directly
     */
    public function getTrendAnalysisReport(?int $year = null, ?int $month = null): array
    {
        return $this->trendAnalysisService->generate($year, $month);
    }

    /**
     * Gerçek zamanlı üretim dashboard
     * 
     * @deprecated Use RealtimeDashboardService::generate() directly
     */
    public function getRealtimeDashboard(): array
    {
        return $this->realtimeService->generate();
    }

    /**
     * Kapsamlı özet rapor
     * 
     * @deprecated Use ExecutiveSummaryService::generate() directly
     */
    public function getExecutiveSummary(string $startDate, string $endDate): array
    {
        return $this->executiveService->generate($startDate, $endDate);
    }

    /**
     * İşçi detay raporu
     * 
     * @deprecated Use WorkerDetailReportService::generate() directly
     */
    public function getWorkerDetailReport(int $userId, string $startDate, string $endDate): array
    {
        return $this->workerDetailService->generate($userId, $startDate, $endDate);
    }
}
