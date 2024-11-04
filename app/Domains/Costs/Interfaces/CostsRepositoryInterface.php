<?php

namespace App\Domains\Costs\Interfaces;

use App\Domains\Costs\Models\Cost;
use App\Http\Requests\Costs\CostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CostsRepositoryInterface
{
    /**
     * Kaynakların listesini görüntüler.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse;

    /**
     * Yeni bir kaynağı depolama alanında oluşturur.
     *
     * @param  CostRequest  $request  Oluşturulacak kaynağın verilerini içeren istek
     * @return JsonResponse
     */
    public function store(CostRequest $request): JsonResponse;

    /**
     * Belirtilen kaynağı görüntüler.
     *
     * @param  int  $id  Görüntülenecek kaynağın ID'si
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse;

    /**
     * Belirtilen kaynağı depolama alanında günceller.
     *
     * @param  CostRequest  $request  Güncelleme verilerini içeren istek
     * @param  int  $id  Güncellenecek kaynağın ID'si
     * @return JsonResponse
     */
    public function update(CostRequest $request, int $id): JsonResponse;

    /**
     * Belirtilen kaynağı depolama alanından siler.
     *
     * @param  int  $id  Silinecek kaynağın ID'si
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;

    /**
     * Birden fazla kaynağı depolama alanından siler.
     *
     * @param  Request  $request  Silinecek kaynakların ID'lerini içeren istek
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse;


}
