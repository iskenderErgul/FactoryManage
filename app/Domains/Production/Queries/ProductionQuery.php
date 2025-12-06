<?php

namespace App\Domains\Production\Queries;

use App\Domains\Production\Models\Production;
use Illuminate\Database\Eloquent\Builder;

/**
 * ProductionQuery - Üretim sorguları için temel sınıf
 * 
 * Bu sınıf, Production model'i üzerinde yapılan sorguları merkezi hale getirir.
 * Reusable query metodları sağlar.
 */
class ProductionQuery
{
    /**
     * Temel query builder'ı döner
     * 
     * @return Builder
     */
    public function baseQuery(): Builder
    {
        return Production::query()->with(['product', 'user', 'machine']);
    }

    /**
     * Tarih aralığına göre filtrele
     * 
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function byDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('production_date', [$startDate, $endDate]);
    }

    /**
     * Kullanıcıya göre filtrele
     * 
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function byUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Ürüne göre filtrele
     * 
     * @param Builder $query
     * @param int $productId
     * @return Builder
     */
    public function byProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Makineye göre filtrele
     * 
     * @param Builder $query
     * @param int $machineId
     * @return Builder
     */
    public function byMachine(Builder $query, int $machineId): Builder
    {
        return $query->where('machine_id', $machineId);
    }

    /**
     * Vardiyaya göre filtrele
     * 
     * @param Builder $query
     * @param int $shiftId
     * @return Builder
     */
    public function byShift(Builder $query, int $shiftId): Builder
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Bugünün üretimlerini getir
     * 
     * @return Builder
     */
    public function today(): Builder
    {
        return $this->baseQuery()->whereDate('production_date', today());
    }

    /**
     * Bu haftanın üretimlerini getir
     * 
     * @return Builder
     */
    public function thisWeek(): Builder
    {
        return $this->baseQuery()->whereBetween('production_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Bu ayın üretimlerini getir
     * 
     * @return Builder
     */
    public function thisMonth(): Builder
    {
        return $this->baseQuery()->whereBetween('production_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }
}
