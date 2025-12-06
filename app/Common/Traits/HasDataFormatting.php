    <?php

namespace App\Common\Traits;

use App\Common\Helpers\ChartHelper;

/**
 * HasDataFormatting Trait
 *
 * Bu trait, veri formatlama işlemlerini standartlaştırır.
 * Chart ve tablo verilerini formatlamak için kullanılır.
 */
trait HasDataFormatting
{
    /**
     * Bar chart verisi formatlar
     *
     * @param array $labels
     * @param array $data
     * @param string $label
     * @param string $color
     * @return array
     */
    protected function formatBarChart(
        array $labels,
        array $data,
        string $label = 'Veri',
        string $color = '#3B82F6'
    ): array {
        return ChartHelper::formatBarChart($labels, $data, $label, $color);
    }

    /**
     * Line chart verisi formatlar
     *
     * @param array $labels
     * @param array $data
     * @param string $label
     * @param string $color
     * @return array
     */
    protected function formatLineChart(
        array $labels,
        array $data,
        string $label = 'Veri',
        string $color = '#3B82F6'
    ): array {
        return ChartHelper::formatLineChart($labels, $data, $label, $color);
    }

    /**
     * Pie chart verisi formatlar
     *
     * @param array $labels
     * @param array $data
     * @param array|null $colors
     * @return array
     */
    protected function formatPieChart(
        array $labels,
        array $data,
        ?array $colors = null
    ): array {
        return ChartHelper::formatPieChart($labels, $data, $colors);
    }

    /**
     * Çoklu dataset bar chart formatlar
     *
     * @param array $labels
     * @param array $datasets
     * @return array
     */
    protected function formatMultiBarChart(array $labels, array $datasets): array
    {
        return ChartHelper::formatMultiBarChart($labels, $datasets);
    }

    /**
     * Çoklu dataset line chart formatlar
     *
     * @param array $labels
     * @param array $datasets
     * @return array
     */
    protected function formatMultiLineChart(array $labels, array $datasets): array
    {
        return ChartHelper::formatMultiLineChart($labels, $datasets);
    }

    /**
     * Boş chart verisi döner
     *
     * @param string $type
     * @param string $label
     * @return array
     */
    protected function emptyChart(string $type = 'bar', string $label = 'Veri'): array
    {
        return ChartHelper::emptyChart($type, $label);
    }

    /**
     * Tablo verisi formatlar
     *
     * @param array $data
     * @param array $columns
     * @return array
     */
    protected function formatTableData(array $data, array $columns): array
    {
        return ChartHelper::formatTableData($data, $columns);
    }

    /**
     * Response formatlar (chart + table)
     *
     * @param array $chartData
     * @param array $tableData
     * @param array $additional
     * @return array
     */
    protected function formatResponse(
        array $chartData,
        array $tableData,
        array $additional = []
    ): array {
        return array_merge([
            'chartData' => $chartData,
            'tableData' => $tableData
        ], $additional);
    }

    /**
     * Hata response'u formatlar
     *
     * @param string $chartType
     * @param string $message
     * @return array
     */
    protected function formatErrorResponse(
        string $chartType = 'bar',
        string $message = 'Veri bulunamadı'
    ): array {
        return [
            'chartData' => $this->emptyChart($chartType),
            'tableData' => [],
            'message' => $message,
            'error' => true
        ];
    }
}
