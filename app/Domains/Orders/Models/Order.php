<?php

namespace App\Domains\Orders\Models;

use App\Domains\Customer\Models\Customer;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    protected  $guarded = [];

    /**
     * Bir siparişin birçok ürünü olabilir.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Order modelinde
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id'); // 'customer_id' foreign key ile ilişkiyi kuruyoruz
    }
}
