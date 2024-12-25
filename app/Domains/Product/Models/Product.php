<?php

namespace App\Domains\Product\Models;

use App\Domains\Sales\Models\Sales;
use App\Domains\Sales\Models\SalesProduct;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_type',
        'product_photo',
        'description',
        'production_cost',
        'stock_quantity',
    ];

    public function salesProducts(): HasMany
    {
        return $this->hasMany(SalesProduct::class);
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sales::class, 'sales_products')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function getTotalPriceAttribute(): float|int
    {
        return $this->pivot->quantity * $this->pivot->price;
    }

    /**
     * Bir ürün birden fazla siparişte yer alabilir.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
