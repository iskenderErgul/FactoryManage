<?php

namespace App\Domains\Production\Interfaces;

use App\DTOs\Production\StoreProductionDTO;
use App\DTOs\Production\UpdateProductionDTO;
use App\Http\Requests\Production\StoreByWorkerProductionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ProductionRepositoryInterface
{
    /**
     * Tüm üretim kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductions(): JsonResponse;

    /**
     * Tüm üretim loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductionLogs(): JsonResponse;

    /**
     * Çalışan tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreByWorkerProductionRequest $request
     * @return JsonResponse
     */
    public function storeByWorker(StoreByWorkerProductionRequest $request): JsonResponse;

    /**
     * Yönetici tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreProductionDTO $request
     * @return JsonResponse
     */
    public function storeByAdmin(StoreProductionDTO $request): JsonResponse;

    /**
     * Var olan bir üretim kaydını günceller.
     *
     * @param UpdateProductionDTO $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductionDTO $request, int $id): JsonResponse;

    /**
     * Belirtilen bir üretim kaydını siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;
}
