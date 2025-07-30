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
     * Oluşturulmuş olan Vardiya şablonlarını getirir.
     */
    public function getShiftTemplates(): JsonResponse
    {
        $shiftTemplates = ShiftTemplate::with('shifts')->orderBy('id')->get();
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

        // Otomatik olarak bu hafta için shift oluştur
        $this->createShiftsForCurrentWeek($shiftTemplate);

        return response()->json($shiftTemplate);
    }

    /**
     * Güncellenmiş getAllShifts - tarih aralığı ile
     */
    public function getAllShifts($startDate = null, $endDate = null): JsonResponse
    {
        $query = Shift::with(['user', 'template', 'shiftAssignments.user']);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif (!$startDate && !$endDate) {
            // Varsayılan: 4 haftalık görünüm (2 hafta geri, 2 hafta ileri)
            $today = Carbon::now();
            $startDate = $today->copy()->subWeeks(2)->startOfWeek()->format('Y-m-d');
            $endDate = $today->copy()->addWeeks(2)->endOfWeek()->format('Y-m-d');
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $shifts = $query->orderBy('date')->orderBy('template_id')->get();

        return response()->json([
            'shifts' => $shifts,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    /**
     * Belirli tarih aralığı için shift'leri getirir
     */
    public function getShiftsByDateRange($startDate, $endDate): JsonResponse
    {
        return $this->getAllShifts($startDate, $endDate);
    }

    /**
     * 4 haftalık vardiya görünümü için
     */
    public function getFourWeekView($centerDate = null): JsonResponse
    {
        $center = $centerDate ? Carbon::parse($centerDate) : Carbon::now();
        $startDate = $center->copy()->subWeeks(2)->startOfWeek()->format('Y-m-d');
        $endDate = $center->copy()->addWeeks(2)->endOfWeek()->format('Y-m-d');

        $shifts = Shift::with(['template', 'shiftAssignments.user'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('template_id')
            ->get();

        // Haftalık gruplandırma
        $weeklyData = [];
        $current = Carbon::parse($startDate);

        while ($current <= Carbon::parse($endDate)) {
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd = $current->copy()->endOfWeek();
            $weekKey = $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d');

            $weekShifts = $shifts->filter(function($shift) use ($weekStart, $weekEnd) {
                $shiftDate = Carbon::parse($shift->date);
                return $shiftDate->between($weekStart, $weekEnd);
            });

            $weeklyData[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'week_label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
                'shifts' => $weekShifts->values(),
                'is_current_week' => $weekStart->isCurrentWeek()
            ];

            $current->addWeek();
        }

        return response()->json([
            'weekly_data' => $weeklyData,
            'center_date' => $center->format('Y-m-d'),
            'navigation' => [
                'prev_period' => $center->copy()->subWeeks(4)->format('Y-m-d'),
                'next_period' => $center->copy()->addWeeks(4)->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Vardiya ataması yapar (güncellenmiş)
     */
    /**
     * Vardiya ataması yapar (template ID ile)
     */
    public function addShiftAssignments(AddShiftAssignmentRequest $request): JsonResponse
    {
        $workerId = $request->user_id;
        $templateId = $request->shift_id; // Frontend'den template ID gelecek

        // Template'i kontrol et
        $template = ShiftTemplate::findOrFail($templateId);

        // Bu hafta için bu template'e ait shift'leri bul veya oluştur
        $today = Carbon::now();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        // Bu haftanın shift'lerini oluştur (yoksa)
        $this->ensureShiftsExistForWeek($weekStart);

        // Bu haftanın tüm günleri için atama yap
        $assignedShifts = [];
        $conflictDays = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');

            // Bu tarih için template'e ait shift'i bul
            $shift = Shift::where('template_id', $templateId)
                ->where('date', $dateStr)
                ->first();

            if (!$shift) {
                $shift = Shift::create([
                    'template_id' => $templateId,
                    'date' => $dateStr,
                ]);
            }

            // Bu kullanıcının bu tarihte başka ataması var mı?
            $existingAssignment = ShiftAssignment::where('user_id', $workerId)
                ->whereHas('shift', function($query) use ($dateStr) {
                    $query->where('date', $dateStr);
                })
                ->first();

            if ($existingAssignment) {
                // Mevcut atamayı güncelle
                $existingAssignment->update(['shift_id' => $shift->id]);
                $assignedShifts[] = $existingAssignment;
            } else {
                // Yeni atama oluştur
                $assignment = ShiftAssignment::create([
                    'user_id' => $workerId,
                    'shift_id' => $shift->id,
                ]);
                $assignedShifts[] = $assignment;
            }
        }

        return response()->json([
            'message' => 'Kullanıcı bu hafta ' . $template->name . ' vardiyasına atandı.',
            'assignment_count' => count($assignedShifts),
            'template' => $template,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d')
        ]);
    }

    /**
     * Mevcut vardiya atamalarını dinamik olarak sıralı şekilde değiştir
     * Örnek: 4 vardiya varsa: 1->2->3->4->1 şeklinde döner
     */
    public function rotateCurrentAssignments(): JsonResponse
    {
        try {
            $today = Carbon::now();
            $weekStart = $today->copy()->startOfWeek();
            $weekEnd = $today->copy()->endOfWeek();

            // Bu haftanın tüm atamalarını al
            $currentAssignments = ShiftAssignment::whereHas('shift', function($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d')
                ]);
            })->with(['shift.template', 'user'])->get();

            if ($currentAssignments->isEmpty()) {
                return response()->json([
                    'message' => 'Bu hafta için atama bulunamadı.',
                    'rotated_count' => 0
                ]);
            }

            // Vardiya template'lerini al (dinamik sayıda olabilir)
            $templates = ShiftTemplate::orderBy('id')->get();

            if ($templates->count() < 2) {
                return response()->json([
                    'message' => 'Rotasyon için en az 2 vardiya template\'i olmalı.',
                    'current_templates' => $templates->count()
                ], 400);
            }

            $rotatedCount = 0;
            $rotationLog = [];

            // Her kullanıcının bu haftki atamalarını sıralı şekilde değiştir
            $userAssignments = $currentAssignments->groupBy('user_id');

            foreach ($userAssignments as $userId => $assignments) {
                $user = $assignments->first()->user;
                $currentTemplate = $assignments->first()->shift->template;

                // Mevcut template'in index'ini bul
                $currentTemplateIndex = $templates->search(function($template) use ($currentTemplate) {
                    return $template->id === $currentTemplate->id;
                });

                // Bir sonraki template'i belirle (sıralı rotasyon)
                $nextTemplateIndex = ($currentTemplateIndex + 1) % $templates->count();
                $newTemplate = $templates[$nextTemplateIndex];

                // Bu haftanın her günü için yeni vardiyaya ata
                foreach ($assignments as $assignment) {
                    $currentDate = $assignment->shift->date;

                    // Yeni template'e ait shift'i bul
                    $newShift = Shift::where('template_id', $newTemplate->id)
                        ->where('date', $currentDate)
                        ->first();

                    if (!$newShift) {
                        // Eğer yeni shift yoksa oluştur
                        $newShift = Shift::create([
                            'template_id' => $newTemplate->id,
                            'date' => $currentDate,
                        ]);
                    }

                    // Atamayı güncelle
                    $assignment->update(['shift_id' => $newShift->id]);
                    $rotatedCount++;
                }

                $rotationLog[] = [
                    'user' => $user->name,
                    'from' => $currentTemplate->name,
                    'to' => $newTemplate->name,
                    'from_index' => $currentTemplateIndex + 1,
                    'to_index' => $nextTemplateIndex + 1
                ];
            }

            return response()->json([
                'message' => 'Vardiya rotasyonu başarıyla tamamlandı.',
                'rotated_count' => $rotatedCount,
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'rotation_details' => $rotationLog,
                'total_templates' => $templates->count(),
                'template_names' => $templates->pluck('name')->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rotasyon sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... diğer metodlar (güncellenmiş çakışma kontrolü ile)

    private function hasShiftConflictByDate($userId, $date, $startTime, $endTime): bool
    {
        return ShiftAssignment::where('user_id', $userId)
            ->whereHas('shift', function ($query) use ($date, $startTime, $endTime) {
                $query->where('date', $date)
                    ->whereHas('template', function ($q) use ($startTime, $endTime) {
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

    private function createShiftsForCurrentWeek($template)
    {
        $weekStart = Carbon::now()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);

            $existingShift = Shift::where('template_id', $template->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if (!$existingShift) {
                Shift::create([
                    'template_id' => $template->id,
                    'date' => $date->format('Y-m-d'),
                ]);
            }
        }
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
     * Atanmış bir vardiyı güncelleme işlemini yapar.
     */
    public function updateShiftAssignments(UpdateShiftAssignmentRequest $request, $id): JsonResponse
    {
        $workerId = $request->user_id;
        $shiftId = $request->shift_id;

        $shiftAssignment = ShiftAssignment::findOrFail($id);
        $shift = Shift::with('template')->findOrFail($shiftId);

        if ($this->hasWorkerAssignedToShift($workerId, $shift->id)) {
            return response()->json(['message' => 'Bu işçi zaten bu vardiyaya atanmış.'], 409);
        }

        if ($this->hasShiftConflictByDate($workerId, $shift->date, $shift->template->start_time, $shift->template->end_time)) {
            return response()->json(['message' => 'Bu tarih ve saat aralığında işçi başka bir vardiyaya atanmış.'], 409);
        }

        $shiftAssignment->update([
            'user_id' => $workerId,
            'shift_id' => $shift->id,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Vardiya ataması başarıyla güncellendi.',
            'assignment' => $shiftAssignment->load(['shift.template', 'user']),
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

    /**
     * Kullanıcının atanmış olduğu vardiya template'lerini getirir.
     */
    public function getUserShiftTemplates($userId): JsonResponse
    {
        $shiftTemplates = ShiftTemplate::whereHas('shifts.shiftAssignments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        return response()->json($shiftTemplates);
    }

    private function getShiftTemplate($shiftTemplateId): ShiftTemplate
    {
        $shiftTemplate = ShiftTemplate::find($shiftTemplateId);
        if (!$shiftTemplate) {
            abort(404, 'Vardiya şablonu bulunamadı.');
        }
        return $shiftTemplate;
    }

    private function hasWorkerAssignedToShift($userId, $shiftId): bool
    {
        return ShiftAssignment::where('user_id', $userId)
            ->where('shift_id', $shiftId)
            ->exists();
    }

    private function ensureShiftsExistForWeek($weekStart)
    {
        $templates = ShiftTemplate::all();

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);

            foreach ($templates as $template) {
                $existingShift = Shift::where('template_id', $template->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->first();

                if (!$existingShift) {
                    Shift::create([
                        'template_id' => $template->id,
                        'date' => $date->format('Y-m-d'),
                    ]);
                }
            }
        }
    }
}
