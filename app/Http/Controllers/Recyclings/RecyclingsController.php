<?php

namespace App\Http\Controllers\Recyclings;

use App\Domains\Recyclings\Repositories\RecyclingsRepository;
use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecyclingsController extends Controller
{
    protected  RecyclingsRepository $recyclingRepository;

    public function __construct(RecyclingsRepository $recyclingRepository)
    {
        $this->recyclingRepository = $recyclingRepository;
    }

    public function index(): JsonResponse
    {
        return $this->recyclingRepository->index();
    }


    public function store(Request $request): JsonResponse
    {
        return $this->recyclingRepository->store($request);
    }


    public function show($id): JsonResponse
    {
        return $this->recyclingRepository->show($id);
    }


    public function update(Request $request, $id): JsonResponse
    {
        return $this->recyclingRepository->update($request, $id);
    }


    public function destroy($id): JsonResponse
    {
        return $this->recyclingRepository->destroy($id);
    }

    public function destroySelected(Request $request): JsonResponse
    {
        return $this->recyclingRepository->destroySelected($request);
    }
}
