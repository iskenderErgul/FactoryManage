<?php

namespace App\Domains\PacsEntry\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacsEntriesLog extends Model
{
    use HasFactory;

    protected $fillable = ['pacs_entry_id', 'user_id', 'action', 'changes', 'created_at', 'updated_at'];

    public function pacsEntry(): BelongsTo
    {
        return $this->belongsTo(PacsEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
