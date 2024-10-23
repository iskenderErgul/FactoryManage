<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'start_time',
        'user_id',
        'schedule_id',
        'date',
        'end_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(ShiftTemplate::class);
    }

    public function schedule()
    {
        return $this->belongsTo(WeeklySchedule::class);
    }

    public function logs()
    {
        return $this->hasMany(ShiftsLog::class);
    }
}
