<?php

namespace App\Http\Controllers\Product;

use App\Domains\Product\Repositories\ProductRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    protected ProductRepository $productRepository;


    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Tüm ürünleri döner.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return $this->productRepository->index();
    }

    /**
     * Yeni bir ürün kaydeder.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        return $this->productRepository->store($request);
    }

    /**
     * Belirtilen ürünün detaylarını döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->productRepository->show($id);
    }

    /**
     * Belirtilen ürünü günceller.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        return $this->productRepository->update($request, $id);
    }

    /**
     * Belirtilen ürünü siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->productRepository->destroy($id);
    }

    /**
     * Seçilen ürünleri siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->productRepository->destroySelected($request);
    }
}
