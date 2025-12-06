<?php

namespace App\Domains\Sales\Queries;

use App\Domains\Sales\Models\Sales;
use Illuminate\Database\Eloquent\Builder;

/**
 * SalesQuery - Satış sorguları için temel sınıf
 */
class SalesQuery
{
    public function baseQuery(): Builder
    {
        return Sales::query()->with(['customer', 'salesProducts.product']);
    }

    public function byDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('sale_date', [$startDate, $endDate]);
    }

    public function byCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function byPaymentMethod(Builder $query, string $paymentMethod): Builder
    {
        return $query->where('payment_method', $paymentMethod);
    }

    public function today(): Builder
    {
        return $this->baseQuery()->whereDate('sale_date', today());
    }

    public function thisMonth(): Builder
    {
        return $this->baseQuery()->whereBetween('sale_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }
}
