<?php

namespace App\Services;

use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesProduct;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Tarih Aralıklı Rapor - Özet Bilgiler
     */
    public function getDateRangeSummary($startDate, $endDate)
    {
        $sales = Sales::whereBetween('sale_date', [$startDate, $endDate])->get();
        
        $totalSales = 0;
        $totalProducts = 0;
        
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalSales += $product->quantity * $product->price;
                $totalProducts += $product->quantity;
            }
        }
        
        $averageSale = $sales->count() > 0 ? $totalSales / $sales->count() : 0;
        
        return [
            'total_sales' => $totalSales,
            'total_products' => $totalProducts,
            'total_transactions' => $sales->count(),
            'average_sale' => $averageSale,
        ];
    }

    /**
     * Günlük Satış Dağılımı
     */
    public function getDailySalesDistribution($startDate, $endDate)
    {
        $sales = Sales::whereBetween('sale_date', [$startDate, $endDate])
            ->with('salesProducts')
            ->get()
            ->groupBy(function($sale) {
                return Carbon::parse($sale->sale_date)->format('Y-m-d');
            });
        
        $distribution = [];
        
        foreach ($sales as $date => $daySales) {
            $dailyTotal = 0;
            foreach ($daySales as $sale) {
                foreach ($sale->salesProducts as $product) {
                    $dailyTotal += $product->quantity * $product->price;
                }
            }
            $distribution[] = [
                'date' => $date,
                'total' => $dailyTotal,
                'count' => $daySales->count()
            ];
        }
        
        return collect($distribution)->sortBy('date')->values()->all();
    }

    /**
     * En Çok Satılan Ürünler
     */
    public function getTopProducts($startDate, $endDate, $limit = 10)
    {
        $topProducts = SalesProduct::whereHas('sale', function($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
        
        return $topProducts->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Bilinmeyen Ürün',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
            ];
        });
    }

    /**
     * Ödeme Yöntemleri Dağılımı
     */
    public function getPaymentMethodDistribution($startDate, $endDate)
    {
        $distribution = Sales::whereBetween('sale_date', [$startDate, $endDate])
            ->with('salesProducts')
            ->get()
            ->groupBy('payment_type');
        
        $result = [];
        
        foreach ($distribution as $paymentType => $sales) {
            $total = 0;
            foreach ($sales as $sale) {
                foreach ($sale->salesProducts as $product) {
                    $total += $product->quantity * $product->price;
                }
            }
            
            $result[] = [
                'payment_type' => $paymentType,
                'total' => $total,
                'count' => $sales->count()
            ];
        }
        
        return $result;
    }

    /**
     * Aylık Özet
     */
    public function getMonthlySummary($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $sales = Sales::whereBetween('sale_date', [$startDate, $endDate])->with('salesProducts')->get();
        
        $totalRevenue = 0;
        $totalPaid = 0;
        
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalRevenue += $product->quantity * $product->price;
            }
            
            if ($sale->payment_type === 'pesin') {
                foreach ($sale->salesProducts as $product) {
                    $totalPaid += $product->quantity * $product->price;
                }
            } elseif ($sale->payment_type === 'kismi' && $sale->paid_amount) {
                $totalPaid += $sale->paid_amount;
            }
        }
        
        $openBalance = $totalRevenue - $totalPaid;
        
        return [
            'total_revenue' => $totalRevenue,
            'total_paid' => $totalPaid,
            'open_balance' => $openBalance,
            'total_transactions' => $sales->count(),
        ];
    }

    /**
     * Ay İçin En Çok Satış Yapılan Müşteri
     */
    public function getTopCustomerForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $topCustomer = Sales::whereBetween('sale_date', [$startDate, $endDate])
            ->with(['customer', 'salesProducts'])
            ->get()
            ->groupBy('customer_id')
            ->map(function($customerSales) {
                $total = 0;
                foreach ($customerSales as $sale) {
                    foreach ($sale->salesProducts as $product) {
                        $total += $product->quantity * $product->price;
                    }
                }
                return [
                    'customer' => $customerSales->first()->customer,
                    'total' => $total,
                    'count' => $customerSales->count()
                ];
            })
            ->sortByDesc('total')
            ->first();
        
        return $topCustomer;
    }

    /**
     * Ay İçin En Çok Satılan Ürün
     */
    public function getTopProductForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $topProduct = SalesProduct::whereHas('sale', function($query) use ($startDate, $endDate) {
                $query->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->first();
        
        if (!$topProduct) {
            return null;
        }
        
        return [
            'product_id' => $topProduct->product_id,
            'product_name' => $topProduct->product->product_name ?? 'Bilinmeyen Ürün',
            'total_quantity' => $topProduct->total_quantity,
            'total_revenue' => $topProduct->total_revenue,
        ];
    }

    /**
     * Aylık Trend (Gün Bazında)
     */
    public function getMonthlyTrend($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        return $this->getDailySalesDistribution($startDate, $endDate);
    }

    /**
     * Müşteri Satış Özeti
     */
    public function getCustomerSales($customerId, $startDate = null, $endDate = null)
    {
        $query = Sales::where('customer_id', $customerId)->with('salesProducts');
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $sales = $query->get();
        
        $totalSales = 0;
        $totalProducts = 0;
        
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $totalSales += $product->quantity * $product->price;
                $totalProducts += $product->quantity;
            }
        }
        
        return [
            'total_sales' => $totalSales,
            'total_products' => $totalProducts,
            'total_transactions' => $sales->count(),
            'average_sale' => $sales->count() > 0 ? $totalSales / $sales->count() : 0,
        ];
    }

    /**
     * Müşterinin Satın Aldığı Ürünler
     */
    public function getCustomerProducts($customerId, $startDate = null, $endDate = null)
    {
        $query = Sales::where('customer_id', $customerId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $salesIds = $query->pluck('id');
        
        $products = SalesProduct::whereIn('sales_id', $salesIds)
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->get();
        
        return $products->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Bilinmeyen Ürün',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
            ];
        });
    }

    /**
     * Müşteri Harcama Grafiği (Aylık)
     */
    public function getCustomerSpendingChart($customerId)
    {
        $sales = Sales::where('customer_id', $customerId)
            ->with('salesProducts')
            ->get()
            ->groupBy(function($sale) {
                return Carbon::parse($sale->sale_date)->format('Y-m');
            });
        
        $chart = [];
        
        foreach ($sales as $month => $monthlySales) {
            $total = 0;
            foreach ($monthlySales as $sale) {
                foreach ($sale->salesProducts as $product) {
                    $total += $product->quantity * $product->price;
                }
            }
            $chart[] = [
                'month' => $month,
                'total' => $total
            ];
        }
        
        return collect($chart)->sortBy('month')->values()->all();
    }

    /**
     * Müşteri Satın Alma Alışkanlığı Analizi
     */
    public function getCustomerPurchaseHabits($customerId)
    {
        $sales = Sales::where('customer_id', $customerId)->with('salesProducts')->get();
        
        if ($sales->isEmpty()) {
            return [
                'analysis' => 'Henüz satış verisi bulunmamaktadır.',
                'favorite_payment_method' => null,
                'average_days_between_purchases' => 0,
                'most_purchased_day_of_week' => null,
            ];
        }
        
        // En çok kullanılan ödeme yöntemi
        $paymentMethods = $sales->groupBy('payment_type');
        $favoritePaymentMethod = $paymentMethods->sortByDesc(function($group) {
            return $group->count();
        })->keys()->first();
        
        // Satın almalar arası ortalama gün
        $dates = $sales->pluck('sale_date')->sort()->values();
        $daysBetween = [];
        for ($i = 1; $i < $dates->count(); $i++) {
            $daysBetween[] = Carbon::parse($dates[$i])->diffInDays(Carbon::parse($dates[$i-1]));
        }
        $averageDaysBetween = count($daysBetween) > 0 ? array_sum($daysBetween) / count($daysBetween) : 0;
        
        // En çok satın alınan gün
        $dayOfWeek = $sales->groupBy(function($sale) {
            return Carbon::parse($sale->sale_date)->dayOfWeek;
        })->sortByDesc(function($group) {
            return $group->count();
        })->keys()->first();
        
        $dayNames = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $mostPurchasedDay = $dayOfWeek !== null ? $dayNames[$dayOfWeek] : null;
        
        $analysis = sprintf(
            'Müşteri genellikle %s ödeme yöntemini tercih ediyor. Ortalama %d günde bir alışveriş yapıyor. En çok %s günü alışveriş yapıyor.',
            $favoritePaymentMethod ?? 'bilinmeyen',
            round($averageDaysBetween),
            $mostPurchasedDay ?? 'bilinmeyen'
        );
        
        return [
            'analysis' => $analysis,
            'favorite_payment_method' => $favoritePaymentMethod,
            'average_days_between_purchases' => round($averageDaysBetween),
            'most_purchased_day_of_week' => $mostPurchasedDay,
        ];
    }

    /**
     * Müşteri Ürün Bazlı Satış
     */
    public function getCustomerProductSales($customerId, $filter = [])
    {
        $query = Sales::where('customer_id', $customerId);
        
        if (isset($filter['start_date']) && isset($filter['end_date'])) {
            $query->whereBetween('sale_date', [$filter['start_date'], $filter['end_date']]);
        } elseif (isset($filter['month']) && isset($filter['year'])) {
            $startDate = Carbon::create($filter['year'], $filter['month'], 1)->startOfMonth();
            $endDate = Carbon::create($filter['year'], $filter['month'], 1)->endOfMonth();
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $salesIds = $query->pluck('id');
        
        $products = SalesProduct::whereIn('sales_id', $salesIds)
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->get();
        
        return $products->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Bilinmeyen Ürün',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
            ];
        });
    }

    /**
     * Müşterinin En Çok Satın Aldığı Ürünler
     */
    public function getTopCustomerProducts($customerId, $limit = 10)
    {
        $salesIds = Sales::where('customer_id', $customerId)->pluck('id');
        
        $products = SalesProduct::whereIn('sales_id', $salesIds)
            ->with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
        
        return $products->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Bilinmeyen Ürün',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
            ];
        });
    }

    /**
     * Müşteri Ödeme Özeti
     */
    public function getCustomerPaymentSummary($customerId, $startDate = null, $endDate = null)
    {
        $query = Sales::where('customer_id', $customerId)->with('salesProducts');
        
        if ($startDate && $endDate) {
            $query->whereBetween('sale_date', [$startDate, $endDate]);
        }
        
        $sales = $query->get();
        
        $totalRevenue = 0;
        $cashPayment = 0;
        $debtPayment = 0;
        
        foreach ($sales as $sale) {
            $saleTotal = 0;
            foreach ($sale->salesProducts as $product) {
                $saleTotal += $product->quantity * $product->price;
            }
            $totalRevenue += $saleTotal;
            
            if ($sale->payment_type === 'pesin') {
                $cashPayment += $saleTotal;
            } elseif ($sale->payment_type === 'kismi' && $sale->paid_amount) {
                $cashPayment += $sale->paid_amount;
                $debtPayment += ($saleTotal - $sale->paid_amount);
            } elseif ($sale->payment_type === 'borc') {
                $debtPayment += $saleTotal;
            }
        }
        
        // Transaction tablosundan ödeme bilgilerini al
        $transactionQuery = Transaction::where('customer_id', $customerId);
        if ($startDate && $endDate) {
            $transactionQuery->whereBetween('date', [$startDate, $endDate]);
        }
        $transactions = $transactionQuery->get();
        
        $paidFromDebt = 0;
        foreach ($transactions as $transaction) {
            if (strtolower($transaction->type) === 'ödeme') {
                $paidFromDebt += $transaction->amount;
            }
        }
        
        $openBalance = $debtPayment - $paidFromDebt;
        
        return [
            'total_revenue' => $totalRevenue,
            'cash_payment' => $cashPayment,
            'debt_payment' => $debtPayment,
            'paid_from_debt' => $paidFromDebt,
            'open_balance' => $openBalance,
        ];
    }

    /**
     * Müşteri Ödeme Grafiği
     */
    public function getCustomerPaymentChart($customerId)
    {
        $transactions = Transaction::where('customer_id', $customerId)
            ->orderBy('date')
            ->get()
            ->groupBy(function($transaction) {
                return Carbon::parse($transaction->date)->format('Y-m');
            });
        
        $chart = [];
        
        foreach ($transactions as $month => $monthlyTransactions) {
            $payments = 0;
            $debts = 0;
            
            foreach ($monthlyTransactions as $transaction) {
                if (strtolower($transaction->type) === 'ödeme') {
                    $payments += $transaction->amount;
                } elseif (strtolower($transaction->type) === 'borç') {
                    $debts += $transaction->amount;
                }
            }
            
            $chart[] = [
                'month' => $month,
                'payments' => $payments,
                'debts' => $debts,
            ];
        }
        
        return collect($chart)->sortBy('month')->values()->all();
    }

    /**
     * Müşteri Ödeme Alışkanlığı
     */
    public function getCustomerPaymentHabits($customerId)
    {
        $sales = Sales::where('customer_id', $customerId)->get();
        
        if ($sales->isEmpty()) {
            return [
                'analysis' => 'Henüz ödeme verisi bulunmamaktadır.',
                'payment_reliability' => 0,
            ];
        }
        
        $paymentTypes = $sales->groupBy('payment_type');
        $cashCount = $paymentTypes->get('pesin', collect())->count();
        $totalCount = $sales->count();
        
        $reliability = $totalCount > 0 ? ($cashCount / $totalCount) * 100 : 0;
        
        $analysis = sprintf(
            'Müşteri toplam %d alışverişin %d tanesini peşin ödemiştir. Ödeme güvenilirliği: %%%d',
            $totalCount,
            $cashCount,
            round($reliability)
        );
        
        return [
            'analysis' => $analysis,
            'payment_reliability' => round($reliability),
            'cash_count' => $cashCount,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Son 12 Ay Satış Verisi
     */
    public function getLast12MonthsSales()
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        
        $sales = Sales::whereBetween('sale_date', [$startDate, $endDate])
            ->with('salesProducts')
            ->get()
            ->groupBy(function($sale) {
                return Carbon::parse($sale->sale_date)->format('Y-m');
            });
        
        $chart = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthSales = $sales->get($month, collect());
            
            $total = 0;
            foreach ($monthSales as $sale) {
                foreach ($sale->salesProducts as $product) {
                    $total += $product->quantity * $product->price;
                }
            }
            
            $chart[] = [
                'month' => $month,
                'total' => $total,
                'count' => $monthSales->count()
            ];
        }
        
        return $chart;
    }

    /**
     * Yıllık Büyüme Oranı
     */
    public function getYearlyGrowthRate($year)
    {
        $currentYearStart = Carbon::create($year, 1, 1)->startOfYear();
        $currentYearEnd = Carbon::create($year, 12, 31)->endOfYear();
        
        $previousYearStart = Carbon::create($year - 1, 1, 1)->startOfYear();
        $previousYearEnd = Carbon::create($year - 1, 12, 31)->endOfYear();
        
        $currentYearSales = $this->calculateTotalSales($currentYearStart, $currentYearEnd);
        $previousYearSales = $this->calculateTotalSales($previousYearStart, $previousYearEnd);
        
        if ($previousYearSales == 0) {
            return [
                'growth_rate' => 0,
                'current_year_sales' => $currentYearSales,
                'previous_year_sales' => $previousYearSales,
            ];
        }
        
        $growthRate = (($currentYearSales - $previousYearSales) / $previousYearSales) * 100;
        
        return [
            'growth_rate' => round($growthRate, 2),
            'current_year_sales' => $currentYearSales,
            'previous_year_sales' => $previousYearSales,
        ];
    }

    /**
     * Yıl Karşılaştırması (Aynı Ay)
     */
    public function getYearOverYearComparison($year, $month)
    {
        $currentMonthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentMonthEnd = Carbon::create($year, $month, 1)->endOfMonth();
        
        $previousYearMonthStart = Carbon::create($year - 1, $month, 1)->startOfMonth();
        $previousYearMonthEnd = Carbon::create($year - 1, $month, 1)->endOfMonth();
        
        $currentMonthSales = $this->calculateTotalSales($currentMonthStart, $currentMonthEnd);
        $previousYearMonthSales = $this->calculateTotalSales($previousYearMonthStart, $previousYearMonthEnd);
        
        $difference = $currentMonthSales - $previousYearMonthSales;
        $percentageChange = $previousYearMonthSales > 0 
            ? (($difference / $previousYearMonthSales) * 100) 
            : 0;
        
        return [
            'current_month_sales' => $currentMonthSales,
            'previous_year_month_sales' => $previousYearMonthSales,
            'difference' => $difference,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    /**
     * Satış Trend Analizi
     */
    public function getSalesTrendAnalysis()
    {
        $last12Months = $this->getLast12MonthsSales();
        
        if (count($last12Months) < 2) {
            return [
                'trend' => 'Yetersiz veri',
                'analysis' => 'Trend analizi için yeterli veri bulunmamaktadır.',
            ];
        }
        
        $totals = array_column($last12Months, 'total');
        $firstHalf = array_slice($totals, 0, 6);
        $secondHalf = array_slice($totals, 6, 6);
        
        $firstHalfAvg = array_sum($firstHalf) / count($firstHalf);
        $secondHalfAvg = array_sum($secondHalf) / count($secondHalf);
        
        $trend = 'Stabil';
        if ($secondHalfAvg > $firstHalfAvg * 1.1) {
            $trend = 'Yükseliş';
        } elseif ($secondHalfAvg < $firstHalfAvg * 0.9) {
            $trend = 'Düşüş';
        }
        
        $analysis = sprintf(
            'Son 12 ayda satışlar %s eğiliminde. İlk 6 ay ortalaması: %s TL, Son 6 ay ortalaması: %s TL',
            strtolower($trend),
            number_format($firstHalfAvg, 2, ',', '.'),
            number_format($secondHalfAvg, 2, ',', '.')
        );
        
        return [
            'trend' => $trend,
            'analysis' => $analysis,
            'first_half_avg' => $firstHalfAvg,
            'second_half_avg' => $secondHalfAvg,
        ];
    }

    /**
     * Yardımcı Metot: Toplam Satış Hesaplama
     */
    private function calculateTotalSales($startDate, $endDate)
    {
        $sales = Sales::whereBetween('sale_date', [$startDate, $endDate])->with('salesProducts')->get();
        
        $total = 0;
        foreach ($sales as $sale) {
            foreach ($sale->salesProducts as $product) {
                $total += $product->quantity * $product->price;
            }
        }
        
        return $total;
    }
}
