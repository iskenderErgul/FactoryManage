<?php

namespace App\Http\Controllers\Suppliers;

use App\Domains\Suppliers\Repositories\SuppliersRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    protected SuppliersRepository $suppliersRepository;

    public function __construct(SuppliersRepository $suppliersRepository)
    {
        $this->suppliersRepository = $suppliersRepository;
    }

    /**
     * Tüm tedarikçileri döner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->suppliersRepository->index($request);
    }

    /**
     * Yeni bir tedarikçi kaydeder.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return $this->suppliersRepository->store($request);
    }

    /**
     * Belirtilen tedarikçiyi döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->suppliersRepository->show($id);
    }

    /**
     * Var olan bir tedarikçi kaydını günceller.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        return $this->suppliersRepository->update($request, $id);
    }

    /**
     * Belirtilen ID'ye sahip tedarikçiyi siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->suppliersRepository->destroy($id);
    }

    /**
     * Seçili tedarikçi kayıtlarını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->suppliersRepository->destroySelected($request);
    }

    /**
     * Manuel transaction ekler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addTransaction(Request $request): JsonResponse
    {
        return $this->suppliersRepository->addTransaction($request);
    }

    /**
     * Toplu transaction güncelleme yapar.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdateTransactions(Request $request): JsonResponse
    {
        return $this->suppliersRepository->bulkUpdateTransactions($request);
    }

    /**
     * Belirtilen tedarikçinin dönemsel borç bilgilerini döner.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getPeriodicDebt($id, Request $request): JsonResponse
    {
        return $this->suppliersRepository->getPeriodicDebt($id, $request);
    }
}
