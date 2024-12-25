<?php

namespace App\Domains\Orders\Models;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    use HasFactory;

    protected $table = 'order_products';

    protected  $guarded = [];

    /**
     * Ara tablonun sipariş ile ilişkisi.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Ara tablonun ürün ile ilişkisi.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
