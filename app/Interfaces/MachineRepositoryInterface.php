<?php

namespace App\Interfaces;

use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface MachineRepositoryInterface
{
    public function index(): JsonResponse;

    public function store(StoreMachineRequest $request): JsonResponse;

    public function show(int $id): JsonResponse;

    public function update(UpdateMachineRequest $request, int $id): JsonResponse;

    public function destroy(int $id): JsonResponse;

    public function destroySelected(Request $request): JsonResponse;
}
