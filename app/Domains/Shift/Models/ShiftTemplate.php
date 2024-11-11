<?php

namespace App\Domains\Shift\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->hasMany(Shift::class, 'template_id');
    }

}
