<?php

namespace App\Http\Repositories;

use App\Http\Requests\Production\DestroyProductionRequest;
use App\Http\Requests\Production\StoreByAdminProductionRequest;
use App\Http\Requests\Production\UpdateProductionRequest;
use App\Interfaces\ProductionRepositoryInterface;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionLog;
use App\Services\LoggerService;
use App\Services\StockMovementService;
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
    public function getAllProductions(): JsonResponse
    {
        $productions = Production::with(['machine', 'product', 'user', 'shift'])->get();

        return response()->json($productions);
    }
    public function getAllProductionLogs(): JsonResponse
    {
        $productionLogs = ProductionLog::with('user')->get();
        return response()->json($productionLogs);
    }
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
    public function storeByAdmin(StoreByAdminProductionRequest $request): JsonResponse
    {


        $formattedProductionDate = Carbon::parse($request->production_date)->format('Y-m-d H:i:s');

        $production = Production::create([
            'user_id' =>$request->worker_id,
            'machine_id' => $request->machine_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'shift_id' => $request->shift_id,
            'production_date' => $formattedProductionDate,
        ]);

        $this->stockMovementService->createStockMovement(
            $request->product_id,
            $request->quantity,
            'giriş',
            'Üretim',
            'create'
        );


        $this->loggerService->logProductionAction('create', $production, 'Yönetici tarafından üretim kaydı oluşturuldu.');

        $product = Product::findOrFail($request->product_id);
        $product->stock_quantity += $request->quantity;
        $product->save();


        return response()->json($production, 201);
    }
    public function update(UpdateProductionRequest $request, $id): JsonResponse
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