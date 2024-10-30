<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function getAllWorkers(): JsonResponse
    {
        $workers = User::where('role', 'worker')->get();

        return response()->json($workers);
    }

    public function getShifts(): JsonResponse
    {
        $shifts = Shift::all();
        return response()->json($shifts);
    }
}
