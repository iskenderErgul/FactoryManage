<?php

namespace App\Domains\PacsEntry\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PacsEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'user_id', 'entry_type', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'entry_type' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PacsEntriesLog::class);
    }
}
