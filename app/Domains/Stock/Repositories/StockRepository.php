<?php

namespace App\Domains\Stock\Repositories;

use App\Common\Models\StockMovement;
use App\Common\Models\StockMovementsLog;
use App\Domains\Stock\Interfaces\StockRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StockRepository implements StockRepositoryInterface
{

    /**
     * Tüm stok hareket loglarını alır.
     *
     * @return JsonResponse
     */
    public function getStockMovementsLogs(): JsonResponse
    {
        $logs = StockMovementsLog::with('user')->get();
        return response()->json($logs);
    }

    /**
     * Tüm stok hareketlerini alır.
     *
     * @return JsonResponse
     */
    public function getStockMovements(): JsonResponse
    {
        $movements = StockMovement::with('product')->get();
        return response()->json($movements);
    }
}
