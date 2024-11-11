<?php

namespace App\Domains\Shift\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ShiftRepositoryInterface
{
    public function getShiftTemplates(): JsonResponse;
    public function addShiftTemplates(Request $request): JsonResponse;
    public function updateShiftTemplates(Request $request,$id): JsonResponse;
    public function destroyShiftTemplates($id): JsonResponse;
    public function addShiftAssignments(Request $request): JsonResponse;
    public function updateShiftAssignments(Request $request,$id): JsonResponse;
    public function destroyShiftAssignments($id): JsonResponse;
    public function getShiftAssignments(): JsonResponse;

}
