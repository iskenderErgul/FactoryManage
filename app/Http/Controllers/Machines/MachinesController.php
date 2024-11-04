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

    /**
     * Tüm makineleri döner.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->machineRepository->index();
    }

    /**
     * Yeni bir makine kaydeder.
     *
     * @param StoreMachineRequest $request
     * @return JsonResponse
     */
    public function store(StoreMachineRequest $request): JsonResponse
    {
        return $this->machineRepository->store($request);
    }

    /**
     * Belirtilen ID'ye sahip makineyi döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->machineRepository->show($id);
    }


    /**
     * Var olan bir makine kaydını günceller.
     *
     * @param UpdateMachineRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMachineRequest $request, $id): JsonResponse
    {
        return $this->machineRepository->update($request, $id);
    }
    /**
     * Belirtilen ID'ye sahip makinayı siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->machineRepository->destroy($id);
    }

    /**
     * Seçili makineleri siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->machineRepository->destroySelected($request);
    }

}
