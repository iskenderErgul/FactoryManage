<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Repositories\CustomerRepository;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
     $this->customerRepository = $customerRepository;
    }
    public function index(): JsonResponse
    {
        return $this->customerRepository->index();
    }
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        return $this->customerRepository->store($request);
    }
    public function update(UpdateCustomerRequest $request, $id): JsonResponse
    {
        return $this->customerRepository->update($request, $id);
    }
    public function destroy($id): JsonResponse
    {
      return $this->customerRepository->destroy($id);
    }
    public function deleteSelected(Request $request): JsonResponse
    {
        return $this->customerRepository->deleteSelected($request);
    }
}
