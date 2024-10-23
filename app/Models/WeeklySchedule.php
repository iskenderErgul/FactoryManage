<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'week',
        'start_date',
        'end_date',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function logs()
    {
        return $this->hasMany(WeeklyScheduleLog::class);
    }
}
