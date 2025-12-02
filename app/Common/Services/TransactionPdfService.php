<?php

namespace App\Common\Services;

use App\Domains\Customer\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TransactionPdfService
{
    /**
     * Müşteri transaction'larını Cari Ekstre formatında PDF olarak üretir
     *
     * @param int $customerId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param bool $allHistory
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateTransactionPdf(int $customerId, ?string $startDate, ?string $endDate, bool $allHistory = false)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            // 1. Devreden Bakiye Hesabı (Opening Balance)
            $openingBalance = 0;
            $transactions = collect();
            
            if ($allHistory) {
                // Tüm geçmiş isteniyorsa devreden bakiye 0'dır, tüm işlemleri çek
                $transactions = $customer->transactions()
                    ->orderBy('date', 'asc') // Eskiden yeniye
                    ->orderBy('id', 'asc')
                    ->get();
                    
                $startDate = $transactions->first()?->date ?? Carbon::now()->format('Y-m-d');
                $endDate = Carbon::now()->format('Y-m-d');
            } else {
                // Tarih aralığı varsa, başlangıç tarihinden önceki işlemleri hesapla
                $openingBalance = $this->calculateOpeningBalance($customer, $startDate);
                
                // Seçilen aralıktaki işlemleri çek
                $transactions = $customer->transactions()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'asc') // Eskiden yeniye
                    ->orderBy('id', 'asc')
                    ->get();
            }
            
            // PDF verilerini hazırla
            $data = [
                'customer' => $customer,
                'transactions' => $transactions,
                'startDate' => Carbon::parse($startDate)->format('d.m.Y'),
                'endDate' => Carbon::parse($endDate)->format('d.m.Y'),
                'generatedDate' => Carbon::now()->format('d.m.Y H:i'),
                'openingBalance' => $openingBalance,
                'allHistory' => $allHistory
            ];
            
            // PDF oluştur
            $pdf = Pdf::loadView('pdf.transaction-pdf', $data);
            
            // PDF ayarları
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultFont', 'DejaVu Sans');
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'customer_id' => $customerId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Belirtilen tarihten önceki toplam bakiyeyi hesaplar
     */
    private function calculateOpeningBalance(Customer $customer, string $date): float
    {
        // Tarihten önceki tüm işlemleri al
        $previousTransactions = $customer->transactions()
            ->where('date', '<', $date)
            ->get();
            
        $balance = 0;
        
        foreach ($previousTransactions as $transaction) {
            $type = strtolower($transaction->type);
            $amount = floatval($transaction->amount);
            
            if ($type === 'borç') {
                $balance += $amount; // Borç artırır (Pozitif)
            } elseif ($type === 'ödeme') {
                $balance -= $amount; // Ödeme azaltır (Negatif)
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
