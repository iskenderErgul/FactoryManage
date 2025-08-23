<?php

namespace App\Http\Controllers\Suppliers;

use App\Domains\Suppliers\Repositories\SuppliesRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuppliesController extends Controller
{
    protected SuppliesRepository $suppliesRepository;

    public function __construct(SuppliesRepository $suppliesRepository)
    {
        $this->suppliesRepository = $suppliesRepository;
    }

    /**
     * Tüm tedarikleri döner.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->suppliesRepository->index();
    }

    /**
     * Yeni bir tedarik kaydeder.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return $this->suppliesRepository->store($request);
    }

    /**
     * Belirtilen tedariki döner.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->suppliesRepository->show($id);
    }

    /**
     * Var olan bir tedarik kaydını günceller.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        return $this->suppliesRepository->update($request, $id);
    }

    /**
     * Belirtilen ID'ye sahip tedariki siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->suppliesRepository->destroy($id);
    }

    /**
     * Seçili tedarik kayıtlarını siler.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->suppliesRepository->destroySelected($request);
    }
}
