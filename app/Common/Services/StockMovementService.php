<?php

namespace App\Common\Services;

use App\Common\Models\StockMovement;
use App\Domains\Product\Models\Product;

class StockMovementService
{

    /**
     * LoggerService sınıfı ile stok hareketleri ve loglama işlemlerini gerçekleştirir.
     */
    protected LoggerService $loggerService;

    /**
     * LoggerService bağımlılığını enjekte eder.
     *
     * @param LoggerService $loggerService Loglama işlemleri için kullanılan servis sınıfı.
     */
    public function __construct(LoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
    }

    /**
     * Yeni bir stok hareketi oluşturur ve log kaydını yapar.
     *
     * @param int $productId Stok hareketi yapılacak ürünün ID'si.
     * @param int $quantity Stok hareketinde kullanılacak miktar.
     * @param string $movementType Stok hareket tipi ("giriş" veya "çıkış").
     * @param string $relatedProcess Stok hareketiyle ilgili işlemin adı.
     * @param string $action Loglama için kullanılacak işlem adı ("create", "update", "delete" vb.).
     */
    public function createStockMovement(int $productId, int $quantity, string $movementType, string $relatedProcess, string $action): void
    {
        $stockMovement = StockMovement::create([
            'product_id' => $productId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'related_process' => $relatedProcess,
            'movement_date' => now(),
        ]);

        // Loglama işlemi
        $this->loggerService->logStockMovementAction($action, $stockMovement, 'Stok hareketi oluşturuldu.');
    }

    /**
     * Stok miktarını azaltır ve ilgili stok hareketini oluşturur.
     *
     * @param int $productId Stok azaltılacak ürünün ID'si.
     * @param int $quantity Azaltılacak stok miktarı.
     * @param string $relatedProcess Stok azaltma işlemiyle ilgili işlem adı (örn. "Satış güncelleme").
     */
    public function reduceStock(int $productId, int $quantity, string $relatedProcess): void
    {
        $product = Product::findOrFail($productId);
        $product->decrement('stock_quantity', $quantity);

        $this->createStockMovement($productId, $quantity, 'çıkış', $relatedProcess, 'update');
    }

    /**
     * Stok miktarını artırır ve ilgili stok hareketini oluşturur.
     *
     * @param int $productId Stok artırılacak ürünün ID'si.
     * @param int $quantity Artırılacak stok miktarı.
     */
    public function increaseStock(int $productId, int $quantity): void
    {
        $product = Product::findOrFail($productId);
        $product->increment('stock_quantity', $quantity);

        $this->createStockMovement($productId, $quantity, 'giriş', 'Stok arttırma işlemi', 'update');
    }

    /**
     * Stok miktarını günceller. Yeni miktar önceki miktardan farklıysa, stok hareketini
     * doğru yöne göre ("giriş" veya "çıkış") oluşturur.
     *
     * @param int $productId Stok güncellemesi yapılacak ürünün ID'si.
     * @param int $newQuantity Yeni stok miktarı.
     * @param int $previousQuantity Önceki stok miktarı.
     */
    public function updateStockQuantity(int $productId, int $newQuantity, int $previousQuantity): void
    {
        $difference = $newQuantity - $previousQuantity;

        if ($difference > 0) {
            $this->increaseStock($productId, $difference);
        }

        elseif ($difference < 0) {
            $this->reduceStock($productId, abs($difference), 'Satış güncelleme');
        }
    }
}
