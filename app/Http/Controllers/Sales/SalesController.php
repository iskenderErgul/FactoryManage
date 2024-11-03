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
    public function index(): Collection|array
    {
        return $this->salesRepository->index();
    }
    public function store(SalesRequest $request): JsonResponse
    {
        return $this->salesRepository->store(SalesDTO::buildFromRequest($request));
    }
    public function update(SalesRequest $request, $id): JsonResponse
    {
        return $this->salesRepository->update(SalesDTO::buildFromRequest($request), $id);
    }
    public function destroy($id): JsonResponse
    {
        return $this->salesRepository->destroy($id);
    }





}
