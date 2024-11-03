<?php

namespace App\Http\Controllers\Stock;

use App\Domains\Stock\Repositories\StockRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

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
