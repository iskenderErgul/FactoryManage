<?php

namespace App\Http\Controllers;

use App\Common\Helpers\ChartHelper;
use App\Common\Helpers\DateHelper;
use App\Common\Traits\HasDateFilters;
use App\Common\Traits\HasDataFormatting;
use App\Common\Traits\HasStatistics;
use App\Domains\Production\Models\Production;
use App\Domains\Machines\Models\Machine;
use App\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use HasDateFilters, HasDataFormatting, HasStatistics;

    /**
     * Günlük üretim verilerini getir
     */
    public function getDailyProduction(Request $request)
    {
        try {
            $dateRange = $this->getDateRangeFromRequest($request);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

            // Günlük toplam üretim verileri
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

            // Ürün detayları için ayrı sorgu
            $productDetails = Production::with('product:id,product_name')
                ->whereBetween('production_date', [$startDate, $endDate])
                ->select('production_date', 'product_id', 'quantity')
                ->get()
                ->groupBy('production_date')
                ->map(function($dailyProductions) {
                    return $dailyProductions->groupBy('product_id')->map(function($productGroup) {
                        $product = $productGroup->first()->product;
                        return [
                            'product_name' => $product ? $product->product_name : 'Bilinmeyen Ürün',
                            'quantity' => $productGroup->sum('quantity')
                        ];
                    })->values()->toArray();
                });

            // Chart data için formatla
            $labels = $productions->pluck('production_date')
                ->map(fn($date) => DateHelper::formatTurkish($date, 'chart'))
                ->reverse()
                ->values()
                ->toArray();
            
            $data = $productions->pluck('total_quantity')->reverse()->values()->toArray();
            
            $chartData = $this->formatBarChart($labels, $data, 'Günlük Üretim');

            // Table data için formatla
            $tableData = $productions->map(function($production) use ($productDetails) {
                return [
                    'production_date' => DateHelper::formatTurkish($production->production_date, 'short'),
                    'production_date_raw' => $production->production_date,
                    'total_quantity' => $production->total_quantity,
                    'active_machines' => $production->active_machines,
                    'active_workers' => $production->active_workers,
                    'product_details' => $productDetails[$production->production_date] ?? []
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange($startDate, $endDate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => $this->emptyChart('bar', 'Günlük Üretim'),
                'tableData' => [],
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange(
                    DateHelper::getStartDateByPeriod('weekly'),
                    Carbon::now()
                )
            ], 200);
        }
    }


    /**
     * Ürün dağılım verilerini getir
     */
    public function getProductDistribution(Request $request)
    {
        try {
            $dateRange = $this->getDateRangeFromRequest($request);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

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
            $labels = $productDistribution->pluck('product.product_name')->toArray();
            $data = $productDistribution->pluck('total_produced')->toArray();
            $chartData = $this->formatPieChart($labels, $data);

            // Table data için formatla
            $tableData = $productDistribution->map(function($item) use ($totalProduction) {
                return [
                    'product_name' => $item->product->product_name ?? 'Bilinmeyen Ürün',
                    'product_type' => $item->product->product_type ?? 'Belirsiz',
                    'total_produced' => $item->total_produced,
                    'percentage' => $this->calculatePercentage($item->total_produced, $totalProduction, 1)
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'totalProduction' => $totalProduction,
                'period' => $request->get('period', 'monthly')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => $this->emptyChart('pie'),
                'tableData' => [],
                'totalProduction' => 0,
                'period' => $request->get('period', 'monthly')
            ], 200);
        }
    }

    /**
     * YENİ - İşçi üretim matrisi (tarih x işçi tablosu)
     */
    public function getWorkerProductionMatrix(Request $request)
    {
        try {
            $dateRange = $this->getDateRangeFromRequest($request);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

            // Tarih aralığındaki tüm tarihleri al
            $dates = DateHelper::getDatesBetween($startDate, $endDate);
            $dateStrings = array_map(fn($date) => $date->format('Y-m-d'), $dates);

            // Bu tarih aralığında üretim yapan tüm işçileri al
            $workerIds = Production::whereBetween('production_date', [$startDate, $endDate])
                ->distinct('user_id')
                ->pluck('user_id')
                ->toArray();

            $workers = User::whereIn('id', $workerIds)
                ->orderBy('name')
                ->get();

            // İşçi ve tarih bazlı üretim verilerini al (ürün detayları ile)
            $productions = Production::with('product:id,product_name')
                ->whereBetween('production_date', [$startDate, $endDate])
                ->whereIn('user_id', $workerIds)
                ->select('user_id', 'production_date', 'product_id', 'quantity')
                ->get();

            // Toplam üretim verilerini hesapla
            $productionTotals = Production::whereBetween('production_date', [$startDate, $endDate])
                ->whereIn('user_id', $workerIds)
                ->select(
                    'user_id',
                    'production_date',
                    DB::raw('SUM(quantity) as total_quantity')
                )
                ->groupBy('user_id', 'production_date')
                ->get();

            // Veriyi matrix formatına dönüştür
            $matrixData = [];
            $dailyTotals = [];
            $grandTotal = 0;

            foreach ($workers as $worker) {
                $workerData = [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'productions' => [],
                    'productDetails' => [],
                    'total' => 0
                ];

                foreach ($dateStrings as $date) {
                    // Toplam üretim miktarını al
                    $totalProduction = $productionTotals->where('user_id', $worker->id)
                        ->where('production_date', $date)
                        ->first();

                    $quantity = $totalProduction ? $totalProduction->total_quantity : 0;
                    $workerData['productions'][$date] = $quantity;
                    $workerData['total'] += $quantity;

                    // Bu tarihteki ürün detaylarını al
                    $dailyProducts = $productions->where('user_id', $worker->id)
                        ->where('production_date', $date)
                        ->groupBy('product_id')
                        ->map(function($productGroup) {
                            $totalQuantity = $productGroup->sum('quantity');
                            $product = $productGroup->first()->product;
                            return [
                                'product_name' => $product ? $product->product_name : 'Bilinmeyen Ürün',
                                'quantity' => $totalQuantity
                            ];
                        })->values()->toArray();

                    $workerData['productDetails'][$date] = $dailyProducts;

                    // Günlük toplam hesapla
                    if (!isset($dailyTotals[$date])) {
                        $dailyTotals[$date] = 0;
                    }
                    $dailyTotals[$date] += $quantity;
                }

                $grandTotal += $workerData['total'];
                $matrixData[] = $workerData;
            }

            // Tarihleri frontend için formatla
            $formattedDates = collect($dates)->map(function($date) {
                return [
                    'formatted' => $date->format('Y-m-d'),
                    'display' => DateHelper::formatTurkish($date, 'chart'),
                    'full' => DateHelper::formatTurkish($date, 'short'),
                ];
            })->values();

            return response()->json([
                'dates' => $formattedDates,
                'workers' => $matrixData,
                'dailyTotals' => $dailyTotals,
                'grandTotal' => $grandTotal,
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange($startDate, $endDate)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'dates' => [],
                'workers' => [],
                'dailyTotals' => [],
                'grandTotal' => 0,
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange(
                    DateHelper::getStartDateByPeriod('weekly'),
                    Carbon::now()
                )
            ], 200);
        }
    }

    /**
     * Filtreleme için makine listesi
     */
    public function getMachines()
    {
        try {
            $machines = Machine::select('id', 'machine_name')
                ->where('status', 'active')
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
            $dateRange = $this->getDateRangeFromRequest($request);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

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
            $labels = $workerProduction->map(fn($worker) => $users[$worker->user_id] ?? 'Bilinmeyen İşçi')->toArray();
            $data = $workerProduction->pluck('total_produced')->toArray();
            $chartData = $this->formatBarChart($labels, $data, 'İşçi Üretimi');

            // Table data için formatla
            $tableData = $workerProduction->map(function($worker) use ($users) {
                return [
                    'worker_id' => $worker->user_id,
                    'worker_name' => $users[$worker->user_id] ?? 'Bilinmeyen İşçi',
                    'total_produced' => $worker->total_produced,
                    'products_worked' => $worker->products_worked,
                    'machines_worked' => $worker->machines_worked,
                    'total_shifts' => $worker->total_shifts,
                    'avg_per_shift' => $this->calculateAverage(
                        array_fill(0, $worker->total_shifts, $worker->total_produced / max($worker->total_shifts, 1)),
                        1
                    )
                ];
            });

            return response()->json([
                'chartData' => $chartData,
                'tableData' => $tableData,
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange($startDate, $endDate)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'chartData' => $this->emptyChart('bar', 'İşçi Üretimi'),
                'tableData' => [],
                'period' => $request->get('period', 'weekly'),
                'dateRange' => $this->formatDateRange(
                    DateHelper::getStartDateByPeriod('weekly'),
                    Carbon::now()
                )
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
            $labels = $workerProduction->map(fn($worker) => $users[$worker->user_id] ?? 'Bilinmeyen İşçi')->toArray();
            $data = $workerProduction->pluck('total_produced')->toArray();
            $chartData = $this->formatBarChart($labels, $data, 'İşçi Üretimi');

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
            return $this->formatErrorResponse('bar', 'Veri bulunamadı');
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
