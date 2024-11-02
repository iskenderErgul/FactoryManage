<?php

namespace App\Http\Controllers\Sales;

use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesProduct;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController
{
// Tüm satışları listele
    public function index()
    {
        return Sales::with('customer','products')->get();
    }



    public function store(Request $request): JsonResponse
    {
        $products = $request->input('products');


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
        //Gelen veriler

        /*
        array:3 [ // app\Http\Controllers\Sales\SalesController.php:75
  "customer_id" => 103
  "sale_date" => "2024-11-01"
  "products" => array:3 [
    0 => array:11 [
      "id" => 4
      "product_name" => "Baskılı Poşet"
      "product_type" => "Küçük"
      "product_photo" => "url_to_photo_4"
      "description" => "Özel baskılı poşetler, markanız için harika bir seçimdir."
      "production_cost" => "0.75"
      "stock_quantity" => 500
      "created_at" => "2024-10-31T18:10:38.000000Z"
      "updated_at" => "2024-11-01T19:02:46.000000Z"
      "pivot" => array:6 [
        "sales_id" => 3
        "product_id" => 4
        "quantity" => 4
        "price" => "3.00"
        "created_at" => "2024-10-31T18:25:30.000000Z"
        "updated_at" => "2024-11-01T19:05:00.000000Z"
      ]
      "total_price" => 12
    ]
    1 => array:11 [
      "id" => 6
      "product_name" => "Şeffaf Poşet"
      "product_type" => "Küçük"
      "product_photo" => "url_to_photo_6"
      "description" => "Ürünlerinizi sergilemek için ideal şeffaf poşetler."
      "production_cost" => "0.40"
      "stock_quantity" => 1200
      "created_at" => "2024-10-31T18:10:38.000000Z"
      "updated_at" => "2024-11-01T19:02:46.000000Z"
      "pivot" => array:6 [
        "sales_id" => 3
        "product_id" => 6
        "quantity" => 2
        "price" => "0.80"
        "created_at" => "2024-10-31T18:25:30.000000Z"
        "updated_at" => "2024-11-01T19:05:00.000000Z"
      ]
      "total_price" => 1.6
    ]
    2 => array:11 [
      "id" => 1
      "product_name" => "Renkli Poşet"
      "product_type" => "Küçük"
      "product_photo" => "url_to_photo_1"
      "description" => "Renkli plastik poşetler, alışveriş için idealdir."
      "production_cost" => "0.50"
      "stock_quantity" => 1000
      "created_at" => "2024-10-31T18:10:38.000000Z"
      "updated_at" => "2024-11-01T19:02:46.000000Z"
      "pivot" => array:2 [
        "quantity" => 1
        "price" => 1
      ]
      "total_price" => 1
    ]
  ]
]
"3" // app\Http\Controllers\Sales\SalesController.php:75
        */


        dd($request->all(),$id);
        $sale = Sales::findOrFail($id);
        $sale->update($request->all());
        return response()->json($sale, 200);
    }

    public function destroy($id): JsonResponse
    {

        $sale = Sales::findOrFail($id);
        $saleProducts = DB::table('sales_products')->where('sales_id', $id)->get();

        foreach ($saleProducts as $saleProduct) {
            DB::table('products')
                ->where('id', $saleProduct->product_id)
                ->increment('stock_quantity', $saleProduct->quantity);
        }

        DB::table('sales_products')->where('sales_id', $id)->delete();

        $sale->delete();
        return response()->json(null, 204);
    }


}
