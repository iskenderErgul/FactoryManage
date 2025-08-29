<?php

namespace App\Http\Controllers\Customer;

use App\Domains\Customer\Models\Transaction;
use App\Domains\Customer\Repositories\CustomerRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
     $this->customerRepository = $customerRepository;
    }

    /**
     * Tüm müşterileri döner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->customerRepository->index($request);
    }

    /**
     * Yeni bir müşteri kaydeder.
     *
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        return $this->customerRepository->store($request);
    }

    /**
     * Var olan bir müşteri kaydını günceller.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, $id): JsonResponse
    {
        return $this->customerRepository->update($request, $id);
    }

    /**
     * Belirtilen ID'ye sahip müşteriyi siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
      return $this->customerRepository->destroy($id);
    }

    /**
     * Seçili müşteri kayıtlarını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSelected(Request $request): JsonResponse
    {
        return $this->customerRepository->deleteSelected($request);
    }

    public function addTransaction(Request $request): JsonResponse
    {
        return $this->customerRepository->addTransaction($request);
    }
    public function bulkUpdateTransactions(Request $request): JsonResponse
    {
        return $this->customerRepository->bulkUpdateTransactions($request);
    }

    /**
     * Belirtilen müşterinin dönemsel borç bilgilerini döner.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getPeriodicDebt($id, Request $request): JsonResponse
    {
        return $this->customerRepository->getPeriodicDebt($id, $request);
    }
}
