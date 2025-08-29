<?php

namespace App\Domains\Recyclings\Repositories;

use App\Domains\Recyclings\Models\Recycling;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecyclingsRepository
{

    public function index(): JsonResponse
    {
        return response()->json(Recycling::all());
    }

    public function store(Request $request): JsonResponse
    {

        $recyclingDate = Carbon::parse($request->get('recycling_date'))->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s');

        $recycling = Recycling::create([
            'company_name' => $request->get('company_name'),
            'material_type' => $request->get('material_type'),
            'recycling_date' => $recyclingDate, // UTC olarak dönüştürülmüş tarihi kullanıyoruz
            'recycling_quantity' => $request->get('recycling_quantity'),
        ]);

        return response()->json($recycling, 201);
    }

    public function show($id): JsonResponse
    {
        $recycling = Recycling::findOrFail($id);

        return response()->json($recycling);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $recycling = Recycling::findOrFail($id);


        $recyclingDate = Carbon::parse($request->get('recycling_date'))->setTimezone('Europe/Istanbul')->format('Y-m-d H:i:s');

        $recycling->update([
            'company_name' => $request->company_name,
            'material_type' => $request->material_type,
            'recycling_date' => $recyclingDate,
            'recycling_quantity' => $request->recycling_quantity,
        ]);

        return response()->json($recycling);
    }

    public function destroy($id): JsonResponse
    {
        $recycling = Recycling::findOrFail($id);
        $recycling->delete();

        return response()->json(null, 204);
    }

    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        Recycling::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }
}
