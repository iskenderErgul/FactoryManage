<?php

namespace App\Domains\Product\Interfaces;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ProductRepositoryInterface
{
    /**
     * Tüm ürünleri listele.
     *
     * @return Collection
     */
    public function index(): Collection;

    /**
     * Yeni bir ürünü kaydet.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse;

    /**
     * Belirtilen ID'ye sahip ürünü göster.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse;

    /**
     * Belirtilen ID'ye sahip ürünü güncelle.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse;

    /**
     * Belirtilen ID'ye sahip ürünü sil.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;

    /**
     * Seçili ürünleri sil.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse;
}
