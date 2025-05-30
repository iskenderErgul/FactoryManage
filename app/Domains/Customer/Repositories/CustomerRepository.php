<?php

namespace App\Domains\Customer\Repositories;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Domains\Customer\Interfaces\CustomerRepositoryInterface;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\Transaction;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Services\Whattsapp\WhatsAppService;

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
        $customer->transactions()->delete();
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
        if (empty($transactions)) {
            return response()->json(['message' => 'Hiçbir işlem verisi bulunamadı.'], 400);
        }

        $customerId = $transactions[0]['customer_id'] ?? null;
        if (!$customerId) {
            return response()->json(['message' => 'Müşteri ID si bulunamadı.'], 400);
        }

        $existingTransactions = Transaction::where('customer_id', $customerId)->get();
        $incomingTransactionIds = collect($transactions)->pluck('id')->toArray();
        $existingTransactionIds = $existingTransactions->pluck('id')->toArray();
        $transactionsToDelete = array_diff($existingTransactionIds, $incomingTransactionIds);

        if (!empty($transactionsToDelete)) {
            Transaction::whereIn('id', $transactionsToDelete)->delete();
        }

        foreach ($transactions as $transactionData) {
            $transaction = Transaction::find($transactionData['id']);
            if ($transaction) {
                $transaction->update([
                    'type' => $transactionData['type'],
                    'date' => $transactionData['date'],
                    'amount' => $transactionData['amount'],
                    'description' => $transactionData['description'],
                ]);
            }
        }

        return response()->json(['message' => 'İşlemler başarıyla güncellendi!'], 200);
    }



}
