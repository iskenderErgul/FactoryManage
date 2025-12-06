<?php

namespace App\Common\Helpers;

/**
 * ChartHelper - Chart verisi formatlama için yardımcı sınıf
 * 
 * Bu sınıf, Chart.js ve benzeri kütüphaneler için veri formatlamayı merkezi hale getirir.
 * DashboardController ve Report Service'lerde tekrar eden chart formatlama kodunu önler.
 */
class ChartHelper
{
    /**
     * Bar chart için veri formatlar
     * 
     * @param array $labels Etiketler
     * @param array $data Veriler
     * @param string $label Dataset etiketi
     * @param string $color Renk (hex veya rgba)
     * @return array Chart.js formatında veri
     */
    public static function formatBarChart(
        array $labels,
        array $data,
        string $label = 'Veri',
        string $color = '#3B82F6'
    ): array {
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $data,
                    'backgroundColor' => self::addAlpha($color, 0.8),
                    'borderColor' => $color,
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false
                ]
            ]
        ];
    }

    /**
     * Line chart için veri formatlar
     * 
     * @param array $labels Etiketler
     * @param array $data Veriler
     * @param string $label Dataset etiketi
     * @param string $color Renk
     * @return array Chart.js formatında veri
     */
    public static function formatLineChart(
        array $labels,
        array $data,
        string $label = 'Veri',
        string $color = '#3B82F6'
    ): array {
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $data,
                    'borderColor' => $color,
                    'backgroundColor' => self::addAlpha($color, 0.1),
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    /**
     * Pie/Doughnut chart için veri formatlar
     * 
     * @param array $labels Etiketler
     * @param array $data Veriler
     * @param array|null $colors Renkler (opsiyonel)
     * @return array Chart.js formatında veri
     */
    public static function formatPieChart(
        array $labels,
        array $data,
        ?array $colors = null
    ): array {
        $defaultColors = self::getDefaultColors();
        $colors = $colors ?? $defaultColors;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_map(
                        fn($color) => self::addAlpha($color, 0.8),
                        $colors
                    ),
                    'borderColor' => $colors,
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    /**
     * Çoklu dataset için bar chart formatlar
     * 
     * @param array $labels Etiketler
     * @param array $datasets Dataset'ler [['label' => '...', 'data' => [...], 'color' => '...']]
     * @return array Chart.js formatında veri
     */
    public static function formatMultiBarChart(array $labels, array $datasets): array
    {
        $formattedDatasets = [];

        foreach ($datasets as $dataset) {
            $color = $dataset['color'] ?? self::getDefaultColors()[count($formattedDatasets)];
            
            $formattedDatasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'backgroundColor' => self::addAlpha($color, 0.8),
                'borderColor' => $color,
                'borderWidth' => 2,
                'borderRadius' => 6,
                'borderSkipped' => false
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $formattedDatasets
        ];
    }

    /**
     * Çoklu dataset için line chart formatlar
     * 
     * @param array $labels Etiketler
     * @param array $datasets Dataset'ler
     * @return array Chart.js formatında veri
     */
    public static function formatMultiLineChart(array $labels, array $datasets): array
    {
        $formattedDatasets = [];

        foreach ($datasets as $dataset) {
            $color = $dataset['color'] ?? self::getDefaultColors()[count($formattedDatasets)];
            
            $formattedDatasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'borderColor' => $color,
                'backgroundColor' => self::addAlpha($color, 0.1),
                'borderWidth' => 2,
                'fill' => $dataset['fill'] ?? true,
                'tension' => 0.4
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $formattedDatasets
        ];
    }

    /**
     * Varsayılan renk paletini döner
     * 
     * @return array Hex renk kodları
     */
    public static function getDefaultColors(): array
    {
        return [
            '#3B82F6', // Blue
            '#10B981', // Green
            '#F59E0B', // Amber
            '#8B5CF6', // Violet
            '#EC4899', // Pink
            '#EF4444', // Red
            '#22C55E', // Green-500
            '#A855F7', // Purple
            '#06B6D4', // Cyan
            '#F97316', // Orange
        ];
    }

    /**
     * Hex renge alpha değeri ekler ve rgba formatına çevirir
     * 
     * @param string $hexColor Hex renk kodu (#RRGGBB)
     * @param float $alpha Alpha değeri (0-1)
     * @return string rgba renk kodu
     */
    public static function addAlpha(string $hexColor, float $alpha = 0.8): string
    {
        // # işaretini kaldır
        $hex = ltrim($hexColor, '#');

        // RGB değerlerini çıkar
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return "rgba($r, $g, $b, $alpha)";
    }

    /**
     * Tablo verisi için formatlar
     * 
     * @param array $data Ham veri
     * @param array $columns Gösterilecek kolonlar
     * @return array Formatlanmış tablo verisi
     */
    public static function formatTableData(array $data, array $columns): array
    {
        return array_map(function($row) use ($columns) {
            $formattedRow = [];
            foreach ($columns as $column) {
                $formattedRow[$column] = $row[$column] ?? null;
            }
            return $formattedRow;
        }, $data);
    }

    /**
     * Boş chart verisi döner (hata durumları için)
     * 
     * @param string $type Chart tipi (bar, line, pie)
     * @param string $label Dataset etiketi
     * @return array Boş chart verisi
     */
    public static function emptyChart(string $type = 'bar', string $label = 'Veri'): array
    {
        $baseStructure = [
            'labels' => [],
            'datasets' => []
        ];

        switch ($type) {
            case 'bar':
                $baseStructure['datasets'][] = [
                    'label' => $label,
                    'data' => [],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false
                ];
                break;

            case 'line':
                $baseStructure['datasets'][] = [
                    'label' => $label,
                    'data' => [],
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4
                ];
                break;

            case 'pie':
                $baseStructure['datasets'][] = [
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 2
                ];
                break;
        }

        return $baseStructure;
    }

    /**
     * Chart options için varsayılan ayarları döner
     * 
     * @param string $type Chart tipi
     * @return array Chart options
     */
    public static function getDefaultOptions(string $type = 'bar'): array
    {
        $baseOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top'
                ],
                'tooltip' => [
                    'enabled' => true
                ]
            ]
        ];

        if ($type === 'bar' || $type === 'line') {
            $baseOptions['scales'] = [
                'y' => [
                    'beginAtZero' => true
                ]
            ];
        }

        return $baseOptions;
    }
}
