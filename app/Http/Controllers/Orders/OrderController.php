<?php

namespace App\Http\Controllers\Orders;

use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderProduct;
use App\Domains\Orders\Repositories\OrderRepository;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    public function index(): JsonResponse
    {
        return $this->orderRepository->index();
    }

    public function store(Request $request): JsonResponse
    {
        return $this->orderRepository->store($request);
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->orderRepository->update($request, $id);
    }

    public function destroy($id): JsonResponse
    {

        return $this->orderRepository->destroy($id);
    }

}
