<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function index(): JsonResponse
    {
        $customer = Customer::all();
        return response()->json($customer);
    }
    public function store(Request $request): JsonResponse
    {
        $customer = Customer::create($request->all());

        return response()->json($customer);
    }
    public function update(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $customer->update($request->all());

        return response()->json($customer);
    }
    public function destroy($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(null);
    }
    public function deleteSelected(Request $request): JsonResponse
    {
        Customer::destroy($request->ids);

        return response()->json(null);
    }
}
