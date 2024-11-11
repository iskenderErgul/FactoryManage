<?php

namespace App\Http\Controllers\ShiftAssignment;

use App\Domains\Shift\Repositories\ShiftRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftAssignments\AddShiftAssignmentRequest;
use App\Http\Requests\ShiftAssignments\UpdateShiftAssignmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    protected ShiftRepository $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        return $this->shiftRepository = $shiftRepository;
    }

    /**
     * Tüm atanmış vardiyaları getirir.
     */

    public function getShiftAssignments(): JsonResponse
    {
        return $this->shiftRepository->getShiftAssignments();
    }

    /**
     * Bir çalışanı belirlenen bir vardiyaya atama işlemini yapar.
     */
    public function addShiftAssignments(AddShiftAssignmentRequest $request): JsonResponse
    {

        return $this->shiftRepository->addShiftAssignments($request);
    }

    /**
     * Atanmış bir vardiyı güncelleme işlemini yapar.
     */

    public function updateShiftAssignments(UpdateShiftAssignmentRequest $request,$id): JsonResponse
    {

        return $this->shiftRepository->updateShiftAssignments($request,$id);
    }

    /**
     * Atama yapılmiş bir vardiyayaı siler.
     */

    public function destroyShiftAssignments($id): JsonResponse
    {
        return $this->shiftRepository->destroyShiftAssignments($id);
    }
}
