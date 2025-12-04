<?php

namespace App\Services;

use App\Domains\Production\Models\Production;
use App\Domains\Product\Models\Product;
use App\Domains\Users\Models\User;
use App\Domains\Shift\Models\ShiftTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionReportService
{
    /**
     * Tarih aralıklı üretim raporu
     */
    public function getDateRangeReport(string $startDate, string $endDate, array $filters = []): array
    {
        $query = $this->buildBaseQuery($startDate, $endDate, $filters);

        $productions = $query->get();

        $summary = $this->calculateSummary($productions, $startDate, $endDate);
        $dailyDistribution = $this->getDailyDistribution($startDate, $endDate, $filters);
        $topProducts = $this->getTopProducts($startDate, $endDate, $filters, 5);
        $hourlyAnalysis = $this->getHourlyAnalysis($startDate, $endDate, $filters);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
            'daily_distribution' => $dailyDistribution,
            'top_products' => $topProducts,
            'hourly_analysis' => $hourlyAnalysis,
        ];
    }

    /**
     * İşçi verimliliği raporu
     */
    public function getWorkerEfficiencyReport(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $workerStats = $query
            ->select(
                'user_id',
                DB::raw('COUNT(*) as production_count'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('AVG(quantity) as average_quantity'),
                DB::raw('COUNT(DISTINCT DATE(production_date)) as work_days')
            )
            ->groupBy('user_id')
            ->with('user:id,name')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function ($stat) {
                return [
                    'user_id' => $stat->user_id,
                    'user_name' => $stat->user->name ?? 'Bilinmeyen',
                    'production_count' => $stat->production_count,
                    'total_quantity' => (int) $stat->total_quantity,
                    'average_quantity' => round($stat->average_quantity, 2),
                    'work_days' => $stat->work_days,
                    'daily_average' => $stat->work_days > 0 
                        ? round($stat->total_quantity / $stat->work_days, 2) 
                        : 0,
                ];
            });

        $topWorkers = $workerStats->take(10)->values();
        
        $workerTrend = $this->getWorkerTrend($startDate, $endDate, $userId);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'worker_stats' => $workerStats,
            'top_workers' => $topWorkers,
            'worker_trend' => $workerTrend,
            'total_workers' => $workerStats->count(),
        ];
    }

    /**
     * Ürün bazlı üretim raporu
     */
    public function getProductAnalysisReport(string $startDate, string $endDate, ?int $productId = null): array
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $productStats = $query
            ->select(
                'product_id',
                DB::raw('COUNT(*) as production_count'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('AVG(quantity) as average_quantity'),
                DB::raw('MIN(quantity) as min_quantity'),
                DB::raw('MAX(quantity) as max_quantity')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function ($stat) {
                $product = Product::find($stat->product_id);
                return [
                    'product_id' => $stat->product_id,
                    'product_name' => $product->product_name ?? 'Bilinmeyen',
                    'product_type' => $product->product_type ?? null,
                    'production_count' => $stat->production_count,
                    'total_quantity' => (int) $stat->total_quantity,
                    'average_quantity' => round($stat->average_quantity, 2),
                    'min_quantity' => (int) $stat->min_quantity,
                    'max_quantity' => (int) $stat->max_quantity,
                ];
            });

        $productTrend = $this->getProductTrend($startDate, $endDate, $productId);
        $productDistribution = $this->getProductDistribution($productStats);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'product_stats' => $productStats,
            'product_trend' => $productTrend,
            'product_distribution' => $productDistribution,
            'total_products' => $productStats->count(),
        ];
    }

    /**
     * Üretim trend analizi
     */
    public function getTrendAnalysisReport(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? Carbon::now()->year;

        $monthlyData = Production::query()
            ->whereYear('production_date', $year)
            ->select(
                DB::raw('MONTH(production_date) as month'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as production_count'),
                DB::raw('COUNT(DISTINCT user_id) as worker_count')
            )
            ->groupBy(DB::raw('MONTH(production_date)'))
            ->orderBy('month')
            ->get()
            ->map(function ($data) {
                return [
                    'month' => $data->month,
                    'month_name' => $this->getMonthName($data->month),
                    'total_quantity' => (int) $data->total_quantity,
                    'production_count' => $data->production_count,
                    'worker_count' => $data->worker_count,
                ];
            });

        $yearlyGrowth = $this->calculateYearlyGrowth($year);
        $trendAnalysis = $this->analyzeTrend($monthlyData);

        $yearOverYear = null;
        if ($month) {
            $yearOverYear = $this->getYearOverYearComparison($year, $month);
        }

        return [
            'year' => $year,
            'month' => $month,
            'monthly_data' => $monthlyData,
            'yearly_growth' => $yearlyGrowth,
            'trend_analysis' => $trendAnalysis,
            'year_over_year' => $yearOverYear,
        ];
    }

    /**
     * Gerçek zamanlı üretim dashboard
     */
    public function getRealtimeDashboard(): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $todayProductions = Production::whereDate('production_date', $today)
            ->with(['user:id,name', 'product:id,product_name', 'machine:id,machine_name'])
            ->get();

        $todayTotal = $todayProductions->sum('quantity');
        $todayCount = $todayProductions->count();
        $activeWorkers = $todayProductions->pluck('user_id')->unique()->count();

        $hourlyProduction = $this->getTodayHourlyProduction();
        $recentProductions = $this->getRecentProductions(10);

        $currentHour = $now->hour;
        $hourlyAverage = $currentHour > 0 ? round($todayTotal / $currentHour, 2) : $todayTotal;

        $yesterdayTotal = Production::whereDate('production_date', Carbon::yesterday())
            ->sum('quantity');

        $comparison = $yesterdayTotal > 0 
            ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 2)
            : 0;

        return [
            'timestamp' => $now->toIso8601String(),
            'today_summary' => [
                'total_quantity' => $todayTotal,
                'production_count' => $todayCount,
                'active_workers' => $activeWorkers,
                'hourly_average' => $hourlyAverage,
            ],
            'comparison' => [
                'yesterday_total' => $yesterdayTotal,
                'change_percentage' => $comparison,
                'trend' => $comparison >= 0 ? 'up' : 'down',
            ],
            'hourly_production' => $hourlyProduction,
            'recent_productions' => $recentProductions,
        ];
    }

    /**
     * Kapsamlı özet rapor
     */
    public function getExecutiveSummary(string $startDate, string $endDate): array
    {
        $productions = Production::whereBetween('production_date', [$startDate, $endDate])
            ->with(['product', 'user'])
            ->get();

        $totalQuantity = $productions->sum('quantity');
        $totalCount = $productions->count();
        $uniqueWorkers = $productions->pluck('user_id')->unique()->count();
        $uniqueProducts = $productions->pluck('product_id')->unique()->count();

        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $dailyAverage = $daysDiff > 0 ? round($totalQuantity / $daysDiff, 2) : 0;

        $topProducts = $this->getTopProducts($startDate, $endDate, [], 5);
        $topWorkers = $this->getTopWorkers($startDate, $endDate, 5);

        $previousPeriod = $this->getPreviousPeriodComparison($startDate, $endDate, $totalQuantity);

        $kpis = [
            [
                'name' => 'Toplam Üretim',
                'value' => $totalQuantity,
                'unit' => 'adet',
                'change' => $previousPeriod['quantity_change'],
            ],
            [
                'name' => 'Günlük Ortalama',
                'value' => $dailyAverage,
                'unit' => 'adet/gün',
                'change' => null,
            ],
            [
                'name' => 'Aktif İşçi',
                'value' => $uniqueWorkers,
                'unit' => 'kişi',
                'change' => null,
            ],
            [
                'name' => 'Üretilen Ürün Çeşidi',
                'value' => $uniqueProducts,
                'unit' => 'çeşit',
                'change' => null,
            ],
        ];

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $daysDiff,
            ],
            'kpis' => $kpis,
            'summary' => [
                'total_quantity' => $totalQuantity,
                'total_count' => $totalCount,
                'unique_workers' => $uniqueWorkers,
                'unique_products' => $uniqueProducts,
                'daily_average' => $dailyAverage,
            ],
            'top_products' => $topProducts,
            'top_workers' => $topWorkers,
            'previous_period' => $previousPeriod,
        ];
    }

    /**
     * İşçi detay raporu - Belirli bir işçinin detaylı üretim analizi
     */
    public function getWorkerDetailReport(int $userId, string $startDate, string $endDate): array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return ['error' => 'İşçi bulunamadı'];
        }

        // Genel istatistikler
        $productions = Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with(['product', 'machine', 'shift.template'])
            ->get();

        $totalQuantity = $productions->sum('quantity');
        $totalCount = $productions->count();
        $workDays = $productions->pluck('production_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->unique()->count();
        
        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $dailyAverage = $workDays > 0 ? round($totalQuantity / $workDays, 2) : 0;

        // Ürün bazlı dağılım
        $productBreakdown = Production::where('user_id', $userId)
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
            });

        // Günlük üretim trendi
        $dailyTrend = Production::where('user_id', $userId)
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
                'date' => $item->date,
                'total' => (int) $item->total,
                'count' => $item->count,
            ]);

        // Aylık performans
        $monthlyPerformance = Production::where('user_id', $userId)
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
                    'month_name' => $this->getMonthName($item->month),
                    'total_quantity' => (int) $item->total,
                    'production_count' => $item->count,
                    'work_days' => $item->work_days,
                    'daily_average' => $item->work_days > 0 ? round($item->total / $item->work_days, 2) : 0,
                ];
            });

        // Makine bazlı üretim
        $machineBreakdown = Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select('machine_id', DB::raw('SUM(quantity) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('machine_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $machine = \App\Domains\Machines\Models\Machine::find($item->machine_id);
                return [
                    'machine_id' => $item->machine_id,
                    'machine_name' => $machine->machine_name ?? 'Bilinmeyen',
                    'total_quantity' => (int) $item->total,
                    'production_count' => $item->count,
                ];
            });

        // Vardiya bazlı performans
        $shiftPerformance = Production::where('user_id', $userId)
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with('shift.template')
            ->get()
            ->groupBy(fn($p) => $p->shift->template->name ?? 'Bilinmeyen')
            ->map(function ($items, $shiftName) {
                return [
                    'shift_name' => $shiftName,
                    'total_quantity' => $items->sum('quantity'),
                    'production_count' => $items->count(),
                ];
            })
            ->values();

        return [
            'worker' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $daysDiff,
            ],
            'summary' => [
                'total_quantity' => $totalQuantity,
                'production_count' => $totalCount,
                'work_days' => $workDays,
                'daily_average' => $dailyAverage,
                'unique_products' => $productBreakdown->count(),
                'unique_machines' => $machineBreakdown->count(),
            ],
            'product_breakdown' => $productBreakdown,
            'daily_trend' => $dailyTrend,
            'monthly_performance' => $monthlyPerformance,
            'machine_breakdown' => $machineBreakdown,
            'shift_performance' => $shiftPerformance,
        ];
    }


    // ==================== Private Helper Methods ====================

    private function buildBaseQuery(string $startDate, string $endDate, array $filters = [])
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate])
            ->with(['product', 'user', 'machine']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['machine_id'])) {
            $query->where('machine_id', $filters['machine_id']);
        }

        return $query;
    }

    private function calculateSummary($productions, string $startDate, string $endDate): array
    {
        $totalQuantity = $productions->sum('quantity');
        $totalCount = $productions->count();
        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        return [
            'total_quantity' => $totalQuantity,
            'total_count' => $totalCount,
            'daily_average' => $daysDiff > 0 ? round($totalQuantity / $daysDiff, 2) : 0,
            'unique_products' => $productions->pluck('product_id')->unique()->count(),
            'unique_workers' => $productions->pluck('user_id')->unique()->count(),
        ];
    }

    private function getDailyDistribution(string $startDate, string $endDate, array $filters = []): array
    {
        $query = $this->buildBaseQuery($startDate, $endDate, $filters);

        return $query
            ->select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy(DB::raw('DATE(production_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function getTopProducts(string $startDate, string $endDate, array $filters = [], int $limit = 5): array
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate]);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $product->product_name ?? 'Bilinmeyen',
                    'total_quantity' => (int) $item->total_quantity,
                ];
            })
            ->toArray();
    }

    private function getTopWorkers(string $startDate, string $endDate, int $limit = 5): array
    {
        return Production::query()
            ->whereBetween('production_date', [$startDate, $endDate])
            ->select('user_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('user_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $user->name ?? 'Bilinmeyen',
                    'total_quantity' => (int) $item->total_quantity,
                ];
            })
            ->toArray();
    }

    private function getHourlyAnalysis(string $startDate, string $endDate, array $filters = []): array
    {
        $query = $this->buildBaseQuery($startDate, $endDate, $filters);

        return $query
            ->select(
                DB::raw('HOUR(production_date) as hour'),
                DB::raw('SUM(quantity) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('HOUR(production_date)'))
            ->orderBy('hour')
            ->get()
            ->map(fn($item) => [
                'hour' => $item->hour,
                'total' => (int) $item->total,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getWorkerTrend(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query
            ->select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(quantity) as total'),
                DB::raw('COUNT(DISTINCT user_id) as worker_count')
            )
            ->groupBy(DB::raw('DATE(production_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => (int) $item->total,
                'worker_count' => $item->worker_count,
            ])
            ->toArray();
    }

    private function getProductTrend(string $startDate, string $endDate, ?int $productId = null): array
    {
        $query = Production::query()
            ->whereBetween('production_date', [$startDate, $endDate]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query
            ->select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy(DB::raw('DATE(production_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function getProductDistribution($productStats): array
    {
        $total = $productStats->sum('total_quantity');

        return $productStats->map(function ($stat) use ($total) {
            return [
                'product_name' => $stat['product_name'],
                'quantity' => $stat['total_quantity'],
                'percentage' => $total > 0 ? round(($stat['total_quantity'] / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    private function calculateYearlyGrowth(int $year): array
    {
        $currentYearTotal = Production::whereYear('production_date', $year)->sum('quantity');
        $previousYearTotal = Production::whereYear('production_date', $year - 1)->sum('quantity');

        $growthRate = $previousYearTotal > 0
            ? round((($currentYearTotal - $previousYearTotal) / $previousYearTotal) * 100, 2)
            : 0;

        return [
            'current_year' => $year,
            'current_year_total' => (int) $currentYearTotal,
            'previous_year_total' => (int) $previousYearTotal,
            'growth_rate' => $growthRate,
        ];
    }

    private function analyzeTrend($monthlyData): array
    {
        if ($monthlyData->count() < 2) {
            return [
                'trend' => 'Yetersiz Veri',
                'analysis' => 'Trend analizi için en az 2 aylık veri gereklidir.',
            ];
        }

        $quantities = $monthlyData->pluck('total_quantity')->toArray();
        $firstHalf = array_slice($quantities, 0, (int) ceil(count($quantities) / 2));
        $secondHalf = array_slice($quantities, (int) ceil(count($quantities) / 2));

        $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

        if ($secondAvg > $firstAvg * 1.1) {
            $trend = 'Yükseliş';
            $analysis = 'Üretim miktarı dönem içinde artış göstermektedir.';
        } elseif ($secondAvg < $firstAvg * 0.9) {
            $trend = 'Düşüş';
            $analysis = 'Üretim miktarı dönem içinde azalma göstermektedir.';
        } else {
            $trend = 'Stabil';
            $analysis = 'Üretim miktarı dönem içinde dengeli seyretmektedir.';
        }

        return [
            'trend' => $trend,
            'analysis' => $analysis,
        ];
    }

    private function getYearOverYearComparison(int $year, int $month): array
    {
        $currentMonthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $currentMonthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $previousMonthStart = Carbon::createFromDate($year - 1, $month, 1)->startOfMonth();
        $previousMonthEnd = Carbon::createFromDate($year - 1, $month, 1)->endOfMonth();

        $currentTotal = Production::whereBetween('production_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('quantity');

        $previousTotal = Production::whereBetween('production_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('quantity');

        $change = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 2)
            : 0;

        return [
            'current_year' => [
                'year' => $year,
                'month' => $month,
                'total' => (int) $currentTotal,
            ],
            'previous_year' => [
                'year' => $year - 1,
                'month' => $month,
                'total' => (int) $previousTotal,
            ],
            'difference' => (int) ($currentTotal - $previousTotal),
            'percentage_change' => $change,
        ];
    }

    private function getTodayHourlyProduction(): array
    {
        $today = Carbon::today()->format('Y-m-d');

        return Production::whereDate('production_date', $today)
            ->select(
                DB::raw('HOUR(production_date) as hour'),
                DB::raw('SUM(quantity) as total')
            )
            ->groupBy(DB::raw('HOUR(production_date)'))
            ->orderBy('hour')
            ->get()
            ->map(fn($item) => [
                'hour' => $item->hour,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function getRecentProductions(int $limit = 10): array
    {
        return Production::with(['user:id,name', 'product:id,product_name', 'machine:id,machine_name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'quantity' => $p->quantity,
                'product_name' => $p->product->product_name ?? 'Bilinmeyen',
                'user_name' => $p->user->name ?? 'Bilinmeyen',
                'machine_name' => $p->machine->machine_name ?? 'Bilinmeyen',
                'production_date' => $p->production_date,
                'created_at' => $p->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    private function getPreviousPeriodComparison(string $startDate, string $endDate, int $currentTotal): array
    {
        $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        $previousStart = Carbon::parse($startDate)->subDays($daysDiff)->format('Y-m-d');
        $previousEnd = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $previousTotal = Production::whereBetween('production_date', [$previousStart, $previousEnd])
            ->sum('quantity');

        $change = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 2)
            : 0;

        return [
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'previous_total' => (int) $previousTotal,
            'quantity_change' => $change,
        ];
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        return $months[$month] ?? '';
    }
}
