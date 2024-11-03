<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
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
    public function index(): Collection
    {
        return $this->productRepository->index();
    }
    public function store(StoreProductRequest $request): JsonResponse
    {
        return $this->productRepository->store($request);
    }
    public function show($id): JsonResponse
    {
        return $this->productRepository->show($id);
    }
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        return $this->productRepository->update($request, $id);
    }
    public function destroy($id): JsonResponse
    {
        return $this->productRepository->destroy($id);
    }
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->productRepository->destroySelected($request);
    }
}
