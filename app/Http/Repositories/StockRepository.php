<?php

namespace App\Http\Repositories;

use App\Interfaces\StockRepositoryInterface;
use App\Models\StockMovement;
use App\Models\StockMovementsLog;
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
