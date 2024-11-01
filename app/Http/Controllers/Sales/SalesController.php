<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use Illuminate\Http\Request;

class SalesController
{
// Tüm satışları listele
    public function index()
    {
        return Sales::with('customer','products')->get();
    }



    public function store(Request $request)
    {
        $sale = Sales::create($request->all());
        return response()->json($sale, 201);
    }

    public function update(Request $request, $id)
    {
        $sale = Sales::findOrFail($id);
        $sale->update($request->all());
        return response()->json($sale, 200);
    }

    public function destroy($id)
    {
        $sale = Sales::findOrFail($id);
        $sale->delete();
        return response()->json(null, 204);
    }

    public function bulkDelete(Request $request)
    {
        Sales::whereIn('id', $request->ids)->delete();
        return response()->json(null, 204);
    }
}
