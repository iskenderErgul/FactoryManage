<?php

namespace App\Domains\Stock\Repositories;

use App\Common\Models\StockMovement;
use App\Common\Models\StockMovementsLog;
use App\Domains\Stock\Interfaces\StockRepositoryInterface;
use Illuminate\Http\JsonResponse;

class StockRepository implements StockRepositoryInterface
{
    public function getStockMovementsLogs(): JsonResponse
    {
        $logs = StockMovementsLog::with('user')->get();
        return response()->json($logs);
    }

    public function getStockMovements(): JsonResponse
    {
        $movements = StockMovement::all();
        return response()->json($movements);
    }
}
