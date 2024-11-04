<?php

namespace App\Domains\Production\Repositories;

use App\Common\Services\LoggerService;
use App\Common\Services\StockMovementService;
use App\Domains\Product\Models\Product;
use App\Domains\Production\Interfaces\ProductionRepositoryInterface;
use App\Domains\Production\Models\Production;
use App\Domains\Production\Models\ProductionLog;
use App\DTOs\Production\StoreProductionDTO;
use App\DTOs\Production\UpdateProductionDTO;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionRepository implements ProductionRepositoryInterface
{
    protected StockMovementService $stockMovementService;
    protected LoggerService $loggerService;

    public function __construct(StockMovementService $stockMovementService, LoggerService $loggerService)
    {
        $this->stockMovementService = $stockMovementService;
        $this->loggerService = $loggerService;
    }

    /**
     * Tüm üretim kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductions(): JsonResponse
    {
        $productions = Production::with(['machine', 'product', 'user', 'shift'])->get();

        return response()->json($productions);
    }

    /**
     * Tüm üretim loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductionLogs(): JsonResponse
    {
        $productionLogs = ProductionLog::with('user')->get();
        return response()->json($productionLogs);
    }

    /**
     * İşçi tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param Request $request  Üretim kaydı oluşturmak için gerekli bilgiler
     * @return JsonResponse
     */
    public function storeByWorker(Request $request): JsonResponse
    {


        $production = Production::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'machine_id' => $request->machine_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'production_date' => now(),
        ]);

        $this->stockMovementService->createStockMovement(
            $request->product_id,
            $request->quantity,
            'giriş',
            'Üretim',
            'create'
        );

        $this->loggerService->logProductionAction('create', $production, 'İşçi tarafından üretim kaydı oluşturuldu.');

        $product = Product::findOrFail($request->product_id);
        $product->stock_quantity += $request->quantity;
        $product->save();




        return response()->json($production, 201);
    }

    /**
     * Yönetici tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreProductionDTO $request  Üretim kaydı oluşturmak için gerekli bilgiler
     * @return JsonResponse
     */
    public function storeByAdmin(StoreProductionDTO $request): JsonResponse
    {


        $formattedProductionDate = Carbon::parse($request->productionDate)->format('Y-m-d H:i:s');

        $production = Production::create([
            'user_id' =>$request->workerId,
            'machine_id' => $request->machineId,
            'product_id' => $request->productId,
            'quantity' => $request->quantity,
            'shift_id' => $request->shiftId,
            'production_date' => $formattedProductionDate,
        ]);

        $this->stockMovementService->createStockMovement(
            $request->productId,
            $request->quantity,
            'giriş',
            'Üretim',
            'create'
        );


        $this->loggerService->logProductionAction('create', $production, 'Yönetici tarafından üretim kaydı oluşturuldu.');

        $product = Product::findOrFail($request->productId);
        $product->stock_quantity += $request->quantity;
        $product->save();


        return response()->json($production, 201);
    }

    /**
     * Belirtilen ID'ye sahip üretim kaydını günceller.
     *
     * @param UpdateProductionDTO $request  Güncellenecek üretim bilgilerini içeren istek
     * @param int $id  Güncellenecek üretim kaydı ID'si
     * @return JsonResponse
     */
    public function update(UpdateProductionDTO $request, $id): JsonResponse
    {

        $formattedProductionDate = Carbon::parse($request->production_date)->format('Y-m-d H:i:s');
        $production = Production::findOrFail($id);

        // Mevcut değerleri sakla
        $previousQuantity = $production->quantity;
        $previousProductId = $production->product_id;
        $newQuantity = $request->quantity;
        $newProductId = $request->product_id;

        $logMessage = '';

        // Ürün değişikliği kontrolü
        if ($previousProductId !== $newProductId) {
            // Eski ürünün stok miktarını azalt
            $this->stockMovementService->reduceStock($previousProductId, $previousQuantity, 'Üretim Güncelleme');
            // Yeni ürünün stok miktarını artır
            $this->stockMovementService->increaseStock($newProductId, $newQuantity);
            $logMessage = "Ürün değişti. Eski Ürün: {$previousProductId}, Yeni Ürün: {$newProductId}.";
        } else {
            // Ürün değişmediği takdirde sadece miktar güncellemesi
            if ($newQuantity !== $previousQuantity) {
                $this->stockMovementService->updateStockQuantity($newProductId, $newQuantity, $previousQuantity);
                $logMessage = "Miktar güncellendi. Eski Miktar: {$previousQuantity}, Yeni Miktar: {$newQuantity}.";
            }
        }

        // Üretim kaydını güncelle
        $production->update([
            'user_id' => $request->worker_id,
            'machine_id' => $request->machine_id,
            'product_id' => $newProductId,
            'quantity' => $newQuantity,
            'shift_id' => $request->shift_id,
            'production_date' => $formattedProductionDate,
        ]);

        if (empty($logMessage)) {
            $logMessage = 'Üretim kaydı güncellendi.';
        }
        $this->loggerService->logProductionAction('update', $production, $logMessage);


        return response()->json($production);
    }

    /**
     * Belirtilen ID'ye sahip üretim kaydını siler.
     *
     * @param int $id  Silinecek üretim kaydı ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {

        $production = Production::findOrFail($id);

        $productId = $production->product_id;
        $quantity = $production->quantity;


        $product = Product::findOrFail($productId);

        // Stok hareketi ekleme
        $this->stockMovementService->createStockMovement($productId, $quantity, 'çıkış', 'Üretim Silme','delete');


        $product->stock_quantity -= $quantity;
        $product->save();


        // Loglama işlemi
        $this->loggerService->logProductionAction('delete', $production, 'Üretim kaydı silindi.');

        $production->delete();

        return response()->json(null, 204);
    }
}
