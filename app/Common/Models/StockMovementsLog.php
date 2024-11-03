<?php

namespace App\Common\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_movement_id',
        'action',
        'changes',
        'user_id',

    ];

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
