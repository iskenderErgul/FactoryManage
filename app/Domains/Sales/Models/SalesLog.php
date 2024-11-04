<?php

namespace App\Domains\Sales\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesLog extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
