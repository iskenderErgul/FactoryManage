<?php

namespace App\Http\Repositories;

use App\Common\Services\LoggerService;
use App\Common\Services\StockMovementService;
use App\DTOs\Sales\SalesDTO;
use App\Interfaces\SalesRepositoryInterface;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SalesRepository implements SalesRepositoryInterface
{

    protected StockMovementService $stockMovementService;
    protected LoggerService $loggerService;

    public function __construct(StockMovementService $stockMovementService, LoggerService $loggerService)
    {
        $this->stockMovementService = $stockMovementService;
        $this->loggerService = $loggerService;
    }
    public function index(): Collection|array
    {
        return Sales::with('customer','products')->get();
    }
    public function store(SalesDTO $request): JsonResponse
    {

        $products = $request->input('products');

        DB::beginTransaction();
        try {
            // Satış tarihi formatını düzenliyoruz
            $saleDate = Carbon::createFromFormat('d.m.Y', $request->sale_date)->format('Y-m-d');
            // Yeni satış kaydı oluşturuyoruz
            $sale = Sales::create([
                'customer_id' => $request->customer_id,
                'sale_date' => $saleDate,
            ]);

            foreach ($products as $product) {
                $productModel = Product::find($product['id']);

                if ($productModel && $productModel->stock_quantity >= $product['quantity']) {
                    // Satış ürün kaydı yapıyoruz
                    SalesProduct::create([
                        'sales_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                    ]);

                    // **Güncelleme**: Stok azaltma işlemini servis sınıfıyla yapıyoruz
                    $this->stockMovementService->reduceStock(
                        $product['id'],
                        $product['quantity'],
                        'Yeni satış oluşturma'
                    );
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Yetersiz stok: ' . $productModel->product_name,
                    ], 400);
                }
            }

            DB::commit();
            // **Güncelleme**: Satış log kaydını servis sınıfıyla yapıyoruz
            $this->loggerService->logSaleAction('create', $sale, 'Satış başarıyla oluşturuldu.','Sarış Kaydı Oluşturuldu');
            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function update(SalesDTO $request, $id): JsonResponse
    {

        $saleData = $request->only(['customer_id', 'sale_date', 'products']);
        $sale = Sales::findOrFail($id);


        $sale->update([
            'customer_id' => $saleData['customer_id'],
            'sale_date' => $saleData['sale_date'],
        ]);

        $existingProductIds = $sale->products->pluck('id')->toArray();

        foreach ($saleData['products'] as $productData) {
            $productId = $productData['id'];
            $quantity = $productData['pivot']['quantity'];
            $price = $productData['pivot']['price'];

            if (in_array($productId, $existingProductIds)) {

                $previousQuantity = $sale->products()->where('product_id', $productId)->first()->pivot->quantity;

                // **Güncelleme**: Stok miktarını servis sınıfıyla ayarlıyoruz
                $this->stockMovementService->updateStockQuantity($productId, $quantity, $previousQuantity);


                $sale->products()->updateExistingPivot($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'updated_at' => now(),
                ]);
            } else {
                $this->stockMovementService->reduceStock($productId, $quantity, 'Satış güncelleme');

                // Pivot tablosuna yeni ürün ekleme
                $sale->products()->attach($productId, [
                    'quantity' => $quantity,
                    'price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Mevcut satıştan çıkarılan ürünleri tespit ediyoruz
        $removedProductIds = array_diff($existingProductIds, array_column($saleData['products'], 'id'));

        foreach ($removedProductIds as $removedProductId) {
            $removedQuantity = $sale->products()->where('product_id', $removedProductId)->first()->pivot->quantity;

            $this->stockMovementService->increaseStock($removedProductId, $removedQuantity);

            // Pivot tablosundan ürünü çıkarma
            $sale->products()->detach($removedProductId);
        }

        $this->loggerService->logSaleAction('update', $sale, 'Satış güncelleme işlemi.');
        return response()->json($sale->load('products'), 200);
    }
    public function destroy($id): JsonResponse
    {
        $sale = Sales::findOrFail($id);
        $saleProducts = DB::table('sales_products')->where('sales_id', $id)->get();

        foreach ($saleProducts as $saleProduct) {
            $this->stockMovementService->increaseStock($saleProduct->product_id, $saleProduct->quantity);
        }

        DB::table('sales_products')->where('sales_id', $id)->delete();

        $this->loggerService->logSaleAction('delete', $sale, 'Satış başarıyla silindi.');

        $sale->delete();
        return response()->json(null, 204);
    }

}
