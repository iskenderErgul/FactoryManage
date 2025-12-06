<?php

namespace App\Domains\Production\Services\Reports;

use App\Common\Helpers\DateHelper;
use App\Common\Helpers\StatisticsHelper;
use App\Common\Traits\HasDateFilters;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Queries\ProductionReportQuery;
use App\Domains\Production\Models\Production;
use App\Domains\Product\Models\Product;
use App\Domains\Users\Models\User;
use App\Domains\Machines\Models\Machine;
use Illuminate\Support\Facades\DB;

/**
 * WorkerDetailReportService - İşçi detay raporları
 * 
 * Bu service, belirli bir işçinin detaylı üretim analizini sağlar.
 * Ürün breakdown, günlük trend, aylık performans, makine ve vardiya bazlı analiz içerir.
 */
class WorkerDetailReportService
{
    use HasDateFilters, HasStatistics;

    public function __construct(
        private ProductionReportQuery $query
    ) {}

    /**
     * İşçi detay raporu oluştur
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generate(int $userId, string $startDate, string $endDate): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            throw new \Exception('İşçi bulunamadı');
        }

        $productions = $this->getWorkerProductions($userId, $startDate, $endDate);
        $summary = $this->calculateWorkerSummary($productions, $startDate, $endDate);
        $productBreakdown = $this->getProductBreakdown($userId, $startDate, $endDate);
        $dailyTrend = $this->getDailyTrend($userId, $startDate, $endDate);
        $monthlyPerformance = $this->getMonthlyPerformance($userId, $startDate, $endDate);
        $machineBreakdown = $this->getMachineBreakdown($userId, $startDate, $endDate);
        $shiftPerformance = $this->getShiftPerformance($userId, $startDate, $endDate);

        return [
            'worker' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'period' => [
                'start_date' => DateHelper::formatTurkish($startDate, 'short'),
                'end_date' => DateHelper::formatTurkish($endDate, 'short'),
                'days' => DateHelper::getDaysBetween($startDate, $endDate),
            ],
            'summary' => $summary,
            'product_breakdown' => $productBreakdown,
            'daily_trend' => $dailyTrend,
            'monthly_performance' => $monthlyPerformance,
            'machine_breakdown' => $machineBreakdown,
            'shift_performance' => $shiftPerformance,
        ];
    }

    /**
     * İşçinin üretimlerini getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getWorkerProductions(int $userId, string $startDate, string $endDate)
    {
        return Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with(['product', 'machine', 'shift.template'])
            ->get();
    }

    /**
     * İşçi özet istatistiklerini hesapla
     * 
     * @param \Illuminate\Support\Collection $productions
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function calculateWorkerSummary($productions, string $startDate, string $endDate): array
    {
        $totalQuantity = $productions->sum('quantity');
        $totalCount = $productions->count();
        $workDays = $productions->pluck('production_date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->count();

        $dailyAverage = $this->calculateDailyAverage($totalQuantity, $workDays);

        return [
            'total_quantity' => $totalQuantity,
            'production_count' => $totalCount,
            'work_days' => $workDays,
            'daily_average' => $dailyAverage,
            'unique_products' => $productions->pluck('product_id')->unique()->count(),
            'unique_machines' => $productions->pluck('machine_id')->unique()->count(),
            'average_per_production' => $totalCount > 0 ? round($totalQuantity / $totalCount, 2) : 0,
        ];
    }

    /**
     * Ürün bazlı breakdown getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getProductBreakdown(int $userId, string $startDate, string $endDate): array
    {
        return Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select('product_id', DB::raw('SUM(quantity) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $product->product_name ?? 'Bilinmeyen',
                    'product_type' => $product->product_type ?? null,
                    'total_quantity' => (int) $item->total,
                    'production_count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Günlük üretim trendini getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getDailyTrend(int $userId, string $startDate, string $endDate): array
    {
        $trendData = Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(quantity) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(production_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => DateHelper::formatTurkish($item->date, 'short'),
                'date_raw' => $item->date,
                'total' => (int) $item->total,
                'count' => $item->count,
            ])
            ->toArray();

        // Trend analizi ekle
        $values = array_column($trendData, 'total');
        $analysis = StatisticsHelper::analyzeTrend($values);

        // Frontend direkt array bekliyor
        return $trendData;
    }

    /**
     * Aylık performansı getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getMonthlyPerformance(int $userId, string $startDate, string $endDate): array
    {
        return Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(production_date) as year'),
                DB::raw('MONTH(production_date) as month'),
                DB::raw('SUM(quantity) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT DATE(production_date)) as work_days')
            )
            ->groupBy(DB::raw('YEAR(production_date)'), DB::raw('MONTH(production_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => DateHelper::getMonthNameTurkish($item->month),
                    'total_quantity' => (int) $item->total,
                    'production_count' => $item->count,
                    'work_days' => $item->work_days,
                    'daily_average' => $this->calculateDailyAverage($item->total, $item->work_days),
                ];
            })
            ->toArray();
    }

    /**
     * Makine bazlı üretimi getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getMachineBreakdown(int $userId, string $startDate, string $endDate): array
    {
        return Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select('machine_id', DB::raw('SUM(quantity) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('machine_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $machine = Machine::find($item->machine_id);
                return [
                    'machine_id' => $item->machine_id,
                    'machine_name' => $machine->machine_name ?? 'Bilinmeyen',
                    'total_quantity' => (int) $item->total,
                    'production_count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Vardiya bazlı performansı getir
     * 
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getShiftPerformance(int $userId, string $startDate, string $endDate): array
    {
        return Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with('shift.template')
            ->get()
            ->groupBy(fn($p) => $p->shift->template->name ?? 'Bilinmeyen')
            ->map(function ($items, $shiftName) {
                return [
                    'shift_name' => $shiftName,
                    'total_quantity' => $items->sum('quantity'),
                    'production_count' => $items->count(),
                    'average_per_production' => $items->count() > 0 
                        ? round($items->sum('quantity') / $items->count(), 2) 
                        : 0,
                ];
            })
            ->values()
            ->toArray();
    }
}
