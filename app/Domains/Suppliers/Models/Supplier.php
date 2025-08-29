<?php

namespace App\Domains\Suppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'supplier_address',
        'debt',
    ];

    protected $appends = ['calculated_debt'];

    public function transactions(): HasMany
    {
        return $this->hasMany(SupplierTransaction::class);
    }

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }

    /**
     * Transaction'lardan hesaplanan toplam borç
     */
    public function getCalculatedDebtAttribute(): float
    {
        $totalDebt = 0;

        foreach ($this->transactions as $transaction) {
            if (strtolower($transaction->type) === 'borç') {
                $totalDebt += $transaction->amount;  // Tedarik aldık, borç arttı
            } elseif (strtolower($transaction->type) === 'ödeme') {
                $totalDebt -= $transaction->amount;  // Ödeme yaptık, borç azaldı
            }
        }

        return $totalDebt;
    }
}
