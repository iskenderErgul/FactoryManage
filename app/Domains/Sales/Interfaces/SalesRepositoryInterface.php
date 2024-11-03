<?php

namespace App\Domains\Sales\Interfaces;

use App\DTOs\Sales\SalesDTO;
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
     * @param SalesDTO $request
     * @return JsonResponse
     */
    public function store(SalesDTO $request): JsonResponse;

    /**
     * Var olan bir satış kaydını günceller.
     *
     * @param SalesDTO $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SalesDTO $request, int $id): JsonResponse;

    /**
     * Belirtilen bir satış kaydını siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;
}
