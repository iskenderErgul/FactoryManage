<?php

namespace App\Http\Controllers\Sales;

use App\Http\Repositories\SalesRepository;
use App\Http\Requests\Sales\StoreSalesRequest;
use App\Http\Requests\Sales\UpdateSalesRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;


class SalesController
{
    protected SalesRepository $salesRepository;

    public function __construct(SalesRepository $salesRepository)
    {
        $this->salesRepository = $salesRepository;
    }
    public function index(): Collection|array
    {
        return $this->salesRepository->index();
    }
    public function store(StoreSalesRequest $request): JsonResponse
    {
        return $this->salesRepository->store($request);
    }
    public function update(UpdateSalesRequest $request, $id): JsonResponse
    {
        return $this->salesRepository->update($request, $id);
    }
    public function destroy($id): JsonResponse
    {
        return $this->salesRepository->destroy($id);
    }





}
