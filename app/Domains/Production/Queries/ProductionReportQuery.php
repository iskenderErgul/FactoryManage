<?php

namespace App\Domains\Production\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * ProductionReportQuery - Rapor sorguları için özelleşmiş sınıf
 * 
 * Bu sınıf, raporlama için gereken karmaşık sorguları içerir.
 * Aggregation, grouping ve statistical query'ler sağlar.
 */
class ProductionReportQuery extends ProductionQuery
{
    /**
     * İşçi istatistiklerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Builder
     */
    public function workerStats(string $startDate, string $endDate, ?int $userId = null): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);
        
        if ($userId) {
            $query = $this->byUser($query, $userId);
        }

        return $query->select(
            'user_id',
            DB::raw('COUNT(*) as production_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('AVG(quantity) as average_quantity'),
            DB::raw('COUNT(DISTINCT DATE(production_date)) as work_days')
        )
        ->groupBy('user_id')
        ->orderByDesc('total_quantity');
    }

    /**
     * Ürün istatistiklerini getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $productId
     * @return Builder
     */
    public function productStats(string $startDate, string $endDate, ?int $productId = null): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);
        
        if ($productId) {
            $query = $this->byProduct($query, $productId);
        }

        return $query->select(
            'product_id',
            DB::raw('COUNT(*) as production_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('AVG(quantity) as average_quantity'),
            DB::raw('MIN(quantity) as min_quantity'),
            DB::raw('MAX(quantity) as max_quantity')
        )
        ->groupBy('product_id')
        ->orderByDesc('total_quantity');
    }

    /**
     * Günlük dağılımı getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $filters
     * @return Builder
     */
    public function dailyDistribution(string $startDate, string $endDate, array $filters = []): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);
        
        // Apply filters
        if (!empty($filters['product_id'])) {
            $query = $this->byProduct($query, $filters['product_id']);
        }
        if (!empty($filters['user_id'])) {
            $query = $this->byUser($query, $filters['user_id']);
        }
        if (!empty($filters['machine_id'])) {
            $query = $this->byMachine($query, $filters['machine_id']);
        }

        return $query->select(
            DB::raw('DATE(production_date) as date'),
            DB::raw('SUM(quantity) as total')
        )
        ->groupBy(DB::raw('DATE(production_date)'))
        ->orderBy('date');
    }

    /**
     * Saatlik analiz getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $filters
     * @return Builder
     */
    public function hourlyAnalysis(string $startDate, string $endDate, array $filters = []): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);
        
        // Apply filters
        if (!empty($filters['product_id'])) {
            $query = $this->byProduct($query, $filters['product_id']);
        }
        if (!empty($filters['user_id'])) {
            $query = $this->byUser($query, $filters['user_id']);
        }

        return $query->select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('SUM(quantity) as total'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy(DB::raw('HOUR(created_at)'))
        ->orderBy('hour');
    }

    /**
     * Aylık trend verisi getir
     * 
     * @param int $year
     * @return Builder
     */
    public function monthlyTrend(int $year): Builder
    {
        return $this->baseQuery()
            ->select(
                DB::raw('MONTH(production_date) as month'),
                DB::raw('SUM(quantity) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(quantity) as average')
            )
            ->whereYear('production_date', $year)
            ->groupBy(DB::raw('MONTH(production_date)'))
            ->orderBy('month');
    }

    /**
     * Top N ürünleri getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @param array $filters
     * @return Builder
     */
    public function topProducts(string $startDate, string $endDate, int $limit = 5, array $filters = []): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);
        
        if (!empty($filters['user_id'])) {
            $query = $this->byUser($query, $filters['user_id']);
        }

        return $query->select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity')
        )
        ->groupBy('product_id')
        ->orderByDesc('total_quantity')
        ->limit($limit);
    }

    /**
     * Top N işçileri getir
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return Builder
     */
    public function topWorkers(string $startDate, string $endDate, int $limit = 5): Builder
    {
        $query = $this->baseQuery();
        $query = $this->byDateRange($query, $startDate, $endDate);

        return $query->select(
            'user_id',
            DB::raw('SUM(quantity) as total_quantity')
        )
        ->groupBy('user_id')
        ->orderByDesc('total_quantity')
        ->limit($limit);
    }

    /**
     * Son N üretimi getir
     * 
     * @param int $limit
     * @return Builder
     */
    public function recent(int $limit = 10): Builder
    {
        return $this->baseQuery()
            ->orderByDesc('created_at')
            ->limit($limit);
    }
}
