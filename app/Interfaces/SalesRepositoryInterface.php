<?php

namespace App\Interfaces;

use App\Http\Requests\Sales\StoreSalesRequest;
use App\Http\Requests\Sales\UpdateSalesRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

interface SalesRepositoryInterface
{
    /**
     * Satışların listesini alır.
     *
     * @return Collection|array
     */
    public function index(): Collection|array;

    /**
     * Yeni bir satış kaydı oluşturur.
     *
     * @param StoreSalesRequest $request
     * @return JsonResponse
     */
    public function store(StoreSalesRequest $request): JsonResponse;

    /**
     * Var olan bir satış kaydını günceller.
     *
     * @param UpdateSalesRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateSalesRequest $request, int $id): JsonResponse;

    /**
     * Belirtilen bir satış kaydını siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;
}
