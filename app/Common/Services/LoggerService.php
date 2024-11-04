<?php

namespace App\Common\Services;

use App\Common\Models\StockMovement;
use App\Common\Models\StockMovementsLog;
use App\Domains\PacsEntry\Models\PacsEntriesLog;
use App\Domains\Production\Models\Production;
use App\Domains\Production\Models\ProductionLog;
use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesLog;
use Illuminate\Support\Facades\Auth;

class LoggerService
{
    /**
     * Üretim işlemi log kaydı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param Production $production Üretim kaydı ile ilgili tüm bilgileri içerir.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     */
    public function logProductionAction(string $action, Production $production, string $additionalInfo = ''): void
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

    /**
     * Üretim log mesajı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param Production $production Üretim kaydı ile ilgili tüm bilgileri içerir.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     * @return string Oluşturulan log mesajı.
     */
    private function getProductionLogMessage(string $action, Production $production, string $additionalInfo): string
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

    /**
     * Stok hareketi log kaydı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param StockMovement $stockMovement Stok hareketi kaydı ile ilgili tüm bilgileri içerir.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     */
    public function logStockMovementAction(string $action, StockMovement $stockMovement, string $additionalInfo = ''): void
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

    /**
     * Stok hareketi log mesajı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param StockMovement $stockMovement Stok hareketi kaydı ile ilgili tüm bilgileri içerir.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     * @return string Oluşturulan log mesajı.
     */
    private function getStockMovementLogMessage(string $action, StockMovement $stockMovement, string $additionalInfo): string
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

    /**
     * Satış işlemi log kaydı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param Sales $sale Satış kaydı ile ilgili tüm bilgileri içerir.
     * @param string $message Log kaydı için oluşturulan mesaj.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     */
    public function logSaleAction(string $action, Sales $sale, string $message, string $additionalInfo = ''): void
    {
        $message = $this->getSaleLogMessage($action, $sale, $additionalInfo);
        SalesLog::create([
            'sale_id' => $sale->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Satış log mesajı oluşturur.
     *
     * @param string $action İşlem türü ("create", "update", "delete").
     * @param Sales $sale Satış kaydı ile ilgili tüm bilgileri içerir.
     * @param string $additionalInfo Opsiyonel olarak ekstra bilgi eklemek için kullanılan parametre.
     * @return string Oluşturulan log mesajı.
     */
    private function getSaleLogMessage(string $action, Sales $sale, string $additionalInfo): string
    {
        switch ($action) {
            case 'create':
                return "Satış kaydı oluşturuldu. Müşteri ID: {$sale->customer_id}, Satış Tarihi: {$sale->sale_date}. $additionalInfo";
            case 'update':
                return "Satış kaydı güncellendi. Müşteri ID: {$sale->customer_id}, Yeni Satış Tarihi: {$sale->sale_date}. $additionalInfo";
            case 'delete':
                return "Satış kaydı silindi. Müşteri ID: {$sale->customer_id}, Satış Tarihi: {$sale->sale_date}. $additionalInfo";
            default:
                return $additionalInfo;
        }
    }


    /**
     * PACS giriş kaydı için log oluşturur.
     *
     * @param int $pacsEntryId PACS girişi için ID.
     * @param string $entryType Giriş türü (checkin veya checkout).
     * @return void
     */
    public function createPacsEntryLog(int $pacsEntryId, string $entryType): void
    {
        PacsEntriesLog::create([
            'pacs_entry_id' => $pacsEntryId,
            'user_id' => auth()->id(),
            'action' => 'create',
            'changes' => "ID: {$pacsEntryId}, Tür: {$entryType}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


}
