<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockMovementsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
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
