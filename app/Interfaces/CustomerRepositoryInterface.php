<?php

namespace App\Interfaces;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CustomerRepositoryInterface
{
    public function index(): JsonResponse;

    public function store(StoreCustomerRequest $request): JsonResponse;

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse;

    public function destroy(int $id): JsonResponse;

    public function deleteSelected(Request $request): JsonResponse;
}
