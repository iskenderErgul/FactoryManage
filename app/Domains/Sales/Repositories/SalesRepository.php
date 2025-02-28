<?php

namespace App\Domains\Sales\Repositories;



use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


use App\Common\Services\LoggerService;
use App\Common\Services\StockMovementService;
use App\Domains\Customer\Models\Transaction;
use App\Domains\Product\Models\Product;
use App\Domains\Sales\Interfaces\SalesRepositoryInterface;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesLog;
use App\Domains\Sales\Models\SalesProduct;
use App\DTOs\Sales\SalesDTO;
use App\Common\Services\TransactionService;

class SalesRepository implements SalesRepositoryInterface
{

    protected StockMovementService $stockMovementService;
    protected LoggerService $loggerService;

    public function __construct(StockMovementService $stockMovementService, LoggerService $loggerService,TransactionService $transactionService)
    {
        $this->stockMovementService = $stockMovementService;
        $this->loggerService = $loggerService;
        $this->transactionService = $transactionService;
    }

    /**
     * Tüm satış kayıtlarını alır.
     *
     * @return Collection|array
     */
    public function index(): Collection|array
    {
        return Sales::with('customer','products')->get();
    }

    /**
     * Yeni bir satış kaydı oluşturur.
     *
     * @param SalesDTO $request  Satış kaydı oluşturmak için gerekli bilgiler
     * @return JsonResponse
     */
    public function store(SalesDTO $request): JsonResponse
    {

        $products = $request->products;
        $totalAmount = 0;

        DB::beginTransaction();
        try {

            // Yeni satış kaydı oluşturuyoruz
            $sale = Sales::create([
                'customer_id' => $request->customer_id,
                'payment_type'=> $request->paymentType,
                'paid_amount'=> $request->partialPayment,
                'sale_date' => $request->sale_date,
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

                    $totalAmount += $product['price'] * $product['quantity'];



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
            $this->transactionService->createTransaction($sale, $totalAmount,$request->paymentType, $request->partialPayment);

            // **Güncelleme**: Satış log kaydını servis sınıfıyla yapıyoruz
            $this->loggerService->logSaleAction('create', $sale, 'Satış başarıyla oluşturuldu.','Yeni Satış Kaydı Oluşturuldu');

            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Belirtilen ID'ye sahip satış kaydını günceller.
     *
     * @param SalesDTO $request  Güncellenecek satış bilgilerini içeren istek
     * @param int $id  Güncellenecek satış kaydı ID'si
     * @return JsonResponse
     */
    public function update(SalesDTO $request, $id): JsonResponse
    {


        $customerId = $request->customer_id;
        $saleDate = $request->sale_date;
        $products = $request->products;
        $paymentType = $request->paymentType;
        $partialPayment = $request->partialPayment ?? 0;
        $totalAmount = 0;



        $sale = Sales::findOrFail($id);


        $sale->update([
            'customer_id' => $customerId,
            'sale_date' => $saleDate,
            'payment_type' => $paymentType,
            'paid_amount' => $partialPayment,
        ]);


        $existingProductIds = $sale->products->pluck('id')->toArray();

        foreach ($products as $productData) {
            $productId = $productData['id'];
            $quantity = $productData['pivot']['quantity'];
            $price = $productData['pivot']['price'];
            $totalAmount += $price * $quantity;
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
        $removedProductIds = array_diff($existingProductIds, array_column($products, 'id'));

        foreach ($removedProductIds as $removedProductId) {
            $removedQuantity = $sale->products()->where('product_id', $removedProductId)->first()->pivot->quantity;

            $this->stockMovementService->increaseStock($removedProductId, $removedQuantity);

            // Pivot tablosundan ürünü çıkarma
            $sale->products()->detach($removedProductId);
        }
        // Transaction'ı güncelleme

        $this->transactionService->updateTransaction($sale, $totalAmount, $paymentType, $partialPayment);



        $this->loggerService->logSaleAction('update', $sale, 'Satış güncelleme işlemi.','Satış kaydı Güncelleme İşlemi');
        return response()->json($sale->load('products'), 200);
    }

    /**
     * Belirtilen ID'ye sahip satış kaydını siler.
     *
     * @param int $id  Silinecek satış kaydı ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $sale = Sales::findOrFail($id);
        $saleProducts = DB::table('sales_products')->where('sales_id', $id)->get();

        foreach ($saleProducts as $saleProduct) {
            $this->stockMovementService->increaseStock($saleProduct->product_id, $saleProduct->quantity);
        }

        $transaction = Transaction::where('sale_id',$sale->id)->first();
        $transaction->delete();

        DB::table('sales_products')->where('sales_id', $id)->delete();

        $this->loggerService->logSaleAction('delete', $sale, 'Satış başarıyla silindi.','Satış Kaydı Silme İşlemi');

        $sale->delete();
        return response()->json(null, 204);
    }


    /**
     * Tüm satış loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllSalesLogs(): JsonResponse
    {
        $salesLogs = SalesLog::with('user','sale')->get();

        return response()->json($salesLogs);
    }





}
