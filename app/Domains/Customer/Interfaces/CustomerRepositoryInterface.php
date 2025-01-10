<?php

namespace App\Domains\Customer\Interfaces;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CustomerRepositoryInterface
{
    /**
     * Müşteri verilerini listeler.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse;

    /**
     * Yeni bir müşteri kaydı oluşturur.
     *
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request): JsonResponse;

    /**
     * Mevcut bir müşteri kaydını günceller.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id Müşteri kaydının benzersiz kimliği
     * @return JsonResponse
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse;

    /**
     * Belirli bir müşteri kaydını siler.
     *
     * @param int $id Müşteri kaydının benzersiz kimliği
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;

    /**
     * Seçili müşteri kayıtlarını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteSelected(Request $request): JsonResponse;

    public function addTransaction(Request $request): JsonResponse;

    public function bulkUpdateTransactions(Request $request): JsonResponse;
}
