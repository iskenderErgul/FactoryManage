<?php

namespace App\Domains\Sales\Models;

use App\Domains\Customer\Models\Customer;
use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'sale_date',
        'payment_type',
        'paid_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sales_products')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
    // Accessor for total price
    public function toArray()
    {
        $array = parent::toArray();
        // Her bir ürün için total_price eklemek için
        $array['products'] = $this->products->map(function ($product) {
            // Ürün verilerini array'e çevir
            $productArray = $product->toArray();
            // Total price ekle
            $productArray['total_price'] = $product->total_price; // Accessor çağrısı
            return $productArray;
        });

        return $array;
    }

    public function salesProducts()
    {
        return $this->hasMany(SalesProduct::class);
    }

    public function logs()
    {
        return $this->hasMany(SalesLog::class);
    }
}
