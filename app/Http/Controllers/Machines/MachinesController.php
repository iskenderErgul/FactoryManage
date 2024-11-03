<?php

namespace App\Http\Controllers\Machines;

use App\Domains\Machines\Repositories\MachineRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachinesController extends Controller
{
    protected MachineRepository $machineRepository;


    public function __construct(MachineRepository $machineRepository)
    {
        $this->machineRepository = $machineRepository;
    }
    public function index(): JsonResponse
    {
        return $this->machineRepository->index();
    }
    public function store(StoreMachineRequest $request): JsonResponse
    {
        return $this->machineRepository->store($request);
    }
    public function show($id): JsonResponse
    {
        return $this->machineRepository->show($id);
    }
    public function update(UpdateMachineRequest $request, $id): JsonResponse
    {
        return $this->machineRepository->update($request, $id);
    }
    public function destroy($id): JsonResponse
    {
        return $this->machineRepository->destroy($id);
    }
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->machineRepository->destroySelected($request);
    }

}
