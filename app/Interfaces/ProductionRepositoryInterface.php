<?php

namespace App\Interfaces;

use App\Http\Requests\Production\StoreByAdminProductionRequest;
use App\Http\Requests\Production\UpdateProductionRequest;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function storeByWorker(Request $request): JsonResponse;

    /**
     * Yönetici tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreByAdminProductionRequest $request
     * @return JsonResponse
     */
    public function storeByAdmin(StoreByAdminProductionRequest $request): JsonResponse;

    /**
     * Var olan bir üretim kaydını günceller.
     *
     * @param UpdateProductionRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductionRequest $request, int $id): JsonResponse;

    /**
     * Belirtilen bir üretim kaydını siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;
}
