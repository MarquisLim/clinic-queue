<?php

namespace App\Services;

use App\Models\Appointment;

class QueueService
{
    public function position(Appointment $appointment): int
    {
        return Appointment::query()
            ->where('doctor_id', $appointment->doctor_id)
            ->whereIn('status', ['pending','checked_in','in_progress'])
            ->where('slot_start', '<=', $appointment->slot_start)
            ->count();
    }
}
