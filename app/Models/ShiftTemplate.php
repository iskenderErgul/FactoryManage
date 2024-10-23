<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'duration',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function logs()
    {
        return $this->hasMany(ShiftTemplatesLog::class);
    }
}
