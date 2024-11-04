<?php

namespace App\Domains\Customer\Repositories;

use App\Domains\Customer\Interfaces\CustomerRepositoryInterface;
use App\Domains\Customer\Models\Customer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Tüm müşteri kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $customer = Customer::all();
        return response()->json($customer);
    }

    /**
     * Yeni bir müşteri kaydı oluşturur.
     *
     * @param StoreCustomerRequest $request  Müşteri bilgilerini içeren doğrulanmış istek
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->all());

        return response()->json($customer);
    }

    /**
     * Belirtilen müşteri kaydını günceller.
     *
     * @param UpdateCustomerRequest $request  Güncellenmiş müşteri bilgilerini içeren doğrulanmış istek
     * @param int $id  Güncellenecek müşteri kaydının ID'si
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $customer->update($request->all());

        return response()->json($customer);
    }
    /**
     * Belirtilen müşteri kaydını siler.
     *
     * @param int $id  Silinecek müşteri kaydının ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(null);
    }

    /**
     * Belirtilen müşteri ID'lerine göre birden fazla müşteri kaydını siler.
     *
     * @param Request $request  Silinecek müşteri ID'lerini içeren istek
     * @return JsonResponse
     */
    public function deleteSelected(Request $request): JsonResponse
    {
        Customer::destroy($request->ids);

        return response()->json(null);
    }
}
