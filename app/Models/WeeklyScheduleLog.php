<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyScheduleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_schedule_id',
        'action',
        'changes',
    ];

    public function weeklySchedule()
    {
        return $this->belongsTo(WeeklySchedule::class);
    }
}
