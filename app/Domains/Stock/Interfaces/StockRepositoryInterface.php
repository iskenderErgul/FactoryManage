<?php

namespace App\Domains\Stock\Interfaces;

use Illuminate\Http\JsonResponse;

interface StockRepositoryInterface
{
    public function getStockMovementsLogs(): JsonResponse ;

    public function getStockMovements(): JsonResponse ;
}
