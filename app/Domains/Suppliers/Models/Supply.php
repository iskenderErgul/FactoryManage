<?php

namespace App\Domains\Suppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'supplied_product',
        'supplied_product_quantity',
        'supplied_product_price',
        'supply_date',
        'payment_method',
        'paid_amount',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
