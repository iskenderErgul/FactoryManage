<?php

namespace App\Domains\Customer\Repositories;

use App\Domains\Customer\Interfaces\CustomerRepositoryInterface;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\Transaction;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Carbon\Carbon;
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
        $customer = Customer::with('transactions')->get();
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

    public function addTransaction(Request $request): JsonResponse
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $transaction = Transaction::create([
            'customer_id' => $request->customer_id,
            'type' => $request->type,
            'description' => $request->description,
            'date' => $date,
            'amount' => $request->amount,
        ]);

        return response()->json($transaction, 201);
    }
    public function bulkUpdateTransactions(Request $request): JsonResponse
    {

        $transactions = $request->all();

        foreach ($transactions as $transactionData) {
            $transaction = Transaction::find($transactionData['id']);
            if (!$transaction) {
                return response()->json(['message' => 'Bir işlem bulunamadı: ' . $transactionData['id']], 404);
            }

            $transaction->update([
                'type' => $transactionData['type'],
                'date' => $transactionData['date'],
                'amount' => $transactionData['amount'],
                'description' => $transactionData['description'],
            ]);
        }

        return response()->json(['message' => 'İşlemler başarıyla güncellendi!'], 200);
    }



}
