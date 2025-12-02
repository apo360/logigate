<?php

namespace App\Services;

use App\Models\ScheduledTask;

class RecurrenceService
{
// For now simple helpers; can be extended to use rrule library
public function computeNextFrom(ScheduledTask $task)
{
return $task->computeNextRun();
}
}