<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftTemplatesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_template_id',
        'action',
        'changes',
    ];

    public function shiftTemplate()
    {
        return $this->belongsTo(ShiftTemplate::class);
    }
}
