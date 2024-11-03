<?php

namespace App\Domains\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'action',
        'changes',
        'user_id'
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }
}
