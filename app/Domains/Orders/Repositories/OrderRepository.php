<?php

namespace App\Domains\Orders\Repositories;

use App\Domains\Orders\Interfaces\OrderRepositoryInterface;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderProduct;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{

    public function index(): JsonResponse
    {
        $orders =Order::with('products','customer')->get();
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {

        $products = $request->products;
        $status = $request->status['label'];
        DB::beginTransaction();
        try {

            $orderDate = Carbon::parse($request->order_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_date' => $orderDate,
                'status' => $status ?? 'Sipariş Alındı',
                'notes' => $request->notes ?? null,
            ]);

            foreach ($products as $product) {

                $quantity = $product['pivot']['quantity'];
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product['id'],
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'order_id' => $order->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id): JsonResponse
    {

        $status = $request->status['label'] ?? 'sipariş alındı';
        $customerId = $request->customer_id;
        $orderDate = Carbon::parse($request->order_date)->setTimezone('Asia/Istanbul')->format('Y-m-d H:i:s');
        $products = $request->products;


        $order = Order::findOrFail($id);



        $order->update([
            'customer_id' => $customerId,
            'order_date' => $orderDate,
            'status' => $status
        ]);

        $existingProductIds = $order->products->pluck('id')->toArray();

        foreach ($products as $productData) {
            $productId = $productData['id'];
            $quantity = $productData['pivot']['quantity'];

            if (in_array($productId, $existingProductIds)) {

                $previousQuantity = $order->products()->where('product_id', $productId)->first()->pivot->quantity;
                $order->products()->updateExistingPivot($productId, [
                    'quantity' => $quantity,
                    'updated_at' => now(),
                ]);
            } else {

                $order->products()->attach($productId, [
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $removedProductIds = array_diff($existingProductIds, array_column($products, 'id'));
        foreach ($removedProductIds as $removedProductId) {

            $order->products()->detach($removedProductId);
        }

        return response()->json($order->load('products'), 200);
    }

    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->products()->detach();
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Sipariş başarıyla silindi'], 200);
    }
}
