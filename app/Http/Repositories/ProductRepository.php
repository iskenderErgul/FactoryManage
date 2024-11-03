<?php

namespace App\Http\Repositories;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductRepository implements ProductRepositoryInterface
{

    public function index(): Collection
    {
        return Product::all();
    }
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }
    public function show($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
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
    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }
    public function destroySelected(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        Product::destroy($ids);
        return response()->json(null, 204);
    }
}
