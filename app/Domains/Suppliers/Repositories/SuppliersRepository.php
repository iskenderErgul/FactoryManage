<?php

namespace App\Domains\Suppliers\Repositories;

use App\Domains\Suppliers\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliersRepository
{
    public function index(): JsonResponse
    {
        return response()->json(Supplier::all());
    }

    public function store(Request $request): JsonResponse
    {
        $supplyDate = Carbon::parse($request->supply_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');

        $supplier = Supplier::create([
            'suppliers_name' => $request->suppliers_name,
            'suppliers_address' => $request->suppliers_address,
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
        ]);

        return response()->json($supplier, 201);
    }

    public function show($id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        return response()->json($supplier);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $supplyDate = Carbon::parse($request->supply_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');

        $supplier->update([
            'suppliers_name' => $request->suppliers_name,
            'suppliers_address' => $request->suppliers_address,
            'supplied_product' => $request->supplied_product,
            'supplied_product_quantity' => $request->supplied_product_quantity,
            'supplied_product_price' => $request->supplied_product_price,
            'supply_date' => $supplyDate,
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
}
