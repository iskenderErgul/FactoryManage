<?php

namespace App\Http\Controllers\Costs;

use App\Domains\Costs\Models\Cost;
use App\Domains\Costs\Repositories\CostsRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Costs\CostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;


class CostsController extends Controller
{
    protected CostsRepository $costsRepository;

    /**
     * CostsController yapıcısı.
     *
     * @param  CostsRepository  $costsRepository  Maliyet ile ilgili işlemleri gerçekleştiren repository
     */
    public function __construct(CostsRepository $costsRepository)
    {
        $this->costsRepository = $costsRepository;
    }

    /**
     * Tüm maliyet kayıtlarını listeler.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->costsRepository->index();
    }

    /**
     * Yeni bir maliyet kaydı oluşturur.
     *
     * @param  CostRequest  $request  Yeni kaydı oluşturmak için gerekli verileri içeren istek
     * @return JsonResponse
     */
    public function store(CostRequest $request): JsonResponse
    {
        return $this->costsRepository->store($request);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını görüntüler.
     *
     * @param  int  $id  Görüntülenecek maliyet kaydının ID'si
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return $this->costsRepository->show($id);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını günceller.
     *
     * @param  CostRequest  $request  Güncelleme verilerini içeren istek
     * @param  int  $id  Güncellenecek maliyet kaydının ID'si
     * @return JsonResponse
     */
    public function update(CostRequest $request, $id): JsonResponse
    {
        return $this->costsRepository->update($request, $id);
    }

    /**
     * Belirtilen ID'ye sahip maliyet kaydını siler.
     *
     * @param  int  $id  Silinecek maliyet kaydının ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->costsRepository->destroy($id);
    }


    /**
     * Belirtilen birden fazla maliyet kaydını siler.
     *
     * @param  Request  $request  Silinecek kayıtların ID'lerini içeren istek
     * @return JsonResponse
     */
    public function destroySelected(Request $request): JsonResponse
    {
        return $this->costsRepository->destroySelected($request);

    }
}
