<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Http\Repositories\StockRepository;
use App\Models\StockMovement;
use App\Models\StockMovementsLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockRepository $stockRepository;


    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }
    public function getStockMovementsLogs(): JsonResponse
    {
        return $this->stockRepository->getStockMovementsLogs();
    }

    public function getStockMovements(): JsonResponse
    {
        return $this->stockRepository->getStockMovements();
    }
}
