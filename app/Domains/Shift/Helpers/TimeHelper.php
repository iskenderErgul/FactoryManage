<?php

namespace App\Domains\Shift\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    public static function calculateDuration(string $start, string $end): int
    {
        $startTime = Carbon::createFromFormat('H:i:s', $start);
        $endTime = Carbon::createFromFormat('H:i:s', $end);

        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        return $startTime->diffInMinutes($endTime);
    }
}
