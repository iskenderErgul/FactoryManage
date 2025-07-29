<?php

namespace App\Http\Controllers;

use App\Domains\Production\Models\Production;
use App\Domains\Machines\Models\Machine;
use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Günlük üretim verilerini getir
     */
    public function getDailyProduction(Request $request)
    {
        try {
            $period = $request->get('period', 'weekly'); // daily, weekly, biweekly, triweekly, monthly

            $startDate = $this->getStartDateByPeriod($period);
            $endDate = Carbon::now();

            $productions = Production::with(['machine', 'user', 'product'])
                ->whereBetween('production_date', [$startDate, $endDate])
                ->select(
                    'production_date',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('COUNT(DISTINCT machine_id) as active_machines'),
                    DB::raw('COUNT(DISTINCT user_id) as active_workers')
                )
                ->groupBy('production_date')
                ->orderBy('production_date', 'desc')
                ->get();

            // Chart data için formatla
            $chartData = [
                'labels' => $productions->pluck('production_date')->map(function($date) {
                    return Carbon::parse($date)->format('d M');
                })->reverse()->values(),
                'datasets' => [
                    [
                        'label' => 'Günlük Üretim',
                        'data' => $productions->pluck('total_quantity')->reverse()->values(),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => '#3B82F6',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                        'borderSkipped' => false
                    ]
                ]
            ];

            // Table data için formatla
            $tableData = $productions->map(function($production) {
                return [
                    'production_date' => Carbon::parse($production->production_date)->format('d.m.Y'),
                    'total_quantity' => $production->total_quantity,
                    'active_machines' => $production->active_machines,
                    'active_workers' => $production->active_workers
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'period' => $period,
                'dateRange' => [
                    'start' => $startDate->format('d.m.Y'),
                    'end' => $endDate->format('d.m.Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'Günlük Üretim',
                            'data' => [],
                            'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                            'borderColor' => '#3B82F6',
                            'borderWidth' => 2,
                            'borderRadius' => 6,
                            'borderSkipped' => false
                        ]
                    ]
                ],
                'tableData' => [],
                'period' => $request->get('period', 'weekly'),
                'dateRange' => [
                    'start' => Carbon::now()->subWeek()->format('d.m.Y'),
                    'end' => Carbon::now()->format('d.m.Y')
                ]
            ], 200);
        }
    }

    /**
     * Ürün dağılım verilerini getir
     */
    public function getProductDistribution(Request $request)
    {
        try {
            $period = $request->get('period', 'monthly');

            $startDate = $this->getStartDateByPeriod($period);
            $endDate = Carbon::now();

            $productDistribution = Production::with('product')
                ->whereBetween('production_date', [$startDate, $endDate])
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_produced')
                )
                ->groupBy('product_id')
                ->orderBy('total_produced', 'desc')
                ->get();

            $totalProduction = $productDistribution->sum('total_produced');

            // Chart data için formatla
            $chartData = [
                'labels' => $productDistribution->pluck('product.product_name'),
                'datasets' => [
                    [
                        'data' => $productDistribution->pluck('total_produced'),
                        'backgroundColor' => [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                        ],
                        'borderColor' => [
                            '#3B82F6',
                            '#10B981',
                            '#F59E0B',
                            '#8B5CF6',
                            '#EC4899',
                            '#EF4444',
                            '#22C55E',
                            '#A855F7',
                        ],
                        'borderWidth' => 2
                    ]
                ]
            ];

            // Table data için formatla
            $tableData = $productDistribution->map(function($item) use ($totalProduction) {
                $percentage = $totalProduction > 0 ? round(($item->total_produced / $totalProduction) * 100, 1) : 0;

                return [
                    'product_name' => $item->product->product_name ?? 'Bilinmeyen Ürün',
                    'product_type' => $item->product->product_type ?? 'Belirsiz',
                    'total_produced' => $item->total_produced,
                    'percentage' => $percentage
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'totalProduction' => $totalProduction,
                'period' => $period
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'data' => [],
                            'backgroundColor' => [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(236, 72, 153, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(168, 85, 247, 0.8)',
                            ],
                            'borderColor' => [
                                '#3B82F6',
                                '#10B981',
                                '#F59E0B',
                                '#8B5CF6',
                                '#EC4899',
                                '#EF4444',
                                '#22C55E',
                                '#A855F7',
                            ],
                            'borderWidth' => 2
                        ]
                    ]
                ],
                'tableData' => [],
                'totalProduction' => 0,
                'period' => $request->get('period', 'monthly')
            ], 200);
        }
    }

    /**
     * Filtreleme için makine listesi
     */
    public function getMachines()
    {
        try {
            $machines = Machine::select('id', 'machine_name') // Eğer farklı alan adı varsa buraya yazın (örn: 'name as machine_name')
            ->where('status', 'active') // Eğer status alanı yoksa bu satırı kaldırın
            ->orderBy('machine_name')
                ->get();

            return response()->json($machines);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    /**
     * Filtreleme için işçi listesi
     */
    public function getWorkers()
    {
        try {
            $workers = User::select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json($workers);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    /**
     * Filtreleme için ürün listesi
     */
    public function getProducts()
    {
        try {
            $products = \App\Domains\Product\Models\Product::select('id', 'product_name', 'product_type')
                ->orderBy('product_name')
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    }

    /**
     * Filtrelenmiş üretim verileri
     */
    public function getFilteredProduction(Request $request)
    {
        $query = Production::with(['machine', 'user', 'product']);

        // Tarih aralığı filtresi
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('production_date', [
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            ]);
        }

        // Makine filtresi
        if ($request->has('machine_id') && $request->machine_id) {
            $query->where('machine_id', $request->machine_id);
        }

        // İşçi filtresi
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $productions = $query->select(
            'production_date',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('COUNT(DISTINCT machine_id) as active_machines'),
            DB::raw('COUNT(DISTINCT user_id) as active_workers')
        )
            ->groupBy('production_date')
            ->orderBy('production_date', 'desc')
            ->get();

        // Chart data formatla
        $chartData = [
            'labels' => $productions->pluck('production_date')->map(function($date) {
                return Carbon::parse($date)->format('d M');
            })->reverse()->values(),
            'datasets' => [
                [
                    'label' => 'Günlük Üretim',
                    'data' => $productions->pluck('total_quantity')->reverse()->values(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false
                ]
            ]
        ];

        // Table data formatla
        $tableData = $productions->map(function($production) {
            return [
                'production_date' => Carbon::parse($production->production_date)->format('d.m.Y'),
                'total_quantity' => $production->total_quantity,
                'active_machines' => $production->active_machines,
                'active_workers' => $production->active_workers
            ];
        });

        return response()->json([
            'chartData' => $chartData,
            'tableData' => $tableData
        ]);
    }

    /**
     * Periyoda göre başlangıç tarihi hesapla
     */
    private function getStartDateByPeriod($period)
    {
        $now = Carbon::now();

        return match($period) {
            'daily' => $now->copy()->subDay(),
            'weekly' => $now->copy()->subWeek(),
            'biweekly' => $now->copy()->subWeeks(2),
            'triweekly' => $now->copy()->subWeeks(3),
            'monthly' => $now->copy()->subMonth(),
            default => $now->copy()->subWeek()
        };
    }

    /**
     * Genel dashboard istatistikleri
     */
    public function getDashboardStats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'today_production' => Production::whereDate('production_date', $today)->sum('quantity'),
            'yesterday_production' => Production::whereDate('production_date', $yesterday)->sum('quantity'),
            'week_production' => Production::where('production_date', '>=', $thisWeek)->sum('quantity'),
            'month_production' => Production::where('production_date', '>=', $thisMonth)->sum('quantity'),
            'active_machines_today' => Production::whereDate('production_date', $today)
                ->distinct('machine_id')->count('machine_id'),
            'active_workers_today' => Production::whereDate('production_date', $today)
                ->distinct('user_id')->count('user_id'),
            'total_products' => Production::distinct('product_id')->count('product_id')
        ];

        return response()->json($stats);
    }

    /**
     * İşçi üretim verilerini getir
     */
    public function getWorkerProduction(Request $request)
    {
        try {
            $period = $request->get('period', 'weekly');

            $startDate = $this->getStartDateByPeriod($period);
            $endDate = Carbon::now();

            $workerProduction = Production::with(['user', 'product', 'machine'])
                ->whereBetween('production_date', [$startDate, $endDate])
                ->select(
                    'user_id',
                    DB::raw('SUM(quantity) as total_produced'),
                    DB::raw('COUNT(DISTINCT product_id) as products_worked'),
                    DB::raw('COUNT(DISTINCT machine_id) as machines_worked'),
                    DB::raw('COUNT(*) as total_shifts')
                )
                ->groupBy('user_id')
                ->orderBy('total_produced', 'desc')
                ->get();

            // User bilgilerini ayrıca al
            $userIds = $workerProduction->pluck('user_id')->toArray();
            $users = User::whereIn('id', $userIds)->pluck('name', 'id');

            // Chart data için formatla
            $chartData = [
                'labels' => $workerProduction->map(function($worker) use ($users) {
                    return $users[$worker->user_id] ?? 'Bilinmeyen İşçi';
                }),
                'datasets' => [
                    [
                        'label' => 'İşçi Üretimi',
                        'data' => $workerProduction->pluck('total_produced'),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => '#3B82F6',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                        'borderSkipped' => false
                    ]
                ]
            ];

            // Table data için formatla
            $tableData = $workerProduction->map(function($worker) use ($users) {
                return [
                    'worker_id' => $worker->user_id,
                    'worker_name' => $users[$worker->user_id] ?? 'Bilinmeyen İşçi',
                    'total_produced' => $worker->total_produced,
                    'products_worked' => $worker->products_worked,
                    'machines_worked' => $worker->machines_worked,
                    'total_shifts' => $worker->total_shifts,
                    'avg_per_shift' => $worker->total_shifts > 0 ? round($worker->total_produced / $worker->total_shifts, 1) : 0
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'period' => $period,
                'dateRange' => [
                    'start' => $startDate->format('d.m.Y'),
                    'end' => $endDate->format('d.m.Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'İşçi Üretimi',
                            'data' => [],
                            'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                            'borderColor' => '#3B82F6',
                            'borderWidth' => 2,
                            'borderRadius' => 6,
                            'borderSkipped' => false
                        ]
                    ]
                ],
                'tableData' => [],
                'period' => $request->get('period', 'weekly'),
                'dateRange' => [
                    'start' => Carbon::now()->subWeek()->format('d.m.Y'),
                    'end' => Carbon::now()->format('d.m.Y')
                ]
            ], 200);
        }
    }

    /**
     * Filtrelenmiş işçi üretim verileri
     */
    public function getFilteredWorkerProduction(Request $request)
    {
        try {
            $query = Production::with(['user', 'product', 'machine']);

            // Tarih aralığı filtresi
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('production_date', [
                    Carbon::parse($request->start_date),
                    Carbon::parse($request->end_date)
                ]);
            }

            // Makine filtresi
            if ($request->has('machine_id') && $request->machine_id) {
                $query->where('machine_id', $request->machine_id);
            }

            // Ürün filtresi
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            $workerProduction = $query->select(
                'user_id',
                DB::raw('SUM(quantity) as total_produced'),
                DB::raw('COUNT(DISTINCT product_id) as products_worked'),
                DB::raw('COUNT(DISTINCT machine_id) as machines_worked'),
                DB::raw('COUNT(*) as total_shifts')
            )
                ->groupBy('user_id')
                ->orderBy('total_produced', 'desc')
                ->get();

            // User bilgilerini ayrıca al
            $userIds = $workerProduction->pluck('user_id')->toArray();
            $users = User::whereIn('id', $userIds)->pluck('name', 'id');

            // Chart data formatla
            $chartData = [
                'labels' => $workerProduction->map(function($worker) use ($users) {
                    return $users[$worker->user_id] ?? 'Bilinmeyen İşçi';
                }),
                'datasets' => [
                    [
                        'label' => 'İşçi Üretimi',
                        'data' => $workerProduction->pluck('total_produced'),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => '#3B82F6',
                        'borderWidth' => 2,
                        'borderRadius' => 6,
                        'borderSkipped' => false
                    ]
                ]
            ];

            // Table data formatla
            $tableData = $workerProduction->map(function($worker) use ($users) {
                return [
                    'worker_id' => $worker->user_id,
                    'worker_name' => $users[$worker->user_id] ?? 'Bilinmeyen İşçi',
                    'total_produced' => $worker->total_produced,
                    'products_worked' => $worker->products_worked,
                    'machines_worked' => $worker->machines_worked,
                    'total_shifts' => $worker->total_shifts,
                    'avg_per_shift' => $worker->total_shifts > 0 ? round($worker->total_produced / $worker->total_shifts, 1) : 0
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'İşçi Üretimi',
                            'data' => [],
                            'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                            'borderColor' => '#3B82F6',
                            'borderWidth' => 2,
                            'borderRadius' => 6,
                            'borderSkipped' => false
                        ]
                    ]
                ],
                'tableData' => []
            ], 200);
        }
    }

    /**
     * Belirli bir işçinin detaylı üretim verilerini getir
     */
    public function getWorkerDetailProduction(Request $request)
    {
        $workerId = $request->get('worker_id');
        $period = $request->get('period', 'weekly');

        $startDate = $this->getStartDateByPeriod($period);
        $endDate = Carbon::now();

        $query = Production::with(['product', 'machine'])
            ->where('user_id', $workerId)
            ->whereBetween('production_date', [$startDate, $endDate]);

        // Ürün bazlı üretim verileri
        $productProduction = $query->select(
            'product_id',
            DB::raw('SUM(quantity) as total_produced'),
            DB::raw('COUNT(*) as total_shifts')
        )
            ->groupBy('product_id')
            ->orderBy('total_produced', 'desc')
            ->get();

        // Makine bazlı üretim verileri
        $machineProduction = $query->select(
            'machine_id',
            DB::raw('SUM(quantity) as total_produced'),
            DB::raw('COUNT(*) as total_shifts')
        )
            ->groupBy('machine_id')
            ->orderBy('total_produced', 'desc')
            ->get();

        // Günlük üretim verileri
        $dailyProduction = $query->select(
            'production_date',
            DB::raw('SUM(quantity) as total_produced'),
            DB::raw('COUNT(DISTINCT product_id) as products_worked'),
            DB::raw('COUNT(DISTINCT machine_id) as machines_worked')
        )
            ->groupBy('production_date')
            ->orderBy('production_date', 'desc')
            ->get();

        // Ürün bilgilerini al
        $productIds = $productProduction->pluck('product_id')->toArray();
        $products = \App\Domains\Product\Models\Product::whereIn('id', $productIds)->pluck('product_name', 'id');

        // Makine bilgilerini al
        $machineIds = $machineProduction->pluck('machine_id')->toArray();
        $machines = Machine::whereIn('id', $machineIds)->pluck('machine_name', 'id');

        // İşçi bilgisini al
        $worker = User::find($workerId);

        // Chart data formatla
        $productChartData = [
            'labels' => $productProduction->map(function($item) use ($products) {
                return $products[$item->product_id] ?? 'Bilinmeyen Ürün';
            }),
            'datasets' => [
                [
                    'label' => 'Ürün Bazlı Üretim',
                    'data' => $productProduction->pluck('total_produced'),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => '#10B981',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false
                ]
            ]
        ];

        $dailyChartData = [
            'labels' => $dailyProduction->pluck('production_date')->map(function($date) {
                return Carbon::parse($date)->format('d M');
            })->reverse()->values(),
            'datasets' => [
                [
                    'label' => 'Günlük Üretim',
                    'data' => $dailyProduction->pluck('total_produced')->reverse()->values(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor' => '#F59E0B',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false
                ]
            ]
        ];

        // Table data formatla
        $productTableData = $productProduction->map(function($item) use ($products) {
            return [
                'product_name' => $products[$item->product_id] ?? 'Bilinmeyen Ürün',
                'total_produced' => $item->total_produced,
                'total_shifts' => $item->total_shifts,
                'avg_per_shift' => $item->total_shifts > 0 ? round($item->total_produced / $item->total_shifts, 1) : 0
            ];
        });

        $dailyTableData = $dailyProduction->map(function($item) {
            return [
                'production_date' => Carbon::parse($item->production_date)->format('d.m.Y'),
                'total_produced' => $item->total_produced,
                'products_worked' => $item->products_worked,
                'machines_worked' => $item->machines_worked
            ];
        });

        return response()->json([
            'worker' => $worker ? $worker->name : 'Bilinmeyen İşçi',
            'productChartData' => $productChartData,
            'dailyChartData' => $dailyChartData,
            'productTableData' => $productTableData,
            'dailyTableData' => $dailyTableData,
            'period' => $period,
            'dateRange' => [
                'start' => $startDate->format('d.m.Y'),
                'end' => $endDate->format('d.m.Y')
            ]
        ]);
    }
}
