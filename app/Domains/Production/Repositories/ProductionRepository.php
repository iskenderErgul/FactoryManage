<?php

namespace App\Domains\Production\Repositories;

use App\Common\Services\LoggerService;
use App\Common\Services\StockMovementService;
use App\Domains\Product\Models\Product;
use App\Domains\Production\Interfaces\ProductionRepositoryInterface;
use App\Domains\Production\Models\Production;
use App\Domains\Production\Models\ProductionLog;
use App\DTOs\Production\StoreProductionDTO;
use App\DTOs\Production\UpdateProductionDTO;
use App\Http\Requests\Production\StoreByWorkerProductionRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionRepository implements ProductionRepositoryInterface
{
    protected StockMovementService $stockMovementService;
    protected LoggerService $loggerService;

    public function __construct(StockMovementService $stockMovementService, LoggerService $loggerService)
    {
        $this->stockMovementService = $stockMovementService;
        $this->loggerService = $loggerService;
    }

    /**
     * Tüm üretim kayıtlarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductions(): JsonResponse
    {
        $productions = Production::with(['machine', 'product', 'user', 'shift.template'])->get();

        return response()->json($productions);
    }

    /**
     * Tüm üretim loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllProductionLogs(): JsonResponse
    {
        $productionLogs = ProductionLog::with('user')->get();
        return response()->json($productionLogs);
    }

    /**
     * İşçi tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreByWorkerProductionRequest $request  Üretim kaydı oluşturmak için gerekli bilgiler
     * @return JsonResponse
     */
    public function storeByWorker(StoreByWorkerProductionRequest $request): JsonResponse
    {
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');

        // Bugünkü vardiyaları getir
        $todayShifts = \App\Domains\Shift\Models\ShiftAssignment::with(['shift.template'])
            ->where('user_id', $request->user_id)
            ->whereHas('shift', function($query) use ($today) {
                $query->where('date', $today);
            })
            ->get();

        // Eğer bugün hiç vardiya yoksa, rotasyon sistemine göre doğru vardiyayı belirle ve oluştur
        if ($todayShifts->isEmpty()) {
            $correctTemplate = $this->determineCorrectShiftTemplateForUser($request->user_id, $today);
            
            if ($correctTemplate) {
                // Bugün için shift oluştur
                $todayShift = \App\Domains\Shift\Models\Shift::firstOrCreate([
                    'template_id' => $correctTemplate->id,
                    'date' => $today,
                ]);

                // Bugün için atama oluştur
                $todayAssignment = \App\Domains\Shift\Models\ShiftAssignment::create([
                    'user_id' => $request->user_id,
                    'shift_id' => $todayShift->id,
                ]);

                $todayShifts = collect([$todayAssignment->load(['shift.template'])]);
            }
        }

        // Bugünkü vardiyalar arasından aktif olanı bul
        $currentShiftAssignment = null;
        foreach ($todayShifts as $shiftAssignment) {
            $startTime = $shiftAssignment->shift->template->start_time;
            $endTime = $shiftAssignment->shift->template->end_time;
            
            // Vardiya saatleri arasında mı kontrol et
            if ($startTime <= $currentTime && $endTime >= $currentTime) {
                $currentShiftAssignment = $shiftAssignment;
                break;
            }
        }

        // Eğer aktif vardiya bulunamadıysa, bugünün ilk vardiyasını al
        if (!$currentShiftAssignment && !$todayShifts->isEmpty()) {
            $currentShiftAssignment = $todayShifts->first();
        }

        if (!$currentShiftAssignment) {
            return response()->json([
                'message' => 'Size atanmış herhangi bir vardiya bulunamadı. Lütfen yöneticinize başvurun.',
                'error_code' => 'NO_SHIFT_ASSIGNED'
            ], 403);
        }

        $shift = $currentShiftAssignment->shift;

        // Production oluştur
        $production = Production::create([
            'user_id' => $request->user_id,
            'shift_id' => $shift->id,
            'machine_id' => $request->machine_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'production_date' => now(),
        ]);

        // Stok işlemleri
        $this->stockMovementService->createStockMovement(
            $request->product_id,
            $request->quantity,
            'giriş',
            'Üretim',
            'create'
        );

        $this->loggerService->logProductionAction('create', $production, 'İşçi tarafından üretim kaydı oluşturuldu.');

        $product = Product::findOrFail($request->product_id);
        $product->stock_quantity += $request->quantity;
        $product->save();

        return response()->json([
            'success' => true,
            'production' => $production,
            'shift_info' => [
                'name' => $shift->template->name,
                'start_time' => $shift->template->start_time,
                'end_time' => $shift->template->end_time
            ],
            'message' => "Üretim '{$shift->template->name}' vardiyasına kaydedildi."
        ], 201);
    }

    /**
     * Yönetici tarafından yeni bir üretim kaydı oluşturur.
     *
     * @param StoreProductionDTO $request  Üretim kaydı oluşturmak için gerekli bilgiler
     * @return JsonResponse
     */

    // Kullanıcının bugünkü vardiya listesini döner
    public function getCurrentShift(Request $request)
    {
        $userId = $request->user()->id;
        $today = now()->format('Y-m-d');

        $todayShifts = \App\Domains\Shift\Models\ShiftAssignment::with(['shift.template'])
            ->where('user_id', $userId)
            ->whereHas('shift', function($query) use ($today) {
                $query->where('date', $today);
            })
            ->get();

        return response()->json([
            'today_shifts' => $todayShifts->map(function($assignment) {
                return [
                    'id' => $assignment->shift->id,
                    'name' => $assignment->shift->template->name,
                    'start_time' => $assignment->shift->template->start_time,
                    'end_time' => $assignment->shift->template->end_time,
                ];
            })
        ]);
    }
    public function storeByAdmin(StoreProductionDTO $request): JsonResponse
    {


        $formattedProductionDate = Carbon::parse($request->productionDate)->format('Y-m-d H:i:s');

        $production = Production::create([
            'user_id' =>$request->workerId,
            'machine_id' => $request->machineId,
            'product_id' => $request->productId,
            'quantity' => $request->quantity,
            'shift_id' => $request->shiftId,
            'production_date' => $formattedProductionDate,
        ]);

        $this->stockMovementService->createStockMovement(
            $request->productId,
            $request->quantity,
            'giriş',
            'Üretim',
            'create'
        );


        $this->loggerService->logProductionAction('create', $production, 'Yönetici tarafından üretim kaydı oluşturuldu.');

        $product = Product::findOrFail($request->productId);
        $product->stock_quantity += $request->quantity;
        $product->save();


        return response()->json($production, 201);
    }

    /**
     * Belirtilen ID'ye sahip üretim kaydını günceller.
     *
     * @param UpdateProductionDTO $request  Güncellenecek üretim bilgilerini içeren istek
     * @param int $id  Güncellenecek üretim kaydı ID'si
     * @return JsonResponse
     */
    public function update(UpdateProductionDTO $request, $id): JsonResponse
    {

        $formattedProductionDate = Carbon::parse($request->production_date)->format('Y-m-d H:i:s');
        $production = Production::findOrFail($id);

        // Mevcut değerleri sakla
        $previousQuantity = $production->quantity;
        $previousProductId = $production->product_id;
        $newQuantity = $request->quantity;
        $newProductId = $request->product_id;

        $logMessage = '';

        // Ürün değişikliği kontrolü
        if ($previousProductId !== $newProductId) {
            // Eski ürünün stok miktarını azalt
            $this->stockMovementService->reduceStock($previousProductId, $previousQuantity, 'Üretim Güncelleme');
            // Yeni ürünün stok miktarını artır
            $this->stockMovementService->increaseStock($newProductId, $newQuantity);
            $logMessage = "Ürün değişti. Eski Ürün: {$previousProductId}, Yeni Ürün: {$newProductId}.";
        } else {
            // Ürün değişmediği takdirde sadece miktar güncellemesi
            if ($newQuantity !== $previousQuantity) {
                $this->stockMovementService->updateStockQuantity($newProductId, $newQuantity, $previousQuantity);
                $logMessage = "Miktar güncellendi. Eski Miktar: {$previousQuantity}, Yeni Miktar: {$newQuantity}.";
            }
        }

        // Üretim kaydını güncelle
        $production->update([
            'user_id' => $request->worker_id,
            'machine_id' => $request->machine_id,
            'product_id' => $newProductId,
            'quantity' => $newQuantity,
            'shift_id' => $request->shift_id,
            'production_date' => $formattedProductionDate,
        ]);

        if (empty($logMessage)) {
            $logMessage = 'Üretim kaydı güncellendi.';
        }
        $this->loggerService->logProductionAction('update', $production, $logMessage);


        return response()->json($production);
    }

    /**
     * Belirtilen ID'ye sahip üretim kaydını siler.
     *
     * @param int $id  Silinecek üretim kaydı ID'si
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {

        $production = Production::findOrFail($id);

        $productId = $production->product_id;
        $quantity = $production->quantity;


        $product = Product::findOrFail($productId);

        // Stok hareketi ekleme
        $this->stockMovementService->createStockMovement($productId, $quantity, 'çıkış', 'Üretim Silme','delete');


        $product->stock_quantity -= $quantity;
        $product->save();


        // Loglama işlemi
        $this->loggerService->logProductionAction('delete', $production, 'Üretim kaydı silindi.');

        $production->delete();

        return response()->json(null, 204);
    }

    /**
     * Kullanıcı için rotasyon sistemine göre doğru vardiya template'ini belirler
     */
    private function determineCorrectShiftTemplateForUser($userId, $date)
    {
        // Tüm vardiya template'lerini al (sıralı olarak)
        $templates = \App\Domains\Shift\Models\ShiftTemplate::orderBy('id')->get();
        
        if ($templates->isEmpty()) {
            return null;
        }

        // Tek template varsa onu döndür
        if ($templates->count() === 1) {
            return $templates->first();
        }

        // Kullanıcının en son vardiya atamasını bul
        $lastAssignment = \App\Domains\Shift\Models\ShiftAssignment::with(['shift.template'])
            ->where('user_id', $userId)
            ->whereHas('shift', function($query) use ($date) {
                $query->where('date', '<', $date);
            })
            ->orderByDesc('id')
            ->first();

        if (!$lastAssignment) {
            // Hiç atama yoksa ilk template'i döndür
            return $templates->first();
        }

        // Son template'in index'ini bul
        $lastTemplate = $lastAssignment->shift->template;
        $lastTemplateIndex = $templates->search(function($template) use ($lastTemplate) {
            return $template->id === $lastTemplate->id;
        });

        // Verilen tarih ile son atama tarihi arasındaki hafta farkını hesapla
        $lastAssignmentDate = Carbon::parse($lastAssignment->shift->date);
        $targetDate = Carbon::parse($date);
        
        // Haftaları hesapla (Pazartesi bazlı)
        $lastWeekStart = $lastAssignmentDate->copy()->startOfWeek();
        $targetWeekStart = $targetDate->copy()->startOfWeek();
        $weeksDiff = $lastWeekStart->diffInWeeks($targetWeekStart);

        // Rotasyon mantığı: Her hafta bir sonraki template'e geç
        $targetTemplateIndex = ($lastTemplateIndex + $weeksDiff) % $templates->count();
        
        return $templates[$targetTemplateIndex];
    }
}
