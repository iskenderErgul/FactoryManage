<?php

namespace App\Http\Controllers\Sales;

use App\Models\SalesProduct;
use Illuminate\Http\Request;

class SalesProductController
{
// Belirli bir satışa ait ürünleri listele
    public function index($saleId)
    {
        $products = SalesProduct::where('sales_id', $saleId)->get();
        return response()->json($products);
    }

    // Satışa yeni ürün ekle
    public function store(Request $request, $saleId)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['sales_id'] = $saleId;
        $product = SalesProduct::create($validated);

        return response()->json($product, 201);
    }

    // Satışa bağlı ürünü güncelle
    public function update(Request $request, $saleId, $productId)
    {
        $product = SalesProduct::where('sales_id', $saleId)->where('id', $productId)->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    // Satışa bağlı ürünü sil
    public function destroy($saleId, $productId)
    {
        $product = SalesProduct::where('sales_id', $saleId)->where('id', $productId)->firstOrFail();
        $product->delete();

        return response()->json(['message' => 'Ürün başarıyla silindi.']);
    }
}
