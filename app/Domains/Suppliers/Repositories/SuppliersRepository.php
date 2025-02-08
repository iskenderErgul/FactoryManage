<?php

namespace App\Domains\Suppliers\Repositories;

use App\Domains\Customer\Models\Transaction;
use App\Domains\Suppliers\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuppliersRepository
{
    public function index(): JsonResponse
    {
        // Tedarikçileri ve ilişkili müşteri bilgilerini alıyoruz
        $suppliers = Supplier::with('customer')->get();
        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {

        $totalAmount = 0;
        // supply_date'yi dönüştürüyoruz
        $supplyDate = Carbon::parse($request->supply_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');
        $customer_id =$request->customer_id['id'];
        // Yeni tedarikçi kaydını yapıyoruz
        $supplier = Supplier::create([
            'customer_id' => $customer_id, // Customer ID'yi alıp kaydediyoruz
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
        ]);
        $supplierId=$supplier->id;
        $totalAmount=$request->supplied_product_quantity*$request->supplied_product_price;

        $this->createTransaction($customer_id,$supplyDate,$totalAmount,$supplierId);

        return response()->json($supplier, 201);
    }

    public function show($id): JsonResponse
    {
        // Tedarikçi bilgilerini ve ilişkili müşteri bilgilerini alıyoruz
        $supplier = Supplier::with('customer')->findOrFail($id);

        return response()->json($supplier);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        // supply_date'yi dönüştürüyoruz
        $supplyDate = Carbon::parse($request->supply_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');

        // Tedarikçi bilgilerini güncelliyoruz
        $supplier->update([
            'customer_id' => $request->customer_id, // Customer ID'yi güncelliyoruz
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
        ]);

        Transaction::where('supplier_id',$supplier->id)->update([
            'amount'=> $supplier->supplied_product_quantity*$supplier->supplied_product_price,
            'date' => $supplyDate,
        ]);

        return response()->json($supplier);
    }

    public function destroy($id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(null, 204);
    }

    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        Supplier::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }

    private function createTransaction($customer_id,$supplyDate,$totalAmount,$supplierId): void
    {

        DB::table('transactions')->insert([
            'customer_id' => $customer_id,
            'supplier_id'=>$supplierId,  // Yeni ilişkiyi burada ekliyoruz
            'type' => 'borç',
            'date' => $supplyDate,
            'amount' => $totalAmount,
            'description' => 'Hammadde Tedarik',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


}
