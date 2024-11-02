<?php

namespace App\Http\Controllers\Sales;

use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController
{
// Tüm satışları listele
    public function index()
    {
        return Sales::with('customer','products')->get();
    }



    public function store(Request $request): JsonResponse
    {
        $products = $request->input('products');


        DB::beginTransaction();
        try {

            $saleDate = Carbon::createFromFormat('d.m.Y', $request->sale_date)->format('Y-m-d');
            $sale = Sales::create([
                'customer_id' => $request->customer_id,
                'sale_date' => $saleDate,
            ]);

            foreach ($products as $product) {
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    if ($productModel->stock_quantity < $product['quantity']) {

                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Yetersiz stok: ' . $productModel->product_name,
                        ], 400);
                    }


                    $saleProduct = SalesProduct::create([
                        'sales_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                    ]);


                    $productModel->stock_quantity -= $product['quantity'];
                    $productModel->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id): JsonResponse
    {
        $saleData = $request->only(['customer_id', 'sale_date', 'products']);
        $sale = Sales::findOrFail($id);

        // 1. Customer ve sale_date güncellenmesi
        $sale->update([
            'customer_id' => $saleData['customer_id'],
            'sale_date' => $saleData['sale_date'],
        ]);

        // 2. Gelen ürünlerin mevcut ürünlerle kıyaslanması
        $existingProductIds = $sale->products->pluck('id')->toArray();

        foreach ($saleData['products'] as $productData) {
            $productId = $productData['id'];
            $quantity = $productData['pivot']['quantity'];
            $price = $productData['pivot']['price'];

            $product = Product::findOrFail($productId);

            if (in_array($productId, $existingProductIds)) {
                // Mevcut ürün: stok ve pivot güncellemeleri
                $previousQuantity = $sale->products()->where('product_id', $productId)->first()->pivot->quantity;

                // Stok ayarlaması
                $stockAdjustment = $previousQuantity - $quantity;
                $product->stock_quantity += $stockAdjustment;
                $product->save();

                // Pivot tablosunda güncelle
                $sale->products()->updateExistingPivot($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'updated_at' => now(),
                ]);

            } else {
                // Yeni ürün: stok düşülmesi ve pivot kaydı
                $product->stock_quantity -= $quantity;
                $product->save();

                // Pivot tablosuna yeni kayıt ekle
                $sale->products()->attach($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Eski siparişten kaldırılan ürünlerin stok geri eklenmesi
        $updatedProductIds = array_column($saleData['products'], 'id');
        $removedProductIds = array_diff($existingProductIds, $updatedProductIds);

        foreach ($removedProductIds as $removedProductId) {
            $removedProduct = Product::findOrFail($removedProductId);
            $removedQuantity = $sale->products()->where('product_id', $removedProductId)->first()->pivot->quantity;

            // Stok geri ekle
            $removedProduct->stock_quantity += $removedQuantity;
            $removedProduct->save();

            // Pivot kaydı sil
            $sale->products()->detach($removedProductId);
        }

        return response()->json($sale->load('products'), 200);
    }

    public function destroy($id): JsonResponse
    {

        $sale = Sales::findOrFail($id);
        $saleProducts = DB::table('sales_products')->where('sales_id', $id)->get();

        foreach ($saleProducts as $saleProduct) {
            DB::table('products')
                ->where('id', $saleProduct->product_id)
                ->increment('stock_quantity', $saleProduct->quantity);
        }

        DB::table('sales_products')->where('sales_id', $id)->delete();

        $sale->delete();
        return response()->json(null, 204);
    }


}
