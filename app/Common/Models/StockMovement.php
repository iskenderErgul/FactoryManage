<?php

namespace App\Common\Models;

use App\Domains\Product\Models\Product;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'related_process',
        'movement_date',
    ];


    protected $casts = [
        'movement_type' => 'string',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(StockMovementsLog::class);
    }

     public function user(): BelongsTo
     {
         return $this->belongsTo(User::class);
     }

     public function product(): BelongsTo
     {
         return $this->belongsTo(Product::class);
     }
}
