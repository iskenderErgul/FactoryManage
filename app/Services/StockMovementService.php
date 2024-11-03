<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;

class StockMovementService
{

    protected LoggerService $loggerService;

    public function __construct(LoggerService $loggerService)
    {
        $this->loggerService = $loggerService;
    }
    public function createStockMovement($productId, $quantity, $movementType, $relatedProcess,$action): void
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

    public function reduceStock($productId, $quantity, $relatedProcess): void
    {
        $product = Product::findOrFail($productId);
        $product->decrement('stock_quantity', $quantity);

        $this->createStockMovement($productId, $quantity, 'çıkış', $relatedProcess, 'update');
    }

    public function increaseStock($productId, $quantity): void
    {
        $product = Product::findOrFail($productId);
        $product->increment('stock_quantity', $quantity);

        $this->createStockMovement($productId, $quantity, 'giriş', 'Stok arttırma işlemi', 'update');
    }

    public function updateStockQuantity($productId, $newQuantity, $previousQuantity): void
    {
        $difference = $newQuantity - $previousQuantity;

        if ($difference > 0) {
            // Eğer yeni miktar eskisinden fazlaysa, stoktan çıkış yapılması gerekiyor
            $this->reduceStock($productId, $difference, 'Satış güncelleme');
        } elseif ($difference < 0) {
            // Eğer yeni miktar eskisinden azsa, stok girişi yapılması gerekiyor
            $this->increaseStock($productId, abs($difference));
        }
    }
}
