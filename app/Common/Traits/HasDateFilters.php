<?php

namespace App\Common\Traits;

use App\Common\Helpers\DateHelper;
use Carbon\Carbon;

/**
 * HasDateFilters Trait
 * 
 * Bu trait, tarih filtreleme işlemlerini standartlaştırır.
 * Controller ve Service sınıflarında kullanılabilir.
 */
trait HasDateFilters
{
    /**
     * Request'ten tarih aralığını çıkarır
     * 
     * @param \Illuminate\Http\Request $request
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getDateRangeFromRequest($request): array
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            return [
                'start' => Carbon::parse($request->start_date),
                'end' => Carbon::parse($request->end_date)
            ];
        }

        if ($request->has('period')) {
            return DateHelper::getDateRangeByPeriod($request->period);
        }

        // Varsayılan: Son 1 hafta
        return DateHelper::getDateRangeByPeriod('weekly');
    }

    /**
     * Periyoda göre başlangıç tarihini döner
     * 
     * @param string $period
     * @return Carbon
     */
    protected function getStartDateByPeriod(string $period): Carbon
    {
        return DateHelper::getStartDateByPeriod($period);
    }

    /**
     * Query'ye tarih aralığı filtresi ekler
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column Tarih kolonu adı
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateRangeFilter($query, string $column, $startDate, $endDate)
    {
        return $query->whereBetween($column, [$startDate, $endDate]);
    }

    /**
     * Bugünün tarih aralığını döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getTodayRange(): array
    {
        return DateHelper::getTodayRange();
    }

    /**
     * Bu haftanın tarih aralığını döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getThisWeekRange(): array
    {
        return DateHelper::getThisWeekRange();
    }

    /**
     * Bu ayın tarih aralığını döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getThisMonthRange(): array
    {
        return DateHelper::getThisMonthRange();
    }

    /**
     * Önceki periyodun tarih aralığını hesaplar
     * 
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getPreviousPeriodRange($startDate, $endDate): array
    {
        return DateHelper::getPreviousPeriodRange($startDate, $endDate);
    }

    /**
     * İki tarih arasındaki gün sayısını döner
     * 
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return int
     */
    protected function getDaysBetween($startDate, $endDate): int
    {
        return DateHelper::getDaysBetween($startDate, $endDate);
    }

    /**
     * Tarih aralığını formatlar
     * 
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array ['start' => string, 'end' => string, 'days' => int]
     */
    protected function formatDateRange($startDate, $endDate): array
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return [
            'start' => DateHelper::formatTurkish($start, 'short'),
            'end' => DateHelper::formatTurkish($end, 'short'),
            'days' => $this->getDaysBetween($start, $end)
        ];
    }
}
