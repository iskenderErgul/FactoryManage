<?php

namespace App\Domains\Suppliers\Repositories;

use App\Domains\Suppliers\Models\Supply;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliesRepository
{
    /**
     * Tüm tedarik kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $supplies = Supply::with(['supplier'])->orderBy('supply_date', 'desc')->get();
        return response()->json($supplies);
    }

    /**
     * Yeni bir tedarik kaydı oluşturur ve otomatik borç transaction'ı oluşturur.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $supplyDate = Carbon::parse($request->supply_date)->format('Y-m-d');
        
        $supply = Supply::create([
            'supplier_id' => $request->supplier_id,
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
        ]);

        // Otomatik transaction oluştur
        $totalAmount = $request->supplied_product_quantity * $request->supplied_product_price;
        $this->createTransaction($request->supplier_id, $supplyDate, $totalAmount, $supply->id);

        return response()->json($supply->load('supplier'), 201);
    }

    /**
     * Belirtilen tedarik kaydını döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $supply = Supply::with(['supplier'])->findOrFail($id);
        return response()->json($supply);
    }

    /**
     * Belirtilen tedarik kaydını günceller ve ilgili transaction'ı da günceller.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $supply = Supply::findOrFail($id);
        
        $supplyDate = Carbon::parse($request->supply_date)->format('Y-m-d');
        
        $supply->update([
            'supplier_id' => $request->supplier_id,
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
        ]);

        // İlgili transaction'ı güncelle (otomatik oluşturulan transaction'ı)
        $totalAmount = $request->supplied_product_quantity * $request->supplied_product_price;
        SupplierTransaction::where('supplier_id', $supply->supplier_id)
            ->where('description', 'LIKE', '%Tedarik #' . $supply->id . '%')
            ->update([
                'amount' => $totalAmount,
                'date' => $supplyDate,
                'description' => 'Tedarik #' . $supply->id . ' - ' . $supply->supplied_product,
            ]);

        return response()->json($supply->load('supplier'));
    }

    /**
     * Belirtilen tedarik kaydını siler (transactions cascade delete).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $supply = Supply::findOrFail($id);
        
        // İlgili transaction'ı sil
        SupplierTransaction::where('supplier_id', $supply->supplier_id)
            ->where('description', 'LIKE', '%Tedarik #' . $supply->id . '%')
            ->delete();
            
        $supply->delete();

        return response()->json(null, 204);
    }

    /**
     * Belirtilen tedarik ID'lerine göre birden fazla tedarik kaydını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        
        // Her bir supply için transaction'ları sil
        $supplies = Supply::whereIn('id', $request->ids)->get();
        foreach ($supplies as $supply) {
            SupplierTransaction::where('supplier_id', $supply->supplier_id)
                ->where('description', 'LIKE', '%Tedarik #' . $supply->id . '%')
                ->delete();
        }
        
        Supply::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }

    /**
     * Otomatik transaction oluşturma (private method).
     *
     * @param int $supplierId
     * @param string $supplyDate
     * @param float $totalAmount
     * @param int $supplyId
     * @return void
     */
    private function createTransaction($supplierId, $supplyDate, $totalAmount, $supplyId): void
    {
        $supply = Supply::find($supplyId);
        
        SupplierTransaction::create([
            'supplier_id' => $supplierId,
            'type' => 'borç',
            'date' => $supplyDate,
            'amount' => $totalAmount,
            'description' => 'Tedarik #' . $supplyId . ' - ' . ($supply ? $supply->supplied_product : 'Tedarik'),
        ]);
    }
}
