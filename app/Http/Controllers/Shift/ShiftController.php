<?php

namespace App\Http\Controllers\Shift;

use App\Domains\Shift\Repositories\ShiftRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftTemplate\AddShiftTemplateRequest;
use App\Http\Requests\ShiftTemplate\UpdateShiftTemplateRequest;
use Illuminate\Http\JsonResponse;


class ShiftController extends Controller
{
    protected ShiftRepository $shiftRepository;


    public function __construct(ShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }
    public function getShiftTemplates(): JsonResponse
    {
        return $this->shiftRepository->getShiftTemplates();
    }
    public function addShiftTemplates(AddShiftTemplateRequest $request): JsonResponse
    {
        return $this->shiftRepository->addShiftTemplates($request);
    }
    public function updateShiftTemplates(UpdateShiftTemplateRequest $request,$id): JsonResponse
    {

        return $this->shiftRepository->updateShiftTemplates($request,$id);
    }
    public function destroyShiftTemplates($id): JsonResponse
    {
        return $this->shiftRepository->destroyShiftTemplates($id);
    }

    public function getAllShifts(): JsonResponse
    {
        return $this->shiftRepository->getAllShifts();
    }
}
