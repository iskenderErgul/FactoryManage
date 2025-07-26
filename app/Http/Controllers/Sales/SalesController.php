<?php

namespace App\Http\Controllers\Sales;

use App\Domains\Sales\Repositories\SalesRepository;
use App\DTOs\Sales\SalesDTO;
use App\Http\Requests\Sales\SalesRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;


class SalesController
{
    protected SalesRepository $salesRepository;

    public function __construct(SalesRepository $salesRepository)
    {
        $this->salesRepository = $salesRepository;
    }

    /**
     * Tüm satışları döner.
     *
     * @return Collection|array
     */
    public function index(): Collection|array
    {
        return $this->salesRepository->index();
    }

    /**
     * Yeni bir satış kaydeder.
     *
     * @param SalesRequest $request
     * @return JsonResponse
     */
    public function store(SalesRequest $request): JsonResponse
    {
        //Service içerisine yönlendirilecek.
        return $this->salesRepository->store(SalesDTO::buildFromRequest($request));
    }

    /**
     * Belirtilen satış kaydını günceller.
     *
     * @param SalesRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SalesRequest $request, $id): JsonResponse
    {
        return $this->salesRepository->update(SalesDTO::buildFromRequest($request), $id);
    }

    /**
     * Belirtilen satış kaydını siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->salesRepository->destroy($id);
    }

    /**
     * Tüm satış loglarını döner.
     *
     * @return JsonResponse
     */
    public function getAllSalesLogs(): JsonResponse
    {
        return $this->salesRepository->getAllSalesLogs();
    }






}
