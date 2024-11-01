<?php

namespace App\Http\Controllers\Sales;

use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController
{
// Tüm satışları listele
    public function index()
    {
        return Sales::with('customer','products')->get();
    }



    public function store(Request $request)
    {
        $products = $request->input('products');

        // Transaction başlat
        DB::beginTransaction();

        try {

            $saleDate = Carbon::createFromFormat('d.m.Y', $request->sale_date)->format('Y-m-d');
            $sale = Sales::create([
                'customer_id' => $request->customer_id,
                'sale_date' => $saleDate,
            ]);

            foreach ($products as $product) {
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    if ($productModel->stock_quantity < $product['quantity']) {

                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Yetersiz stok: ' . $productModel->product_name,
                        ], 400);
                    }


                    $saleProduct = SalesProduct::create([
                        'sales_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                    ]);


                    $productModel->stock_quantity -= $product['quantity'];
                    $productModel->save();
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'sale_id' => $sale->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
