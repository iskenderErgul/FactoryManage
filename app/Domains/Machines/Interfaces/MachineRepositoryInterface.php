<?php

namespace App\Domains\Machines\Interfaces;

use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface MachineRepositoryInterface
{
    /**
     * Tüm makine verilerini listeler.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse;

    /**
     * Yeni bir makine kaydı oluşturur.
     *
     * @param StoreMachineRequest $request
     * @return JsonResponse
     */
    public function store(StoreMachineRequest $request): JsonResponse;

    /**
     * Belirli bir makine kaydını gösterir.
     *
     * @param int $id Makine kaydının benzersiz kimliği
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse;

    /**
     * Mevcut bir makine kaydını günceller.
     *
     * @param UpdateMachineRequest $request
     * @param int $id Güncellenmek istenen makine kaydının benzersiz kimliği
     * @return JsonResponse
     */
    public function update(UpdateMachineRequest $request, int $id): JsonResponse;

    /**
     * Belirli bir makine kaydını siler.
     *
     * @param int $id Silinecek makine kaydının benzersiz kimliği
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;

    /**
     * Seçili makine kayıtlarını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse;
}
