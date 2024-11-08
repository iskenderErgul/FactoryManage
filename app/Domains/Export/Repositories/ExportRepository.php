<?php

namespace App\Domains\Export\Repositories;

use App\Common\Models\StockMovement;
use App\Common\Models\StockMovementsLog;
use App\Common\Services\ExportService;
use App\Domains\Costs\Models\Cost;
use App\Domains\Export\Interfaces\ExportRepositoryInterface;
use App\Domains\PacsEntry\Models\PacsEntriesLog;
use App\Domains\PacsEntry\Models\PacsEntry;
use App\Domains\Production\Models\Production;
use App\Domains\Production\Models\ProductionLog;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesLog;
use App\Domains\Sales\Models\SalesProduct;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportRepository implements ExportRepositoryInterface
{
    /**
     * Genel export fonksiyonu.
     *
     * @param  string  $model  Model ismi
     * @param  array   $columns  Kolon isimleri
     * @param  string  $fileName  Çıktı dosya ismi
     * @return BinaryFileResponse
     */
    private function exportData($model, $columns, $fileName): BinaryFileResponse
    {
        $data = $model::select($columns)->get();
        $exportService = new ExportService($data, $columns);
        return $exportService->export($fileName);
    }

    /**
     * Cost'lar için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function costsExport(): BinaryFileResponse
    {
        $columns = ['id', 'cost_type', 'amount', 'cost_date', 'created_at', 'updated_at'];
        return $this->exportData(Cost::class, $columns, 'costs.xlsx');
    }

    /**
     * Production verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function productionExport(): BinaryFileResponse
    {
        $columns = ['id', 'user_id', 'machine_id', 'product_id', 'quantity', 'shift_id', 'production_date', 'created_at', 'updated_at'];
        return $this->exportData(Production::class, $columns, 'production.xlsx');
    }

    /**
     * Sales verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function salesExport(): BinaryFileResponse
    {
        $columns = ['id', 'customer_id', 'sale_date', 'created_at', 'updated_at'];
        return $this->exportData(Sales::class, $columns, 'sales.xlsx');
    }

    /**
     * SalesProduct verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function salesProductExport(): BinaryFileResponse
    {
        $columns = ['id', 'sales_id', 'product_id', 'quantity', 'price', 'created_at', 'updated_at'];
        return $this->exportData(SalesProduct::class, $columns, 'sales-product.xlsx');
    }

    /**
     * Pacs verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function pacsExport(): BinaryFileResponse
    {
        $columns = ['id', 'user_id', 'entry_type', 'created_at', 'updated_at'];
        return $this->exportData(PacsEntry::class, $columns, 'pacs.xlsx');
    }

    /**
     * Stock Movement verileri için export işlemi.
     *
     * @return BinaryFileResponse
     */
    public function stockMovementExport(): BinaryFileResponse
    {
        $columns = ['id', 'product_id', 'movement_type', 'quantity', 'related_process', 'movement_date', 'created_at', 'updated_at'];
        return $this->exportData(StockMovement::class, $columns, 'stock-movement.xlsx');
    }

    // Logların export fonksiyonları
    public function pacsLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'pacs_entry_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];
        return $this->exportData(PacsEntriesLog::class, $columns, 'pacs-log.xlsx');
    }

    public function productionLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'production_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];
        return $this->exportData(ProductionLog::class, $columns, 'production-log.xlsx');
    }

    public function salesLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'sale_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];
        return $this->exportData(SalesLog::class, $columns, 'sales-log.xlsx');
    }

    public function stockMovementLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'stock_movement_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];
        return $this->exportData(StockMovementsLog::class, $columns, 'stock-movement-log.xlsx');
    }
}
