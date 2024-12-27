<?php

namespace App\Http\Controllers\Worker;

use App\Domains\Shift\Models\Shift;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WorkerController extends Controller
{
    /**
     * Tüm işçileri döner.
     *
     * @return JsonResponse
     */
    public function getAllWorkers(): JsonResponse
    {
        $workers = User::where('role', 'worker')->get();

        return response()->json($workers);
    }

    /**
     * Tüm vardiyaları döner.
     *
     * @return JsonResponse
     */
    public function getShifts(): JsonResponse
    {
        $shifts = Shift::with('template')->get();
        return response()->json($shifts);
    }
}
