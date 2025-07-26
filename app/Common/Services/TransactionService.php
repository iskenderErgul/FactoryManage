<?php

namespace App\Common\Services;

use App\Domains\Customer\Models\Transaction;

class TransactionService
{
    public function updateTransaction( $sale, $totalAmount, $paymentType, $partialPayment): void
    {

        $transaction = Transaction::where('sale_id', $sale->id)->first();
        if (!$transaction) {
            return;
        }

        if ($paymentType === 'pesin') {
            $transaction->update([
                'amount' => 0,
                'date' => $sale->sale_date,
                'type' => 'ödeme',
                'description' => 'Peşin ödeme ile satış işlemi tamamlandı.',
            ]);
        } elseif ($paymentType === 'borc') {
            $transaction->update([
                'amount' => $totalAmount,
                'date' => $sale->sale_date,
                'type' => 'borç',
                'description' => 'Borç ile satış işlemi. Toplam borç: ' . number_format($totalAmount, 2) . ' TL',
            ]);
        } elseif ($paymentType === 'kismi') {
            $remainingDebt = $totalAmount - $partialPayment;

            $transaction->update([
                'amount' => $remainingDebt,
                'date' => $sale->sale_date,
                'type' => 'borç',
                'description' => 'Kısmi ödeme yapıldı. Ödenen: ' . number_format($partialPayment, 2) . ' TL, Kalan borç: ' . number_format($remainingDebt, 2) . ' TL',
            ]);
        }
    }

    //TODO Bu fonksiyon çok az  yanlış
    public function createTransaction( $sale, $totalAmount, $paymentType, $partialPayment): void
    {
        if ($paymentType === 'pesin') {
            Transaction::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'type' => 'ödeme',
                'date' => now(),
                'amount' => 0,
                'description' => 'Peşin ödeme ile satış işlemi tamamlandı.',
            ]);
        } elseif ($paymentType === 'borc') {
            Transaction::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'type' => 'borç',
                'date' => now(),
                'amount' => $totalAmount,
                'description' => 'Borç ile satış işlemi. Toplam borç: ' . number_format($totalAmount, 2) . ' TL',
            ]);
        } elseif ($paymentType === 'kismi') {
            $remainingDebt = $totalAmount - $partialPayment;

            Transaction::create([
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'type' => 'borç',
                'date' => now(),
                'amount' => $remainingDebt,
                'description' => 'Kısmi ödeme yapıldı. Ödenen: ' . number_format($partialPayment, 2) . ' TL, Kalan borç: ' . number_format($remainingDebt, 2) . ' TL',
            ]);
        }
    }

}
