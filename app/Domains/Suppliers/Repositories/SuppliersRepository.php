<?php

namespace App\Domains\Suppliers\Repositories;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliersRepository
{
    /**
     * Tüm tedarikçi kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $suppliers = Supplier::with('transactions')->get();
        return response()->json($suppliers);
    }

    /**
     * Yeni bir tedarikçi kaydı oluşturur.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $supplier = Supplier::create([
            'supplier_name' => $request->supplier_name,
            'supplier_email' => $request->supplier_email,
            'supplier_phone' => $request->supplier_phone,
            'supplier_address' => $request->supplier_address,
            'debt' => $request->debt ?? 0,
        ]);

        return response()->json($supplier, 201);
    }

    /**
     * Belirtilen tedarikçi kaydını döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $supplier = Supplier::with('transactions')->findOrFail($id);
        return response()->json($supplier);
    }

    /**
     * Belirtilen tedarikçi kaydını günceller.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        
        $supplier->update([
            'supplier_name' => $request->supplier_name,
            'supplier_email' => $request->supplier_email,
            'supplier_phone' => $request->supplier_phone,
            'supplier_address' => $request->supplier_address,
            'debt' => $request->debt ?? $supplier->debt,
        ]);

        return response()->json($supplier);
    }

    /**
     * Belirtilen tedarikçi kaydını siler (transactions cascade delete).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->transactions()->delete();
        $supplier->delete();

        return response()->json(null, 204);
    }

    /**
     * Belirtilen tedarikçi ID'lerine göre birden fazla tedarikçi kaydını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        Supplier::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }

    /**
     * Manuel transaction ekleme.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addTransaction(Request $request): JsonResponse
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $transaction = SupplierTransaction::create([
            'supplier_id' => $request->supplier_id,
            'type' => $request->type,
            'description' => $request->description,
            'date' => $date,
            'amount' => $request->amount,
        ]);

        return response()->json($transaction, 201);
    }

    /**
     * Toplu transaction güncelleme.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdateTransactions(Request $request): JsonResponse
    {
        $transactions = $request->all();
        if (empty($transactions)) {
            return response()->json(['message' => 'Hiçbir işlem verisi bulunamadı.'], 400);
        }

        $supplierId = $transactions[0]['supplier_id'] ?? null;
        if (!$supplierId) {
            return response()->json(['message' => 'Tedarikçi ID si bulunamadı.'], 400);
        }

        $existingTransactions = SupplierTransaction::where('supplier_id', $supplierId)->get();
        $incomingTransactionIds = collect($transactions)->pluck('id')->toArray();
        $existingTransactionIds = $existingTransactions->pluck('id')->toArray();
        $transactionsToDelete = array_diff($existingTransactionIds, $incomingTransactionIds);

        if (!empty($transactionsToDelete)) {
            SupplierTransaction::whereIn('id', $transactionsToDelete)->delete();
        }

        foreach ($transactions as $transactionData) {
            $transaction = SupplierTransaction::find($transactionData['id']);
            if ($transaction) {
                // Tarihi uygun formata çevir
                $date = $transactionData['date'];
                if ($date && !empty($date)) {
                    $date = Carbon::parse($date)->format('Y-m-d');
                }
                
                $transaction->update([
                    'type' => $transactionData['type'],
                    'date' => $date,
                    'amount' => $transactionData['amount'],
                    'description' => $transactionData['description'],
                ]);
            }
        }

        return response()->json(['message' => 'İşlemler başarıyla güncellendi!'], 200);
    }


}
