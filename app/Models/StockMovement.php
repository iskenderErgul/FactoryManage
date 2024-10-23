<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function logs()
    {
        return $this->hasMany(StockMovementsLog::class);
    }
}
