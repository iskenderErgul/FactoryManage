<?php

namespace App\Common\Helpers;

use Carbon\Carbon;

/**
 * DateHelper - Tarih işlemleri için yardımcı sınıf
 * 
 * Bu sınıf, projede sıkça kullanılan tarih işlemlerini merkezi bir yerde toplar.
 * DRY (Don't Repeat Yourself) prensibine uygun olarak kod tekrarını önler.
 */
class DateHelper
{
    /**
     * Periyoda göre başlangıç tarihini hesaplar
     * 
     * @param string $period Periyot tipi (daily, weekly, biweekly, triweekly, monthly)
     * @return Carbon Hesaplanan başlangıç tarihi
     */
    public static function getStartDateByPeriod(string $period): Carbon
    {
        return match($period) {
            'daily' => Carbon::now()->subDay(),
            'weekly' => Carbon::now()->subWeek(),
            'biweekly' => Carbon::now()->subWeeks(2),
            'triweekly' => Carbon::now()->subWeeks(3),
            'monthly' => Carbon::now()->subMonth(),
            'quarterly' => Carbon::now()->subMonths(3),
            'yearly' => Carbon::now()->subYear(),
            default => Carbon::now()->subWeek()
        };
    }

    /**
     * Periyoda göre başlangıç ve bitiş tarihlerini döner
     * 
     * @param string $period Periyot tipi
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getDateRangeByPeriod(string $period): array
    {
        return [
            'start' => self::getStartDateByPeriod($period),
            'end' => Carbon::now()
        ];
    }

    /**
     * Tarih formatını Türkçe formatına çevirir
     * 
     * @param Carbon|string $date Tarih
     * @param string $format Format tipi (short, medium, long)
     * @return string Formatlanmış tarih
     */
    public static function formatTurkish(Carbon|string $date, string $format = 'medium'): string
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        return match($format) {
            'short' => $carbonDate->format('d.m.Y'),
            'medium' => $carbonDate->format('d.m.Y H:i'),
            'long' => $carbonDate->format('d.m.Y H:i:s'),
            'date_only' => $carbonDate->format('d.m.Y'),
            'time_only' => $carbonDate->format('H:i'),
            'chart' => $carbonDate->format('d M'),
            default => $carbonDate->format('d.m.Y H:i')
        };
    }

    /**
     * İki tarih arasındaki gün sayısını hesaplar
     * 
     * @param Carbon|string $startDate Başlangıç tarihi
     * @param Carbon|string $endDate Bitiş tarihi
     * @return int Gün sayısı
     */
    public static function getDaysBetween(Carbon|string $startDate, Carbon|string $endDate): int
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        return $start->diffInDays($end) + 1;
    }

    /**
     * Bugünün başlangıç ve bitiş saatlerini döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getTodayRange(): array
    {
        return [
            'start' => Carbon::today(),
            'end' => Carbon::now()
        ];
    }

    /**
     * Bu haftanın başlangıç ve bitiş tarihlerini döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getThisWeekRange(): array
    {
        return [
            'start' => Carbon::now()->startOfWeek(),
            'end' => Carbon::now()
        ];
    }

    /**
     * Bu ayın başlangıç ve bitiş tarihlerini döner
     * 
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getThisMonthRange(): array
    {
        return [
            'start' => Carbon::now()->startOfMonth(),
            'end' => Carbon::now()
        ];
    }

    /**
     * Önceki periyodun tarih aralığını hesaplar
     * 
     * @param Carbon|string $startDate Mevcut başlangıç tarihi
     * @param Carbon|string $endDate Mevcut bitiş tarihi
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function getPreviousPeriodRange(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $daysDiff = self::getDaysBetween($start, $end);

        return [
            'start' => $start->copy()->subDays($daysDiff),
            'end' => $start->copy()->subDay()
        ];
    }

    /**
     * Ay ismini Türkçe olarak döner
     * 
     * @param int $month Ay numarası (1-12)
     * @return string Ay ismi
     */
    public static function getMonthNameTurkish(int $month): string
    {
        $months = [
            1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
            5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
            9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
        ];

        return $months[$month] ?? '';
    }

    /**
     * Gün ismini Türkçe olarak döner
     * 
     * @param int $day Gün numarası (0-6, 0=Pazar)
     * @return string Gün ismi
     */
    public static function getDayNameTurkish(int $day): string
    {
        $days = [
            0 => 'Pazar', 1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba',
            4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi'
        ];

        return $days[$day] ?? '';
    }

    /**
     * Tarih aralığındaki tüm tarihleri array olarak döner
     * 
     * @param Carbon|string $startDate Başlangıç tarihi
     * @param Carbon|string $endDate Bitiş tarihi
     * @return array Carbon nesneleri dizisi
     */
    public static function getDatesBetween(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $end = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        $dates = [];
        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * Yıl bazlı karşılaştırma için tarih aralıklarını döner
     * 
     * @param int $year Yıl
     * @param int|null $month Ay (opsiyonel)
     * @return array ['current' => array, 'previous' => array]
     */
    public static function getYearOverYearRange(int $year, ?int $month = null): array
    {
        if ($month) {
            $currentStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $currentEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $previousStart = Carbon::createFromDate($year - 1, $month, 1)->startOfMonth();
            $previousEnd = Carbon::createFromDate($year - 1, $month, 1)->endOfMonth();
        } else {
            $currentStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $currentEnd = Carbon::createFromDate($year, 12, 31)->endOfYear();
            $previousStart = Carbon::createFromDate($year - 1, 1, 1)->startOfYear();
            $previousEnd = Carbon::createFromDate($year - 1, 12, 31)->endOfYear();
        }

        return [
            'current' => ['start' => $currentStart, 'end' => $currentEnd],
            'previous' => ['start' => $previousStart, 'end' => $previousEnd]
        ];
    }
}
