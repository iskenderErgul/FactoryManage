<?php

namespace App\Http\Controllers\Sales;

use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesLog;
use App\Models\SalesProduct;
use App\Models\StockMovement;
use App\Models\StockMovementsLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController
{
// Tüm satışları listele
    public function index(): Collection|array
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

                    // Stok hareketi kaydetme
                    $stockMovement=StockMovement::create([
                        'product_id' => $product['id'],
                        'movement_type' => 'çıkış', // Stok çıkışı
                        'quantity' => $product['quantity'],
                        'related_process' => 'Yeni satış oluşturma',
                        'movement_date' => now(),
                    ]);

                    $this->logStockAction('create', $stockMovement, 'Satış işlemi.');
                }
            }

            DB::commit();
            $this->logSaleAction('create', $sale, 'Satış başarıyla oluşturuldu.');
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

                // Stok hareketi kaydetme
                $stockMovement = StockMovement::create([
                    'product_id' => $productId,
                    'movement_type' => 'giriş', // Eski miktar geri ekleniyor
                    'quantity' => $stockAdjustment,
                    'related_process' => 'Üretim Güncelleme',
                    'movement_date' => now(),
                ]);

                // Pivot tablosunda güncelle
                $sale->products()->updateExistingPivot($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'updated_at' => now(),
                ]);

                $this->logStockAction('update', $stockMovement, 'Satış güncelleme işlemi.');

            } else {
                // Yeni ürün: stok düşülmesi ve pivot kaydı
                $product->stock_quantity -= $quantity;
                $product->save();

                // Stok hareketi kaydetme
                $stockMovement = StockMovement::create([
                    'product_id' => $productId,
                    'movement_type' => 'çıkış', // Yeni ürün ekleniyor
                    'quantity' => $quantity,
                    'related_process' => 'Üretim Güncelleme',
                    'movement_date' => now(),
                ]);

                // Pivot tablosuna yeni kayıt ekle
                $sale->products()->attach($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->logStockAction('create', $stockMovement, 'Satışa Yeni ürün eklendi.');
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

            // Stok hareketi kaydetme
            $stockMovement =StockMovement::create([
                'product_id' => $removedProductId,
                'movement_type' => 'giriş', // Eski ürün geri ekleniyor
                'quantity' => $removedQuantity,
                'related_process' => 'Üretim Güncelleme',
                'movement_date' => now(),
            ]);

            // Pivot kaydı sil
            $sale->products()->detach($removedProductId);

            $this->logStockAction('delete', $stockMovement, 'Satıştan Ürün kaldırıldı.');
        }
        $this->logSaleAction('update', $sale, 'Satış güncelleme işlemi.');

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


            // Stok hareketi kaydetme
            $stockMovement =StockMovement::create([
                'product_id' => $saleProduct->product_id,
                'movement_type' => 'giriş', // Stok geri ekleniyor
                'quantity' => $saleProduct->quantity,
                'related_process' => 'Satış silme',
                'movement_date' => now(),
            ]);

            $product = Product::find($saleProduct->product_id);
            if ($product) {
                $this->logStockAction(
                    'delete',
                    $stockMovement,
                    'Satış silindiğinde stok geri eklendi.'
                );
            }
        }

        DB::table('sales_products')->where('sales_id', $id)->delete();

        $this->logSaleAction('delete', $sale, 'Satış başarıyla silindi.');

        $sale->delete();
        return response()->json(null, 204);
    }

    private function logSaleAction($action, Sales $sale, $additionalInfo = ''): void
    {
        $message = '';

        switch ($action) {
            case 'create':
                $message = "Satış kaydı oluşturuldu. Müşteri ID: {$sale->customer_id}, Satış Tarihi: {$sale->sale_date}. $additionalInfo";
                break;
            case 'update':
                $message = "Satış kaydı güncellendi. Müşteri ID: {$sale->customer_id}, Yeni Satış Tarihi: {$sale->sale_date}. $additionalInfo";
                break;
            case 'delete':
                $message = "Satış kaydı silindi. Müşteri ID: {$sale->customer_id}, Satış Tarihi: {$sale->sale_date}. $additionalInfo";
                break;
            default:
                $message = $additionalInfo;
                break;
        }

        SalesLog::create([
            'sale_id' => $sale->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function logStockAction($action, StockMovement $stockMovement, $additionalInfo = ''): void
    {
        $message = '';

        switch ($action) {
            case 'create':
                $message = "Stok hareketi oluşturuldu. Ürün: {$stockMovement->product_id}, Miktar: {$stockMovement->quantity}. $additionalInfo";
                break;
            case 'update':
                $message = "Stok hareketi güncellendi. Ürün: {$stockMovement->product_id}, Yeni Miktar: {$stockMovement->quantity}. $additionalInfo";
                break;
            case 'delete':
                $message = "Stok hareketi silindi. Ürün: {$stockMovement->product_id}, Miktar: {$stockMovement->quantity}. $additionalInfo";
                break;
            default:
                $message = $additionalInfo;
                break;
        }

        StockMovementsLog::create([
            'stock_movement_id' => $stockMovement->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
        ]);
    }




}
