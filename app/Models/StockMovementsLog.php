<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovementsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_movement_id',
        'action',
        'changes',
    ];

    public function stockMovement()
    {
        return $this->belongsTo(StockMovement::class);
    }
}
