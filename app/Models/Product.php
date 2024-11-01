<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function salesProducts()
    {
        return $this->hasMany(SalesProduct::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sales::class, 'sales_products')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
