<?php

namespace App\Http\Controllers\ShiftAssignment;

use App\Domains\Shift\Repositories\ShiftRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    protected ShiftRepository $shiftRepository;

    public function __construct(ShiftRepository $shiftRepository)
    {
        return $this->shiftRepository = $shiftRepository;
    }

    public function getShiftAssignments(): JsonResponse
    {
        return $this->shiftRepository->getShiftAssignments();
    }

    public function addShiftAssignments(Request $request): JsonResponse
    {
        return $this->shiftRepository->addShiftAssignments($request);
    }

    public function updateShiftAssignments(Request $request,$id): JsonResponse
    {
        return $this->shiftRepository->updateShiftAssignments($request,$id);
    }

    public function destroyShiftAssignments($id): JsonResponse
    {
        return $this->shiftRepository->destroyShiftAssignments($id);
    }
}
