<?php

namespace App\Domains\Production\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'action',
        'changes',
        'user_id'
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public  function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
