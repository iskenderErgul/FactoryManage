<?php

namespace App\Common\Services;

use App\Domains\Suppliers\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SupplierPdfService
{
    /**
     * Tedarikçi transaction'larını Cari Ekstre formatında PDF olarak üretir
     *
     * @param int $supplierId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param bool $allHistory
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateSupplierPdf(int $supplierId, ?string $startDate, ?string $endDate, bool $allHistory = false)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            
            // 1. Devreden Bakiye Hesabı (Opening Balance)
            $openingBalance = 0;
            $transactions = collect();
            
            if ($allHistory) {
                // Tüm geçmiş isteniyorsa devreden bakiye 0'dır, tüm işlemleri çek
                $transactions = $supplier->transactions()
                    ->orderBy('date', 'asc') // Eskiden yeniye
                    ->orderBy('id', 'asc')
                    ->get();
                    
                $startDate = $transactions->first()?->date ?? Carbon::now()->format('Y-m-d');
                $endDate = Carbon::now()->format('Y-m-d');
            } else {
                // Tarih aralığı varsa, başlangıç tarihinden önceki işlemleri hesapla
                $openingBalance = $this->calculateOpeningBalance($supplier, $startDate);
                
                // Seçilen aralıktaki işlemleri çek
                $transactions = $supplier->transactions()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc') // Eskiden yeniye
                    ->orderBy('id', 'asc')
                    ->get();
            }
            
            // PDF verilerini hazırla
            $data = [
                'supplier' => $supplier,
                'transactions' => $transactions,
                'startDate' => Carbon::parse($startDate)->format('d.m.Y'),
                'endDate' => Carbon::parse($endDate)->format('d.m.Y'),
                'generatedDate' => Carbon::now()->format('d.m.Y H:i'),
                'openingBalance' => $openingBalance,
                'allHistory' => $allHistory
            ];
            
            // PDF oluştur
            $pdf = Pdf::loadView('pdf.supplier-pdf', $data);
            
            // PDF ayarları
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'DejaVu Sans');
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Supplier PDF Generation Error: ' . $e->getMessage(), [
                'supplier_id' => $supplierId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Belirtilen tarihten önceki toplam bakiyeyi hesaplar
     */
    private function calculateOpeningBalance(Supplier $supplier, string $date): float
    {
        // Tarihten önceki tüm işlemleri al
        $previousTransactions = $supplier->transactions()
            ->where('date', '<', $date)
            ->get();
            
        $balance = 0;
        
        foreach ($previousTransactions as $transaction) {
            $type = strtolower($transaction->type);
            $amount = floatval($transaction->amount);
            
            if ($type === 'borç') {
                $balance += $amount; // Borç artırır (Tedarikçiye borcumuz)
            } elseif ($type === 'ödeme') {
                $balance -= $amount; // Ödeme azaltır
            }
        }
        
        return $balance;
    }
    
    /**
     * Tutar formatla (Türk Lirası formatı)
     */
    public static function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }
}
