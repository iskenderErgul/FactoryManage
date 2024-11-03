<?php

namespace App\Domains\Sales\Models;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id' ,
        'product_id' ,
        'quantity' ,
        'price' ,
    ];

    protected $table = 'sales_products';

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
