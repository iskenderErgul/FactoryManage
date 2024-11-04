<?php

namespace App\Domains\Product\Repositories;

use App\Domains\Product\Interfaces\ProductRepositoryInterface;
use App\Domains\Product\Models\Product;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Tüm ürünleri alır.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return Product::all();
    }

    /**
     * Yeni bir ürün oluşturur.
     *
     * @param StoreProductRequest $request  Ürün bilgilerini içeren istek
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    /**
     * Belirtilen ID'ye sahip ürünü alır.
     *
     * @param int $id  Ürün ID'si
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Belirtilen ID'ye sahip ürünü günceller.
     *
     * @param UpdateProductRequest $request  Güncellenecek ürün bilgilerini içeren istek
     * @param int $id  Güncellenecek ürün ID'si
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {

        $product= Product::where('id', $id)->update(
            [
                'product_name' => $request->product_name,
                'product_type' => $request->product_type,
                'production_cost' => $request->production_cost,
                'stock_quantity' => $request->stock_quantity,
                'description' => $request->description,
            ]
        );

        return response()->json($product);
    }

    /**
     * Belirtilen ID'ye sahip ürünü siler.
     *
     * @param int $id  Silinecek ürün ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    /**
     * Seçilen ürünleri siler.
     *
     * @param Request $request  Silinecek ürün ID'lerini içeren istek
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        Product::destroy($ids);
        return response()->json(null, 204);
    }
}
