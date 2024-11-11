<?php

namespace App\Domains\Shift\Repositories;

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
        $startTime = Carbon::createFromFormat('H:i:s', $request->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $request->end_time);



        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $duration = $startTime->diffInMinutes($endTime);
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
        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json(['message' => 'Şablon bulunamadı'], 404);
        }
        $startTime = Carbon::createFromFormat('H:i:s', $request->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $request->end_time);
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }
        $duration = $startTime->diffInMinutes($endTime);

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

        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json(['message' => 'Şablon bulunamadı'], 404);
        }
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

        $shiftTemplate = ShiftTemplate::find($shiftTemplateId);

        if (!$shiftTemplate) {
            return response()->json(['message' => 'Vardiya şablonu bulunamadı.'], 404);
        }

        $shift = Shift::where('template_id', $shiftTemplateId)
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'Vardiya bulunamadı.'], 404);
        }


        $conflictingAssignment = ShiftAssignment::where('user_id', $workerId)
            ->where('shift_id', $shift->id)
            ->exists();

        if ($conflictingAssignment) {
            return response()->json(['message' => 'Bu işçi zaten bu vardiyaya atanmış.'], 409);
        }

        $startTime = $shiftTemplate->start_time;
        $endTime = $shiftTemplate->end_time;

        // Aynı işçinin vardiyaları arasında saat çakışması olup olmadığını kontrol et
        $existingAssignments = ShiftAssignment::where('user_id', $workerId)
            ->whereHas('shift', function ($query) use ($startTime, $endTime) {
                $query->whereHas('template', function ($q) use ($startTime, $endTime) {
                    // Burada start_time ve end_time shift_templates tablosundan alınacak
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

        if ($existingAssignments) {
            return response()->json(['message' => 'Bu saat aralığında işçinin çalıştıgı başka bir vardiya var.'], 409);
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
        // Gelen request verilerini al
        $workerId = $request->user_id;
        $shiftTemplateId = $request->shift_id;

        // ShiftAssignment'ı bul
        $shiftAssignment = ShiftAssignment::find($id);

        if (!$shiftAssignment) {
            return response()->json(['message' => 'Vardiya ataması bulunamadı.'], 404);
        }

        // 1. İşçinin değişip değişmediğini kontrol et
        $workerChanged = $shiftAssignment->user_id !== $workerId;

        // 2. Vardiyanın değişip değişmediğini kontrol et
        $shiftChanged = $shiftAssignment->shift_id !== $shiftTemplateId;

        // Eğer işçi veya vardiya değişmişse, yeni atama yapılmadan önce kontrol edilmesi gereken şartlar var
        if ($workerChanged || $shiftChanged) {


            $shiftTemplate = ShiftTemplate::find($shiftTemplateId);

            if (!$shiftTemplate) {
                return response()->json(['message' => 'Vardiya şablonu bulunamadı.'], 404);
            }


            $shift = Shift::where('template_id', $shiftTemplateId)
                ->first();

            if (!$shift) {
                return response()->json(['message' => 'Belirtilen tarihte vardiya bulunamadı.'], 404);
            }

            // 3. Koşulları kontrol et (işçi ve vardiya değişiminden sonra çakışma kontrolü)
            $existingAssignments = ShiftAssignment::where('user_id', $workerId)
                ->where('shift_id', $shift->id)
                ->exists();

            if ($existingAssignments) {
                return response()->json(['message' => 'Bu işçi zaten bu vardiyaya atanmış.'], 409);
            }

            // 4. Vardiya zamanlarının çakışıp çakışmadığını kontrol et
            $startTime = $shiftTemplate->start_time;
            $endTime = $shiftTemplate->end_time;

            $conflictingAssignment = ShiftAssignment::where('user_id', $workerId)
                ->whereHas('shift', function ($query) use ($startTime, $endTime) {
                    $query->whereHas('template', function ($templateQuery) use ($startTime, $endTime) {
                        $templateQuery->where(function ($subQuery) use ($startTime, $endTime) {
                            // Vardiya başlangıç ve bitişi çakışması
                            $subQuery->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                    });
                })
                ->exists();

            if ($conflictingAssignment) {
                return response()->json(['message' => 'Bu saat aralığında başka bir vardiya var.'], 409);
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

        return response()->json(['message' => 'Vardiya ataması zaten mevcut, herhangi bir değişiklik yapılmadı.']);
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


}
