<?php

namespace App\Http\Controllers\Exports;

use App\Common\Models\StockMovement;
use App\Common\Models\StockMovementsLog;
use App\Domains\Costs\Models\Cost;
use App\Domains\PacsEntry\Models\PacsEntriesLog;
use App\Domains\PacsEntry\Models\PacsEntry;
use App\Domains\Production\Models\Production;
use App\Domains\Production\Models\ProductionLog;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesLog;
use App\Domains\Sales\Models\SalesProduct;
use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function costsExport(): BinaryFileResponse
    {

        $columns = ['id', 'cost_type', 'amount', 'cost_date','created_at','updated_at']; // İstediğiniz kolonları buraya ekleyin
        $data = Cost::select($columns)->get();


        $exportService = new ExportService($data, $columns);

        return $exportService->export('costs.xlsx');
    }

    public function productionExport(): BinaryFileResponse
    {

        $columns = ['id', 'user_id', 'machine_id', 'product_id','quantity','shift_id','production_date','created_at','updated_at']; // İstediğiniz kolonları buraya ekleyin
        $data = Production::select($columns)->get();


        $exportService = new ExportService($data, $columns);

        return $exportService->export('production.xlsx');
    }


    //SaleExport ve SalesProductExport tabloları daha sonra bir strategy yazılarak içeriği düzenlenecek.
    public function salesExport(): BinaryFileResponse
    {

        $columns = ['id', 'customer_id', 'sale_date','created_at','updated_at'];
        $data = Sales::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('sales.xlsx');
    }
    public function salesProductExport(): BinaryFileResponse
    {

        $columns = ['id', 'sales_id', 'product_id','quantity','price','created_at','updated_at'];
        $data = SalesProduct::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('sales.xlsx');
    }
    public function pacsExport(): BinaryFileResponse
    {

        $columns = ['id', 'user_id', 'entry_type','created_at','updated_at'];
        $data = PacsEntry::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('pacs.xlsx');
    }

    public function stockMovementExport(): BinaryFileResponse
    {
        $columns = ['id', 'product_id', 'movement_type','quantity','related_process','movement_date','created_at','updated_at'];
        $data = StockMovement::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('stock-movement.xlsx');
    }

    //Logların Exportları

   public function pacsLogExport(): BinaryFileResponse
   {
       $columns = ['id', 'pacs_entry_id', 'user_id','action','changes','created_at','updated_at'];
       $data = PacsEntriesLog::select($columns)->get();

       $exportService = new ExportService($data, $columns);

       return $exportService->export('pacs-log.xlsx');
   }

    public function productionLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'production_id', 'user_id','action','changes','created_at','updated_at'];
        $data = ProductionLog::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('production-log.xlsx');
    }

    public function salesLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'sale_id', 'user_id','action','changes','created_at','updated_at'];
        $data = SalesLog::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('sales-log.xlsx');
    }

    public function stockMovementLogExport(): BinaryFileResponse
    {
        $columns = ['id', 'stock_movement_id', 'user_id','action','changes','created_at','updated_at'];
        $data = StockMovementsLog::select($columns)->get();

        $exportService = new ExportService($data, $columns);

        return $exportService->export('sales-log.xlsx');
    }






}
