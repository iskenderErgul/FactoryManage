<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Production;
use App\Models\StockMovement;
use App\Models\User;
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

        return response()->json($production, 201);
    }
    public function storeByAdmin(Request $request): JsonResponse
    {

        $formattedProductionDate = Carbon::parse($request->production_date)->format('Y-m-d H:i:s');

        $production = Production::create([
            'user_id' =>$request->worker_id,
            'machine_id' => $request->machine_id,
            'product_id' => $request->worker_id,
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

        return response()->json($production, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $production = Production::findOrFail($id);

        $previousQuantity = $production->quantity;
        $newQuantity = $request->quantity;

        StockMovement::create([
            'product_id' => $request->product,
            'movement_type' => 'giriş',
            'quantity' => $newQuantity - $previousQuantity,
            'related_process' => 'Üretim Güncelleme',
            'movement_date' => now(),
        ]);

        $production->update([
            'machine_id' => $request->machine,
            'product_id' => $request->product,
            'quantity' => $newQuantity,
        ]);

        $product = Product::findOrFail($request->product);
        $product->stock_quantity += ($newQuantity - $previousQuantity);
        $product->save();

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


        $production->delete();

        return response()->json(null, 204);
    }


}
