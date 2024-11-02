<?php

namespace App\Services;

use App\Models\Production;
use App\Models\ProductionLog;
use App\Models\StockMovement;
use App\Models\StockMovementsLog;
use Illuminate\Support\Facades\Auth;

class LoggerService
{
    public function logProductionAction($action, Production $production, $additionalInfo = ''): void
    {
        $message = $this->getProductionLogMessage($action, $production, $additionalInfo);
        ProductionLog::create([
            'production_id' => $production->id,
            'user_id' => $production->user_id,
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getProductionLogMessage($action, Production $production, $additionalInfo): string
    {
        switch ($action) {
            case 'create':
                return "Üretim kaydı oluşturuldu. Ürün: {$production->product->name}, Miktar: {$production->quantity}, Makine: {$production->machine->name}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
            case 'update':
                return "Üretim kaydı güncellendi. Ürün: {$production->product->name}, Yeni Miktar: {$production->quantity}, Makine: {$production->machine->name}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
            case 'delete':
                return "Üretim kaydı silindi. Ürün: {$production->product->name}, Miktar: {$production->quantity}, İşçi: " . ($production->user->name ?? 'Belirtilmemiş') . ", Tarih: {$production->production_date}. $additionalInfo";
            default:
                return $additionalInfo;
        }
    }

    public function logStockMovementAction($action, StockMovement $stockMovement, $additionalInfo = ''): void
    {
        $message = $this->getStockMovementLogMessage($action, $stockMovement, $additionalInfo);
        StockMovementsLog::create([
            'stock_movement_id' => $stockMovement->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
        ]);
    }

    private function getStockMovementLogMessage($action, StockMovement $stockMovement, $additionalInfo): string
    {
        switch ($action) {
            case 'create':
                return "Stok hareketi oluşturuldu. Ürün: {$stockMovement->product_id}, Miktar: {$stockMovement->quantity}, Hareket Tipi: {$stockMovement->movement_type}, Tarih: {$stockMovement->movement_date}. $additionalInfo";
            case 'update':
                return "Stok hareketi güncellendi. Ürün: {$stockMovement->product_id}, Yeni Miktar: {$stockMovement->quantity}, Hareket Tipi: {$stockMovement->movement_type}, Tarih: {$stockMovement->movement_date}. $additionalInfo";
            case 'delete':
                return "Stok hareketi silindi. Ürün: {$stockMovement->product_id}, Miktar: {$stockMovement->quantity}, Hareket Tipi: {$stockMovement->movement_type}, Tarih: {$stockMovement->movement_date}. $additionalInfo";
            default:
                return $additionalInfo;
        }
    }


}
