<?php

namespace App\Http\Controllers\Machines;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachinesController extends Controller
{


    public function index(): JsonResponse
    {
        return response()->json(Machine::all());
    }
    public function store(Request $request): JsonResponse
    {


        $machine = Machine::create([
            'machine_name' => $request->get('name'),
        ]);

        return response()->json($machine, 201);
    }
    public function show($id): JsonResponse
    {
        $machine = Machine::findOrFail($id);

        return response()->json($machine);
    }
    public function update(Request $request, $id): JsonResponse
    {
        $machine = Machine::findOrFail($id);
        $machine->update([
            'machine_name' => $request->name,
        ]);

        return response()->json($machine);
    }
    public function destroy($id): JsonResponse
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();

        return response()->json(null, 204);
    }
    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        Machine::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }

}