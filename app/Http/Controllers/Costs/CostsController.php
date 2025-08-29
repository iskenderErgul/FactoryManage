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

    /**
     * Dönemsel maliyet raporlarını getirir.
     * Costs tablosu ve Supplies tablosundan maliyetleri hesaplar.
     *
     * @param  Request  $request  Periyod bilgisini içeren istek
     * @return JsonResponse
     */
    public function getPeriodicCosts(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        
        // Period'a göre tarih aralığını belirle
        $endDate = Carbon::now();
        $startDate = match($period) {
            '1month' => Carbon::now()->subMonth(),
            '2months' => Carbon::now()->subMonths(2),
            '3months' => Carbon::now()->subMonths(3),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth()
        };

        // Costs tablosundan maliyetleri getir
        $costs = Cost::whereBetween('cost_date', [$startDate, $endDate])
            ->selectRaw('cost_type as category, SUM(amount) as total_amount')
            ->groupBy('cost_type')
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category,
                    'amount' => $item->total_amount,
                    'source' => 'Genel Giderler'
                ];
            });

        // Supplies tablosundan tedarik maliyetlerini getir
        $supplies = \App\Domains\Suppliers\Models\Supply::with('supplier')
            ->whereBetween('supply_date', [$startDate, $endDate])
            ->selectRaw('supplier_id, supplied_product, SUM(supplied_product_price * supplied_product_quantity) as total_amount')
            ->groupBy('supplier_id', 'supplied_product')
            ->get()
            ->map(function($item) {
                $supplierName = $item->supplier ? $item->supplier->company_name : 'Bilinmeyen Tedarikçi';
                return [
                    'category' => 'Tedarik - ' . $supplierName . ' (' . $item->supplied_product . ')',
                    'amount' => $item->total_amount,
                    'source' => 'Tedarikler'
                ];
            });

        // Tüm maliyetleri birleştir
        $allCosts = $costs->concat($supplies);

        // Kaynak bazlı toplamları hesapla
        $sourceTotals = $allCosts->groupBy('source')->map(function($items, $source) {
            return [
                'source' => $source,
                'total' => $items->sum('amount')
            ];
        })->values();

        // Genel toplam
        $grandTotal = $allCosts->sum('amount');

        return response()->json([
            'period' => $period,
            'dateRange' => [
                'start' => $startDate->format('d.m.Y'),
                'end' => $endDate->format('d.m.Y')
            ],
            'costs' => $allCosts,
            'sourceTotals' => $sourceTotals,
            'grandTotal' => $grandTotal
        ]);
    }
}
