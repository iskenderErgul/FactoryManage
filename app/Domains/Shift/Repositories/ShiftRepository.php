<?php

namespace App\Domains\Shift\Repositories;

use App\Domains\Shift\Interfaces\ShiftRepositoryInterface;
use App\Domains\Shift\Models\Shift;
use App\Domains\Shift\Models\ShiftTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function getShiftTemplates(): JsonResponse
    {
       $shiftTemplates = ShiftTemplate::with('shifts')->get();

       return response()->json($shiftTemplates);
    }
    public function addShiftTemplates(Request $request): JsonResponse
    {
        $startTime = Carbon::createFromFormat('H:i:s', $request->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $request->end_time);

        $duration = $startTime->diffInMinutes($endTime);

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
    public function updateShiftTemplates(Request $request,$id): JsonResponse
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
}
