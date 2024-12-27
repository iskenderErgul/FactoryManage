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

    public function index(): JsonResponse
    {
        return $this->suppliersRepository->index();
    }

    public function store(Request $request): JsonResponse
    {
        return $this->suppliersRepository->store($request);
    }

    public function show($id): JsonResponse
    {
        return $this->suppliersRepository->show($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->suppliersRepository->update($request, $id);
    }

    public function destroy($id): JsonResponse
    {
        return $this->suppliersRepository->destroy($id);
    }

    public function destroySelected(Request $request): JsonResponse
    {
        return $this->suppliersRepository->destroySelected($request);
    }
}
