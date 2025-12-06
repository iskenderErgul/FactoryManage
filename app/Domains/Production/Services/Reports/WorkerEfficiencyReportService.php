<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;

/**
 * WorkerEfficiencyReportService - İşçi verimliliği raporları
 * 
 * Bu service, işçilerin üretim performansını analiz eder.
 * Verimlilik metrikleri, top işçiler ve trend analizi sağlar.
 */
class WorkerEfficiencyReportService
{
    use HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * İşçi verimliliği raporu oluştur
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return array
     */
    public function generate(string $startDate, string $endDate, ?int $userId = null): array
    {
        $workerStats = $this->getWorkerStats($startDate, $endDate, $userId);
        $topWorkers = $workerStats->take(10)->values();
        $workerTrend = $this->getWorkerTrend($startDate, $endDate, $userId);

        return [
            'period' => [
                'start_date' => DateHelper::formatTurkish($startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($endDate, 'short'),
                'days' => DateHelper::getDaysBetween($startDate, $endDate),
            ],
            'worker_stats' => $workerStats->toArray(),
            'top_workers' => $topWorkers->toArray(),
            'worker_trend' => $workerTrend,
            'total_workers' => $workerStats->count(),
        ];
    }

    /**
     * İşçi istatistiklerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return \Illuminate\Support\Collection
     */
    private function getWorkerStats(string $startDate, string $endDate, ?int $userId = null)
    {
        return $this->query
            ->workerStats($startDate, $endDate, $userId)
            ->get()
            ->map(function ($stat) {
                return [
                    'user_id' => $stat->user_id,
                    'user_name' => $stat->user->name ?? 'Bilinmeyen',
                    'production_count' => $stat->production_count,
                    'total_quantity' => (int) $stat->total_quantity,
                    'average_quantity' => round($stat->average_quantity, 2),
                    'work_days' => $stat->work_days,
                    'daily_average' => $this->calculateDailyAverage(
                        $stat->total_quantity,
                        $stat->work_days
                    ),
                    'efficiency_score' => $this->calculateEfficiencyScore($stat),
                ];
            });
    }

    /**
     * İşçi trendini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return array
     */
    private function getWorkerTrend(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = $this->query->baseQuery();
        $query = $this->query->byDateRange($query, $startDate, $endDate);
        
        if ($userId) {
            $query = $this->query->byUser($query, $userId);
        }

        $trendData = $query
            ->selectRaw('DATE(production_date) as date, SUM(quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => DateHelper::formatTurkish($item->date, 'chart'),
                'total' => (int) $item->total,
            ])
            ->toArray();

        $values = array_column($trendData, 'total');
        $trend = StatisticsHelper::analyzeTrend($values);

        // Frontend direkt array bekliyor
        return $trendData;
    }

    /**
     * Verimlilik skoru hesapla
     * 
     * @param object $stat
     * @return float
     */
    private function calculateEfficiencyScore($stat): float
    {
        // Basit bir verimlilik skoru: günlük ortalama * çalışma günleri / 100
        $dailyAvg = $stat->work_days > 0 ? $stat->total_quantity / $stat->work_days : 0;
        return round(($dailyAvg * $stat->work_days) / 100, 2);
    }
}
