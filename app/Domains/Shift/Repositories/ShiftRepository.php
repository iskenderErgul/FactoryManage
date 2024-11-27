<?php

namespace App\Domains\Shift\Repositories;

use App\Domains\Shift\Helpers\TimeHelper;
use App\Domains\Shift\Interfaces\ShiftRepositoryInterface;
use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftAssignment;
use App\Domains\Shift\Models\ShiftTemplate;
use App\Http\Requests\ShiftAssignments\AddShiftAssignmentRequest;
use App\Http\Requests\ShiftAssignments\UpdateShiftAssignmentRequest;
use App\Http\Requests\ShiftTemplate\AddShiftTemplateRequest;
use App\Http\Requests\ShiftTemplate\UpdateShiftTemplateRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ShiftRepository implements ShiftRepositoryInterface
{
    /**
     * Oluşturulmuş olan Vardiya şanlonlarını getirir.
     */
    public function getShiftTemplates(): JsonResponse
    {
       $shiftTemplates = ShiftTemplate::with('shifts')->get();

       return response()->json($shiftTemplates);
    }

    /**
     * Yeni bir vardiya şablonu ekler.
     */
    public function addShiftTemplates(AddShiftTemplateRequest $request): JsonResponse
    {
        $duration = TimeHelper::calculateDuration($request->start_time, $request->end_time);


        $shiftTemplate = ShiftTemplate::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $duration,
        ]);

        Shift::create([
            'template_id' => $shiftTemplate->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);
        return response()->json($shiftTemplate);
    }

    /**
     * Oluşturulmuş olan bir vardiya şablonunu günceller.
     */
    public function updateShiftTemplates(UpdateShiftTemplateRequest $request,$id): JsonResponse
    {

        $shiftTemplate = $this->getShiftTemplate($id);

        $duration = TimeHelper::calculateDuration($request->start_time, $request->end_time);

        $shiftTemplate->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $duration,
        ]);
        return response()->json($shiftTemplate);
    }

    /**
     *Oluşturulmuş bir vardiya şablonunu siler
     */
    public function destroyShiftTemplates($id): JsonResponse
    {

        $shiftTemplate = $this->getShiftTemplate($id);
        $shiftTemplate->shiftAssignments()->delete();
        $shiftTemplate->shifts()->delete();
        $shiftTemplate->delete();

        return response()->json(['message' => 'Şablon ve ilgili vardiyalar başarıyla silindi.']);
    }

    /**
     * Bir çalışanı belirlenen bir vardiyaya atama işlemini yapar.
     */

    public function addShiftAssignments(AddShiftAssignmentRequest $request): JsonResponse
    {

        $workerId = $request->user_id;
        $shiftTemplateId = $request->shift_id;

        $shiftTemplate = $this->getShiftTemplate($shiftTemplateId);
        $shift = $this->getShiftByTemplate($shiftTemplateId);

        if ($this->hasWorkerAssignedToShift($workerId, $shift->id)) {
            return response()->json(['message' => 'Bu işçi zaten bu vardiyaya atanmış.'], 409);
        }

        if ($this->hasShiftConflict($workerId, $shiftTemplate->start_time, $shiftTemplate->end_time)) {
            return response()->json(['message' => 'Bu saat aralığında işçinin çalıştığı başka bir vardiya var.'], 409);
        }

        $workerShiftAssignment = ShiftAssignment::create([
            'user_id' => $workerId,
            'shift_id' => $shift->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Vardiya başarıyla atandı.',
            'assignment' => $workerShiftAssignment,
        ]);

    }

    /**
     * Atanmış bir vardiyı güncelleme işlemini yapar.
     */
    public function updateShiftAssignments(UpdateShiftAssignmentRequest $request, $id): JsonResponse
    {
        $workerId = $request->user_id;
        $shiftTemplateId = $request->shift_id;

        $shiftAssignment = ShiftAssignment::findOrFail($id);
        if (!$shiftAssignment) {
            return response()->json(['message' => 'Vardiya ataması bulunamadı.'], 404);
        }

        $shiftTemplate = $this->getShiftTemplate($shiftTemplateId);
        $shift = $this->getShiftByTemplate($shiftTemplateId);

        if ($this->hasWorkerAssignedToShift($workerId, $shift->id)) {
            return response()->json(['message' => 'Bu işçi zaten bu vardiyaya atanmış.'], 409);
        }

        if ($this->hasShiftConflict($workerId, $shiftTemplate->start_time, $shiftTemplate->end_time)) {
            return response()->json(['message' => 'Bu saat aralığında işçi  başka bir vardiyaya atanmış.'], 409);
        }

        $shiftAssignment->update([
            'user_id' => $workerId,
            'shift_id' => $shift->id,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Vardiya ataması başarıyla güncellendi.',
            'assignment' => $shiftAssignment,
        ]);
    }

    /**
     * Atama yapılmiş bir vardiyayaı siler.
     */
    public function destroyShiftAssignments($id): JsonResponse
    {
        $workerShiftAssignment = ShiftAssignment::findOrFail($id);
        $workerShiftAssignment->delete();

        return response()->json([
            'message' => 'Atanmış Vardiya başarıyla silindi.',
        ]);
    }

    /**
     * Tüm atanmış vardiyaları getirir.
     */
    public function getShiftAssignments(): JsonResponse
    {
        $shiftAssignments = ShiftAssignment::with('shift','user','shift.template')->get();
        return response()->json($shiftAssignments);
    }

    public function getAllShifts(): JsonResponse
    {
        $shifts =Shift::with('user','template','shiftAssignments.user')->get();
        return response()->json($shifts);

    }

    private function getShiftTemplate($shiftTemplateId): ShiftTemplate
    {
        $shiftTemplate = ShiftTemplate::find($shiftTemplateId);
        if (!$shiftTemplate) {
            abort(404, 'Vardiya şablonu bulunamadı.');
        }
        return $shiftTemplate;
    }

    private function getShiftByTemplate($shiftTemplateId): Shift
    {
        $shift = Shift::where('template_id', $shiftTemplateId)->first();
        if (!$shift) {
            abort(404, 'Vardiya bulunamadı.');
        }
        return $shift;
    }

    private function hasShiftConflict($userId, $startTime, $endTime): bool
    {
        return ShiftAssignment::where('user_id', $userId)
            ->whereHas('shift', function ($query) use ($startTime, $endTime) {
                $query->whereHas('template', function ($q) use ($startTime, $endTime) {
                    $q->where(function ($subQuery) use ($startTime, $endTime) {
                        $subQuery->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhere(function ($q) use ($startTime, $endTime) {
                                $q->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                            });
                    });
                });
            })
            ->exists();
    }

    private function hasWorkerAssignedToShift($userId, $shiftId): bool
    {
        return ShiftAssignment::where('user_id', $userId)
            ->where('shift_id', $shiftId)
            ->exists();
    }




}
