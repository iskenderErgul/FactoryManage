<?php

namespace App\Domains\Shift\Models;

use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['shift_id', 'user_id'];

    // ShiftAssignment belongs to a Shift
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    // ShiftAssignment belongs to a User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function template(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class);
    }
}
