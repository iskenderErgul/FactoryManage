<?php

namespace App\Domains\Costs\Repositories;

use App\Domains\Costs\Interfaces\CostsRepositoryInterface;
use App\Domains\Costs\Models\Cost;
use App\Http\Requests\Costs\CostRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostsRepository implements CostsRepositoryInterface
{
    /**
     * Tüm maliyet kayıtlarını listeler.
     *
     * @return JsonResponse Maliyet kayıtlarının JSON olarak döndürülmesi
     */
    public function index(): JsonResponse
    {
        $costs = Cost::all();
        return response()->json($costs);
    }

    /**
     * Yeni bir maliyet kaydı oluşturur.
     *
     * @param  CostRequest  $request  Yeni kaydı oluşturmak için gerekli verileri içeren istek
     * @return JsonResponse  Oluşturulan maliyet kaydının JSON olarak döndürülmesi (201 durumu ile)
     */
    public function store(CostRequest $request): JsonResponse
    {

        $cost = Cost::create([
            'cost_type' => $request->cost_type,
            'amount' => $request->amount,
            'cost_date' => Carbon::parse($request->cost_date)->setTimezone('Europe/Istanbul')->format('Y-m-d'),
        ]);
        return response()->json($cost, 201);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını görüntüler.
     *
     * @param  int  $id  Görüntülenecek maliyet kaydının ID'si
     * @return JsonResponse  Maliyet kaydı bulunursa JSON olarak, bulunamazsa hata mesajı döner
     */
    public function show($id): JsonResponse
    {
        $cost = Cost::find($id);

        if (!$cost) {
            return response()->json(['message' => 'Cost not found'], 404);
        }

        return response()->json($cost);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını günceller.
     *
     * @param  CostRequest  $request  Güncelleme verilerini içeren istek
     * @param  int  $id  Güncellenecek maliyet kaydının ID'si
     * @return JsonResponse  Güncellenmiş maliyet kaydının JSON olarak döndürülmesi
     */
    public function update(CostRequest $request, $id): JsonResponse
    {

        $cost=Cost::where('id', $id)->update([
            'cost_type' => $request->cost_type,
            'amount' => $request->amount,
            'cost_date' => Carbon::parse($request->cost_date)->setTimezone('Europe/Istanbul')->format('Y-m-d'),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
//        $cost = Cost::find($id);
//        $cost->update($request->all());
        return response()->json($cost);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını siler.
     *
     * @param  int  $id  Silinecek maliyet kaydının ID'si
     * @return JsonResponse  Silme işlemi başarılıysa başarı mesajı, kayıt bulunamazsa hata mesajı döner
     */
    public function destroy($id): JsonResponse
    {
        $cost = Cost::find($id);

        if (!$cost) {
            return response()->json(['message' => 'Cost not found'], 404);
        }

        $cost->delete();
        return response()->json(['message' => 'Cost deleted successfully']);
    }

    /**
     * Belirtilen birden fazla maliyet kaydını siler.
     *
     * @param  Request  $request  Silinecek kayıtların ID'lerini içeren istek
     * @return JsonResponse  Silme işlemi başarılıysa başarı mesajı döner
     */
    public function destroySelected(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:costs,id',
        ]);

        Cost::whereIn('id', $validatedData['ids'])->delete();
        return response()->json(['message' => 'Selected costs deleted successfully']);
    }
}
