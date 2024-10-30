<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::all();
    }

    // Yeni ürün oluştur
    public function store(Request $request): JsonResponse
    {

        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    // Belirli bir ürünü göster
    public function show($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    // Ürünü güncelle
    public function update(Request $request, $id): JsonResponse
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

    // Seçili ürünleri sil
    public function destroySelected(Request $request)
    {
        $ids = $request->input('ids');

        Product::destroy($ids);

        return response()->json(null, 204);
    }
}
