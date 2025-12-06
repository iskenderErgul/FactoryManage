<?php

namespace App\Common\Helpers;

/**
 * StatisticsHelper - İstatistik hesaplamaları için yardımcı sınıf
 * 
 * Bu sınıf, projede kullanılan istatistiksel hesaplamaları merkezi hale getirir.
 * Ortalama, yüzde değişim, trend analizi gibi yaygın işlemler için metodlar sağlar.
 */
class StatisticsHelper
{
    /**
     * Ortalama hesaplar
     * 
     * @param array $values Değerler
     * @param int $decimals Ondalık basamak sayısı
     * @return float Ortalama değer
     */
    public static function average(array $values, int $decimals = 2): float
    {
        if (empty($values)) {
            return 0;
        }

        return round(array_sum($values) / count($values), $decimals);
    }

    /**
     * Yüzde değişim hesaplar
     * 
     * @param float|int $oldValue Eski değer
     * @param float|int $newValue Yeni değer
     * @param int $decimals Ondalık basamak sayısı
     * @return float Yüzde değişim
     */
    public static function percentageChange($oldValue, $newValue, int $decimals = 2): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, $decimals);
    }

    /**
     * Yüzde oran hesaplar
     * 
     * @param float|int $part Parça değer
     * @param float|int $total Toplam değer
     * @param int $decimals Ondalık basamak sayısı
     * @return float Yüzde oran
     */
    public static function percentage($part, $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($part / $total) * 100, $decimals);
    }

    /**
     * Büyüme oranı hesaplar
     * 
     * @param float|int $currentValue Mevcut değer
     * @param float|int $previousValue Önceki değer
     * @param int $decimals Ondalık basamak sayısı
     * @return array ['rate' => float, 'trend' => string]
     */
    public static function growthRate($currentValue, $previousValue, int $decimals = 2): array
    {
        $rate = self::percentageChange($previousValue, $currentValue, $decimals);
        
        $trend = match(true) {
            $rate > 0 => 'up',
            $rate < 0 => 'down',
            default => 'stable'
        };

        return [
            'rate' => $rate,
            'trend' => $trend,
            'absolute_change' => $currentValue - $previousValue
        ];
    }

    /**
     * Toplam hesaplar
     * 
     * @param array $values Değerler
     * @return float|int Toplam
     */
    public static function sum(array $values)
    {
        return array_sum($values);
    }

    /**
     * Minimum değeri bulur
     * 
     * @param array $values Değerler
     * @return float|int|null Minimum değer
     */
    public static function min(array $values)
    {
        return empty($values) ? null : min($values);
    }

    /**
     * Maximum değeri bulur
     * 
     * @param array $values Değerler
     * @return float|int|null Maximum değer
     */
    public static function max(array $values)
    {
        return empty($values) ? null : max($values);
    }

    /**
     * Medyan hesaplar
     * 
     * @param array $values Değerler
     * @param int $decimals Ondalık basamak sayısı
     * @return float Medyan değer
     */
    public static function median(array $values, int $decimals = 2): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return round(($values[$middle - 1] + $values[$middle]) / 2, $decimals);
        }

        return round($values[$middle], $decimals);
    }

    /**
     * Standart sapma hesaplar
     * 
     * @param array $values Değerler
     * @param int $decimals Ondalık basamak sayısı
     * @return float Standart sapma
     */
    public static function standardDeviation(array $values, int $decimals = 2): float
    {
        if (empty($values)) {
            return 0;
        }

        $mean = self::average($values, 10);
        $variance = array_sum(array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / count($values);

        return round(sqrt($variance), $decimals);
    }

    /**
     * Trend analizi yapar
     * 
     * @param array $values Zaman serisi değerleri
     * @return array ['trend' => string, 'analysis' => string, 'slope' => float]
     */
    public static function analyzeTrend(array $values): array
    {
        if (count($values) < 2) {
            return [
                'trend' => 'insufficient_data',
                'analysis' => 'Trend analizi için en az 2 veri noktası gereklidir.',
                'slope' => 0
            ];
        }

        // İlk yarı ve ikinci yarı ortalamalarını karşılaştır
        $halfPoint = (int) ceil(count($values) / 2);
        $firstHalf = array_slice($values, 0, $halfPoint);
        $secondHalf = array_slice($values, $halfPoint);

        $firstAvg = self::average($firstHalf);
        $secondAvg = self::average($secondHalf);

        // Eğim hesapla (basit lineer trend)
        $slope = ($secondAvg - $firstAvg) / $halfPoint;

        // Trend belirleme
        if ($secondAvg > $firstAvg * 1.1) {
            $trend = 'increasing';
            $analysis = 'Değerler dönem içinde artış göstermektedir.';
        } elseif ($secondAvg < $firstAvg * 0.9) {
            $trend = 'decreasing';
            $analysis = 'Değerler dönem içinde azalma göstermektedir.';
        } else {
            $trend = 'stable';
            $analysis = 'Değerler dönem içinde dengeli seyretmektedir.';
        }

        return [
            'trend' => $trend,
            'analysis' => $analysis,
            'slope' => round($slope, 2),
            'first_half_avg' => round($firstAvg, 2),
            'second_half_avg' => round($secondAvg, 2)
        ];
    }

    /**
     * Günlük ortalama hesaplar
     * 
     * @param float|int $total Toplam değer
     * @param int $days Gün sayısı
     * @param int $decimals Ondalık basamak sayısı
     * @return float Günlük ortalama
     */
    public static function dailyAverage($total, int $days, int $decimals = 2): float
    {
        if ($days <= 0) {
            return 0;
        }

        return round($total / $days, $decimals);
    }

    /**
     * Verimliliği hesaplar (gerçekleşen / hedef)
     * 
     * @param float|int $actual Gerçekleşen değer
     * @param float|int $target Hedef değer
     * @param int $decimals Ondalık basamak sayısı
     * @return array ['efficiency' => float, 'status' => string]
     */
    public static function efficiency($actual, $target, int $decimals = 2): array
    {
        if ($target <= 0) {
            return [
                'efficiency' => 0,
                'status' => 'no_target'
            ];
        }

        $efficiency = round(($actual / $target) * 100, $decimals);

        $status = match(true) {
            $efficiency >= 100 => 'excellent',
            $efficiency >= 80 => 'good',
            $efficiency >= 60 => 'average',
            $efficiency >= 40 => 'below_average',
            default => 'poor'
        };

        return [
            'efficiency' => $efficiency,
            'status' => $status,
            'difference' => $actual - $target
        ];
    }

    /**
     * Veri dağılımını hesaplar
     * 
     * @param array $data Veri dizisi
     * @param string $groupBy Gruplama anahtarı
     * @return array Dağılım istatistikleri
     */
    public static function distribution(array $data, string $groupBy): array
    {
        $grouped = [];
        $total = 0;

        foreach ($data as $item) {
            $key = $item[$groupBy] ?? 'unknown';
            $value = $item['value'] ?? 1;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = 0;
            }
            
            $grouped[$key] += $value;
            $total += $value;
        }

        // Yüzdeleri hesapla
        $distribution = [];
        foreach ($grouped as $key => $value) {
            $distribution[] = [
                'category' => $key,
                'value' => $value,
                'percentage' => self::percentage($value, $total)
            ];
        }

        // Değere göre sırala (büyükten küçüğe)
        usort($distribution, fn($a, $b) => $b['value'] <=> $a['value']);

        return $distribution;
    }

    /**
     * Karşılaştırma raporu oluşturur
     * 
     * @param array $current Mevcut dönem verileri
     * @param array $previous Önceki dönem verileri
     * @return array Karşılaştırma raporu
     */
    public static function comparePerformance(array $current, array $previous): array
    {
        $currentTotal = self::sum($current);
        $previousTotal = self::sum($previous);

        return [
            'current' => [
                'total' => $currentTotal,
                'average' => self::average($current),
                'min' => self::min($current),
                'max' => self::max($current)
            ],
            'previous' => [
                'total' => $previousTotal,
                'average' => self::average($previous),
                'min' => self::min($previous),
                'max' => self::max($previous)
            ],
            'comparison' => [
                'change' => $currentTotal - $previousTotal,
                'percentage_change' => self::percentageChange($previousTotal, $currentTotal),
                'trend' => $currentTotal >= $previousTotal ? 'up' : 'down'
            ]
        ];
    }

    /**
     * Top N değerleri döner
     * 
     * @param array $data Veri dizisi
     * @param int $n Döndürülecek eleman sayısı
     * @param string $sortBy Sıralama anahtarı
     * @return array Top N değerler
     */
    public static function topN(array $data, int $n = 5, string $sortBy = 'value'): array
    {
        usort($data, fn($a, $b) => ($b[$sortBy] ?? 0) <=> ($a[$sortBy] ?? 0));
        return array_slice($data, 0, $n);
    }
}
