<?php

namespace App\Domains\Customer\Models;

use App\Domains\Sales\Models\Sales;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'debt',
        'address',
    ];

    protected $appends = ['calculated_debt'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CustomersLog::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class); // Bir müşteri birden fazla tedarikçiye sahip olabilir
    }

    /**
     * Transaction'lardan hesaplanan toplam borç (varsayılan: son 3 ay)
     */
    public function getCalculatedDebtAttribute(): float
    {
        return $this->calculateDebtForPeriod(3); // Varsayılan 3 ay
    }

    /**
     * Belirtilen dönem için borç hesaplama
     * 
     * @param int $months Kaç aylık dönem (0 = tüm geçmiş)
     * @return float
     */
    public function calculateDebtForPeriod(int $months = 3): float
    {
        $totalDebt = 0;
        
        // Eğer 0 ise tüm geçmişi al, değilse belirtilen ay kadar geriye git
        $query = $this->transactions();
        
        if ($months > 0) {
            $startDate = Carbon::now()->subMonths($months);
            $query = $query->where('date', '>=', $startDate);
        }
        
        $transactions = $query->get();

        foreach ($transactions as $transaction) {
            if (strtolower($transaction->type) === 'borç') {
                $totalDebt += $transaction->amount;  // Müşteriden alacağımız, borç arttı
            } elseif (strtolower($transaction->type) === 'ödeme') {
                $totalDebt -= $transaction->amount;  // Müşteri ödeme yaptı, borç azaldı
            }
        }

        return $totalDebt;
    }

    /**
     * Dönemsel borç hesaplama seçenekleri
     */
    public function getDebtForAllPeriods(): array
    {
        return [
            '3_months' => [
                'label' => 'Son 3 Ay',
                'value' => $this->calculateDebtForPeriod(3)
            ],
            '6_months' => [
                'label' => 'Son 6 Ay', 
                'value' => $this->calculateDebtForPeriod(6)
            ],
            '1_year' => [
                'label' => 'Son 1 Yıl',
                'value' => $this->calculateDebtForPeriod(12)
            ],
            '3_years' => [
                'label' => 'Son 3 Yıl',
                'value' => $this->calculateDebtForPeriod(36)
            ],
            'all_time' => [
                'label' => 'Tüm Geçmiş',
                'value' => $this->calculateDebtForPeriod(0)
            ]
        ];
    }
}
