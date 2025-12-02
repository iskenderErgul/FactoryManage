<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tedarikçi Cari Hesap Ekstresi</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #333; }
        @page { margin: 15mm; size: A4 portrait; }
        
        .header { border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .company-title { font-size: 14pt; font-weight: bold; color: #2c3e50; }
        .report-title { font-size: 12pt; font-weight: bold; text-align: center; margin: 10px 0; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }
        .label { font-weight: bold; color: #555; }
        
        .trans-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .trans-table th { background: #f1f2f6; padding: 8px; border: 1px solid #ddd; font-size: 8pt; }
        .trans-table td { padding: 6px; border: 1px solid #ddd; font-size: 8pt; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .debt { color: #c0392b; } /* Borç - Kırmızı */
        .credit { color: #27ae60; } /* Alacak - Yeşil */
        
        .summary-box { float: right; width: 40%; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td style="width: 60%">
                    <div class="company-title">ÖZ ERGÜL PLASTİK</div>
                    <div>Organize Sanayi Bölgesi</div>
                </td>
                <td style="width: 40%; text-align: right">
                    <div>Tarih: {{ $generatedDate }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">TEDARİKÇİ CARİ HESAP EKSTRESİ</div>

    <table class="info-table">
        <tr>
            <td style="width: 60%">
                <div class="label">TEDARİKÇİ:</div>
                <div style="font-size: 11pt; font-weight: bold">{{ $supplier->supplier_name }}</div>
                <div>{{ $supplier->supplier_address ?? '-' }}</div>
                <div>{{ $supplier->supplier_phone ?? '-' }}</div>
            </td>
            <td style="width: 40%">
                <div class="label">DÖNEM:</div>
                @if($allHistory)
                    <div>Tüm Geçmiş İşlemler</div>
                @else
                    <div>{{ $startDate }} - {{ $endDate }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="trans-table">
        <thead>
            <tr>
                <th style="width: 12%">Tarih</th>
                <th style="width: 38%">Açıklama</th>
                <th style="width: 10%">İşlem</th>
                <th style="width: 13%">Borç</th>
                <th style="width: 13%">Alacak</th>
                <th style="width: 14%">Bakiye</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $openingBalance;
                $totalDebt = 0;
                $totalCredit = 0;
            @endphp

            <!-- Devreden Bakiye Satırı -->
            @if(!$allHistory && $openingBalance != 0)
            <tr style="background-color: #fff3cd">
                <td class="text-center">{{ $startDate }}</td>
                <td class="font-bold">DEVREDEN BAKİYE</td>
                <td class="text-center">-</td>
                <td class="text-right">
                    @if($openingBalance > 0) {{ App\Common\Services\SupplierPdfService::formatAmount($openingBalance) }} @else - @endif
                </td>
                <td class="text-right">
                    @if($openingBalance < 0) {{ App\Common\Services\SupplierPdfService::formatAmount(abs($openingBalance)) }} @else - @endif
                </td>
                <td class="text-right font-bold {{ $openingBalance > 0 ? 'debt' : ($openingBalance < 0 ? 'credit' : '') }}">
                    {{ App\Common\Services\SupplierPdfService::formatAmount(abs($openingBalance)) }} 
                    {{ $openingBalance > 0 ? '(B)' : ($openingBalance < 0 ? '(A)' : '') }}
                </td>
            </tr>
            @endif

            @foreach($transactions as $transaction)
                @php
                    $type = strtolower($transaction->type);
                    $amount = floatval($transaction->amount);
                    
                    $isDebt = ($type === 'borç');
                    $isCredit = ($type === 'ödeme');
                    
                    if ($isDebt) {
                        $runningBalance += $amount;
                        $totalDebt += $amount;
                    } elseif ($isCredit) {
                        $runningBalance -= $amount;
                        $totalCredit += $amount;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d.m.Y') }}</td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                    <td class="text-center">{{ $isDebt ? 'Borç' : 'Ödeme' }}</td>
                    
                    <!-- Borç Sütunu -->
                    <td class="text-right">
                        @if($isDebt) {{ App\Common\Services\SupplierPdfService::formatAmount($amount) }} @else - @endif
                    </td>
                    
                    <!-- Alacak Sütunu -->
                    <td class="text-right">
                        @if($isCredit) {{ App\Common\Services\SupplierPdfService::formatAmount($amount) }} @else - @endif
                    </td>
                    
                    <!-- Bakiye Sütunu -->
                    <td class="text-right font-bold {{ $runningBalance > 0 ? 'debt' : ($runningBalance < 0 ? 'credit' : '') }}">
                        {{ App\Common\Services\SupplierPdfService::formatAmount(abs($runningBalance)) }} 
                        {{ $runningBalance > 0 ? '(B)' : ($runningBalance < 0 ? '(A)' : '') }}
                    </td>
                </tr>
            @endforeach
            
            @if($transactions->isEmpty() && $openingBalance == 0)
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px">Bu dönemde işlem bulunmamaktadır.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary-box">
        <div style="border-bottom: 1px solid #ddd; margin-bottom: 5px; font-weight: bold">GENEL TOPLAM</div>
        <table style="width: 100%">
            <tr>
                <td>Toplam Borç:</td>
                <td class="text-right debt">{{ App\Common\Services\SupplierPdfService::formatAmount($totalDebt + ($openingBalance > 0 ? $openingBalance : 0)) }} TL</td>
            </tr>
            <tr>
                <td>Toplam Alacak:</td>
                <td class="text-right credit">{{ App\Common\Services\SupplierPdfService::formatAmount($totalCredit + ($openingBalance < 0 ? abs($openingBalance) : 0)) }} TL</td>
            </tr>
            <tr style="font-weight: bold; font-size: 11pt; border-top: 1px solid #999">
                <td>NET BAKİYE:</td>
                <td class="text-right {{ $runningBalance > 0 ? 'debt' : ($runningBalance < 0 ? 'credit' : '') }}">
                    {{ App\Common\Services\SupplierPdfService::formatAmount(abs($runningBalance)) }} TL 
                    {{ $runningBalance > 0 ? '(BORÇ)' : ($runningBalance < 0 ? '(ALACAK)' : '') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Sayfa <span class="page-number"></span>
    </div>
</body>
</html>
