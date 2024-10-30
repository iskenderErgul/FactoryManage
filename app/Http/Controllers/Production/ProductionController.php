<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionLog;
use App\Models\StockMovement;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProductionController extends Controller
{

    public function index(): JsonResponse
    {
        $productions = Production::with(['machine', 'product', 'user', 'shift'])->get();

        return response()->json($productions);
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

        StockMovement::create([
            'product_id' => $request->product_id,
            'movement_type' => 'giriş',
            'quantity' => $request->quantity,
            'related_process' => 'Üretim',
            'movement_date' => now(),
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->stock_quantity += $request->quantity;
        $product->save();


        $this->logProductionAction('create', $production, 'İşçi tarafından üretim kaydı oluşturuldu.');

        return response()->json($production, 201);
    }
    public function storeByAdmin(Request $request): JsonResponse
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

        StockMovement::create([
            'product_id' => $request->product_id,
            'movement_type' => 'giriş',
            'quantity' => $request->quantity,
            'related_process' => 'Üretim',
            'movement_date' => now(),
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->stock_quantity += $request->quantity;
        $product->save();


        $this->logProductionAction('create', $production, 'Yönetici tarafından üretim kaydı oluşturuldu.');

        return response()->json($production, 201);
    }
    public function update(Request $request, $id): JsonResponse
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
            $previousProduct = Product::findOrFail($previousProductId);
            $previousProduct->stock_quantity -= $previousQuantity;
            $previousProduct->save();

            // Yeni ürünün stok miktarını artır
            $newProduct = Product::findOrFail($newProductId);
            $newProduct->stock_quantity += $newQuantity;
            $newProduct->save();

            // Stok hareketi: Eski ürün çıkışı
            StockMovement::create([
                'product_id' => $previousProductId,
                'movement_type' => 'çıkış',
                'quantity' => $previousQuantity,
                'related_process' => 'Üretim Güncelleme',
                'movement_date' => now(),
            ]);

            $logMessage = "Ürün değişti. Eski Ürün: {$previousProduct->name}, Yeni Ürün: {$newProduct->name}.";
        } else {
            // Ürün değişmediği takdirde sadece miktar güncellemesi
            if ($newQuantity !== $previousQuantity) {
                // Miktar değişikliklerini uygula
                $newProduct = Product::findOrFail($newProductId);
                $newProduct->stock_quantity += ($newQuantity - $previousQuantity);
                $newProduct->save();


                $logMessage .= empty($logMessage) ? "Miktar güncellendi. Eski Miktar: {$previousQuantity}, Yeni Miktar: {$newQuantity}." : " Miktar güncellendi. Eski Miktar: {$previousQuantity}, Yeni Miktar: {$newQuantity}.";
            }
        }

        // Üretim kaydını güncelle
        $production->update([
            'user_id' => $request->user_id,
            'machine_id' => $request->machine_id,
            'product_id' => $newProductId,
            'quantity' => $newQuantity,
            'shift_id' => $request->shift_id,
            'production_date' => $formattedProductionDate,
        ]);

        if (empty($logMessage)) {
            $logMessage = 'Üretim kaydı güncellendi.';
        }
        $this->logProductionAction('update', $production, $logMessage);


        return response()->json($production);
    }
    public function destroy($id): JsonResponse
    {

        $production = Production::findOrFail($id);

        $productId = $production->product_id;
        $quantity = $production->quantity;


        $product = Product::findOrFail($productId);


        StockMovement::create([
            'product_id' => $productId,
            'movement_type' => 'çıkış',
            'quantity' => $quantity,
            'related_process' => 'Üretim Silme',
            'movement_date' => now(),
        ]);

        $product->stock_quantity -= $quantity;
        $product->save();

        $userId = Auth::id();

        $this->logProductionAction('delete', $production, "Üretim kaydı silindi.");

        $production->delete();

        return response()->json(null, 204);
    }
    public function getAllProductionLogs(): JsonResponse
    {
        $productionLogs = ProductionLog::with('user')->get();
        return response()->json($productionLogs);
    }
    private function logProductionAction($action, Production $production, $additionalInfo = ''): void
    {
        $message = '';

        switch ($action) {
            case 'create':
                $message = "Üretim kaydı oluşturuldu. Ürün: {$production->product->name}, Miktar: {$production->quantity}, Makine: {$production->machine->name}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
                break;
            case 'update':
                $message = "Üretim kaydı güncellendi. Ürün: {$production->product->name}, Yeni Miktar: {$production->quantity}, Makine: {$production->machine->name}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
                break;
            case 'delete':
                $message = "Üretim kaydı silindi. Ürün: {$production->product->name}, Miktar: {$production->quantity}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
                break;
            default:
                $message = $additionalInfo;
                break;
        }

        ProductionLog::create([
            'production_id' => $production->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


}
