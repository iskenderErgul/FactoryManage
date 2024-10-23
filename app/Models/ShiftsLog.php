<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'action',
        'changes',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
