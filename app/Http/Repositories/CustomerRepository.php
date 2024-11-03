<?php

namespace App\Http\Repositories;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Interfaces\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function index(): JsonResponse
    {
        $customer = Customer::all();
        return response()->json($customer);
    }
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->all());

        return response()->json($customer);
    }
    public function update(UpdateCustomerRequest $request, $id): JsonResponse
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
