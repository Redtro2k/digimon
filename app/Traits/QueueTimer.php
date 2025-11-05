<?php

namespace App\Traits;

use Carbon\Carbon;

trait QueueTimer
{
    //
    public function calculateDuration($startTime): string
    {
        $start = Carbon::parse($startTime);
        $end = now();
        $diff = $end->diff($start);

        return sprintf("%02d:%02d:%02d", $diff->h, $diff->i, $diff->s);
    }

}
