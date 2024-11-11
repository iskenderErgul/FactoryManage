<?php

namespace App\Domains\Shift\Interfaces;

use App\Http\Requests\ShiftAssignments\AddShiftAssignmentRequest;
use App\Http\Requests\ShiftAssignments\UpdateShiftAssignmentRequest;
use App\Http\Requests\ShiftTemplate\AddShiftTemplateRequest;
use App\Http\Requests\ShiftTemplate\UpdateShiftTemplateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ShiftRepositoryInterface
{
    /**
     * Oluşturulmuş olan Vardiya şanlonlarını getirir.
    */
    public function getShiftTemplates(): JsonResponse;

    /**
     * Yeni bir vardiya şablonu ekler.
    */
    public function addShiftTemplates(AddShiftTemplateRequest $request): JsonResponse;

    /**
     * Oluşturulmuş olan bir vardiya şablonunu günceller.
    */
    public function updateShiftTemplates(UpdateShiftTemplateRequest $request,$id): JsonResponse;

    /**
     *Oluşturulmuş bir vardiya şablonunu siler
    */
    public function destroyShiftTemplates($id): JsonResponse;

    /**
     * Bir çalışanı belirlenen bir vardiyaya atama işlemini yapar.
    */
    public function addShiftAssignments(AddShiftAssignmentRequest $request): JsonResponse;

    /**
     * Atanmış bir vardiyı güncelleme işlemini yapar.
     */
    public function updateShiftAssignments(UpdateShiftAssignmentRequest $request,$id): JsonResponse;

    /**
     * Atama yapılmiş bir vardiyayaı siler.
     */
    public function destroyShiftAssignments($id): JsonResponse;

    /**
     * Tüm atanmış vardiyaları getirir.
     */
    public function getShiftAssignments(): JsonResponse;

}
