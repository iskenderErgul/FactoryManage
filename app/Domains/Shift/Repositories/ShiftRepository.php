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
     * Vardiya ataması yapar (haftalık sistem - tüm hafta için aynı vardiya)
     */
    public function addShiftAssignments(AddShiftAssignmentRequest $request): JsonResponse
    {
        $workerId = $request->user_id;
        $templateId = $request->shift_id; // Frontend'den template ID gelecek

        // Template'i kontrol et
        $template = ShiftTemplate::findOrFail($templateId);

        // Bu haftanın başlangıcı ve bitişi
        $today = Carbon::now();
        $weekStart = $today->copy()->startOfWeek(); // Pazartesi
        $weekEnd = $today->copy()->endOfWeek(); // Pazar

        $assignedShifts = [];
        $updatedCount = 0;
        $createdCount = 0;

        // Haftanın her günü için atama yap
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');

            // Bu tarih için bu template'e ait shift'i bul veya oluştur
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
                // Mevcut atamayı güncelle (başka vardiyadan bu vardiyaya geçir)
                $existingAssignment->update(['shift_id' => $shift->id]);
                $assignedShifts[] = $existingAssignment;
                $updatedCount++;
            } else {
                // Yeni atama oluştur
                $assignment = ShiftAssignment::create([
                    'user_id' => $workerId,
                    'shift_id' => $shift->id,
                ]);
                $assignedShifts[] = $assignment;
                $createdCount++;
            }
        }

        $message = "Kullanıcı bu hafta ({$weekStart->format('d.m.Y')} - {$weekEnd->format('d.m.Y')}) {$template->name} vardiyasına atandı.";
        if ($updatedCount > 0) {
            $message .= " ({$updatedCount} güncellendi, {$createdCount} yeni oluşturuldu)";
        }

        return response()->json([
            'message' => $message,
            'assignment_count' => count($assignedShifts),
            'updated_count' => $updatedCount,
            'created_count' => $createdCount,
            'template' => $template,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'assignments' => collect($assignedShifts)->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'date' => $assignment->shift->date,
                    'shift_name' => $assignment->shift->template->name
                ];
            })
        ]);
    }

    /**
     * Haftalık vardiya değiştirme sistemi - tüm hafta için rotasyon ve gelecek atamalar
     */
    public function rotateCurrentAssignments(): JsonResponse
    {
        try {
            $today = Carbon::now();
            $weekStart = $today->copy()->startOfWeek();
            $weekEnd = $today->copy()->endOfWeek();

            // Bu haftanın tüm atamalarını al
            $weekAssignments = ShiftAssignment::whereHas('shift', function($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d')
                ]);
            })->with(['shift.template', 'user'])->get();

            if ($weekAssignments->isEmpty()) {
                return response()->json([
                    'message' => 'Bu hafta için atama bulunamadı.',
                    'rotated_count' => 0
                ]);
            }

            // Vardiya template'lerini al
            $templates = ShiftTemplate::orderBy('id')->get();

            if ($templates->count() < 2) {
                return response()->json([
                    'message' => 'Rotasyon için en az 2 vardiya template\'i olmalı.',
                    'current_templates' => $templates->count()
                ], 400);
            }

            $rotatedCount = 0;
            $futureAssignmentsCount = 0;
            $rotationLog = [];

            // Her kullanıcının bu haftaki atamalarını sıralı şekilde değiştir
            $userAssignments = $weekAssignments->groupBy('user_id');

            foreach ($userAssignments as $userId => $assignments) {
                // İlk atamadan mevcut template'i al
                $firstAssignment = $assignments->first();
                $user = $firstAssignment->user;
                $currentTemplate = $firstAssignment->shift->template;

                // Mevcut template'in index'ini bul
                $currentTemplateIndex = $templates->search(function($template) use ($currentTemplate) {
                    return $template->id === $currentTemplate->id;
                });

                // Bir sonraki template'i belirle (sıralı rotasyon)
                $nextTemplateIndex = ($currentTemplateIndex + 1) % $templates->count();
                $newTemplate = $templates[$nextTemplateIndex];

                // Bu kullanıcının tüm haftalık atamalarını yeni vardiyaya geçir
                foreach ($assignments as $assignment) {
                    $currentDate = $assignment->shift->date;

                    // Yeni template'e ait shift'i bul veya oluştur
                    $newShift = Shift::where('template_id', $newTemplate->id)
                        ->where('date', $currentDate)
                        ->first();

                    if (!$newShift) {
                        $newShift = Shift::create([
                            'template_id' => $newTemplate->id,
                            'date' => $currentDate,
                        ]);
                    }

                    // Atamayı güncelle
                    $assignment->update(['shift_id' => $newShift->id]);
                    $rotatedCount++;
                }

                // GELECEKTEKİ HAFTALAR İÇİN OTOMATIK ATAMA OLUŞTUR
                $futureAssignmentsCount += $this->createFutureAssignmentsForUser($userId, $newTemplate, $templates);

                $rotationLog[] = [
                    'user' => $user->name,
                    'from' => $currentTemplate->name,
                    'to' => $newTemplate->name,
                    'from_index' => $currentTemplateIndex + 1,
                    'to_index' => $nextTemplateIndex + 1,
                    'days_rotated' => $assignments->count()
                ];
            }

            return response()->json([
                'message' => 'Vardiya rotasyonu başarıyla tamamlandı ve gelecek atamalar oluşturuldu.',
                'rotated_count' => $rotatedCount,
                'future_assignments_count' => $futureAssignmentsCount,
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

    /**
     * Kullanıcının bugünkü vardiyasını getirir
     */
    public function getUserTodayShift($userId): JsonResponse
    {
        $today = now()->format('Y-m-d');

        $todayShift = ShiftAssignment::with(['shift.template', 'user'])
            ->where('user_id', $userId)
            ->whereHas('shift', function($query) use ($today) {
                $query->where('date', $today);
            })
            ->first();

        if (!$todayShift) {
            return response()->json([
                'message' => 'Bugün için vardiya ataması bulunamadı.',
                'has_shift' => false
            ]);
        }

        return response()->json([
            'has_shift' => true,
            'shift' => [
                'id' => $todayShift->shift->id,
                'name' => $todayShift->shift->template->name,
                'start_time' => $todayShift->shift->template->start_time,
                'end_time' => $todayShift->shift->template->end_time,
                'date' => $todayShift->shift->date,
                'user' => $todayShift->user->name
            ]
        ]);
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

    /**
     * Kullanıcı için gelecek haftalar için otomatik atama oluşturur (rotasyon ile)
     */
    private function createFutureAssignmentsForUser($userId, $currentTemplate, $allTemplates): int
    {
        $assignmentsCreated = 0;
        $weeksToCreate = 4; // Gelecek 4 hafta için atama oluştur

        // Mevcut template'in index'ini bul
        $currentTemplateIndex = $allTemplates->search(function($template) use ($currentTemplate) {
            return $template->id === $currentTemplate->id;
        });

        $today = Carbon::now();
        
        for ($weekOffset = 1; $weekOffset <= $weeksToCreate; $weekOffset++) {
            $weekStart = $today->copy()->addWeeks($weekOffset)->startOfWeek();
            
            // Bu hafta için hangi template kullanılacak (rotasyon mantığı)
            $templateIndex = ($currentTemplateIndex + $weekOffset) % $allTemplates->count();
            $templateForWeek = $allTemplates[$templateIndex];

            // Bu kullanıcının bu hafta için zaten ataması var mı kontrol et
            $existingAssignments = ShiftAssignment::whereHas('shift', function($query) use ($weekStart) {
                $weekEnd = $weekStart->copy()->endOfWeek();
                $query->whereBetween('date', [
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d')
                ]);
            })->where('user_id', $userId)->exists();

            // Eğer bu hafta için atama yoksa oluştur
            if (!$existingAssignments) {
                for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                    $date = $weekStart->copy()->addDays($dayOffset);
                    $dateStr = $date->format('Y-m-d');

                    // Bu tarih için shift oluştur veya bul
                    $shift = Shift::where('template_id', $templateForWeek->id)
                        ->where('date', $dateStr)
                        ->first();

                    if (!$shift) {
                        $shift = Shift::create([
                            'template_id' => $templateForWeek->id,
                            'date' => $dateStr,
                        ]);
                    }

                    // Bu kullanıcının bu tarihte başka ataması var mı kontrol et
                    $existingDayAssignment = ShiftAssignment::where('user_id', $userId)
                        ->whereHas('shift', function($query) use ($dateStr) {
                            $query->where('date', $dateStr);
                        })
                        ->first();

                    if (!$existingDayAssignment) {
                        ShiftAssignment::create([
                            'user_id' => $userId,
                            'shift_id' => $shift->id,
                        ]);
                        $assignmentsCreated++;
                    }
                }
            }
        }

        return $assignmentsCreated;
    }
}
