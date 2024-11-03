<?php

namespace App\Interfaces;

use App\Http\Requests\Production\StoreByAdminProductionRequest;
use App\Http\Requests\Production\UpdateProductionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ProductionRepositoryInterface
{
    public function getAllProductions(): JsonResponse;

    public function getAllProductionLogs(): JsonResponse;

    public function storeByWorker(Request $request): JsonResponse;

    public function storeByAdmin(StoreByAdminProductionRequest $request): JsonResponse;

    public function update(UpdateProductionRequest $request, $id): JsonResponse;

    public function destroy($id): JsonResponse;
}
