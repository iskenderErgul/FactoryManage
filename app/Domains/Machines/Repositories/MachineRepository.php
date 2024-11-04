<?php

namespace App\Domains\Machines\Repositories;

use App\Domains\Machines\Interfaces\MachineRepositoryInterface;
use App\Domains\Machines\Models\Machine;
use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineRepository implements MachineRepositoryInterface
{
    /**
     * Tüm makine kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Machine::all());
    }

    /**
     * Yeni bir makine kaydı oluşturur.
     *
     * @param StoreMachineRequest $request  Makine bilgilerini içeren doğrulanmış istek
     * @return JsonResponse
     */
    public function store(StoreMachineRequest $request): JsonResponse
    {


        $machine = Machine::create([
            'machine_name' => $request->get('name'),
        ]);

        return response()->json($machine, 201);
    }

    /**
     * Belirtilen makine kaydını gösterir.
     *
     * @param int $id  Gösterilecek makine kaydının ID'si
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $machine = Machine::findOrFail($id);

        return response()->json($machine);
    }

    /**
     * Belirtilen makine kaydını günceller.
     *
     * @param UpdateMachineRequest $request  Güncellenmiş makine bilgilerini içeren doğrulanmış istek
     * @param int $id  Güncellenecek makine kaydının ID'si
     * @return JsonResponse
     */
    public function update(UpdateMachineRequest $request, $id): JsonResponse
    {
        $machine = Machine::findOrFail($id);
        $machine->update([
            'machine_name' => $request->name,
        ]);

        return response()->json($machine);
    }

    /**
     * Belirtilen makine kaydını siler.
     *
     * @param int $id  Silinecek makine kaydının ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();

        return response()->json(null, 204);
    }

    /**
     * Belirtilen makine ID'lerine göre birden fazla makine kaydını siler.
     *
     * @param Request $request  Silinecek makine ID'lerini içeren istek
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        Machine::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }
}
