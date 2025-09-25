<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

class SlotService
{
    /**
     * Возвращает массив слотов для врача на дату:
     * - slots: список [{start:"10:00", iso:"2025-09-25T10:00:00+05:00", available:true}]
     * - closed: bool, reason?: string
     */
    public function forDoctorDate(Doctor $doctor, string $date): array
    {
        /** @var Schedule|null $sch */
        $sch = $doctor->schedules()
            ->whereDate('date', $date)
            ->first();

        if (!$sch) {
            return ['slots' => [], 'closed' => true, 'closed_reason' => 'Нет расписания'];
        }
        if ($sch->is_closed) {
            return ['slots' => [], 'closed' => true, 'closed_reason' => $sch->closed_reason ?: 'День закрыт'];
        }

        $slotLen = (int) $sch->slot_len_min;
        $start   = CarbonImmutable::parse($date.' '.$sch->start_time);
        $end     = CarbonImmutable::parse($date.' '.$sch->end_time);

        // Перерывы
        $breaks = collect($sch->breaks ?? [])
            ->map(fn($b) => [
                'start' => CarbonImmutable::parse($date.' '.$b['start']),
                'end'   => CarbonImmutable::parse($date.' '.$b['end']),
            ]);

        // Уже занятые слоты
        $taken = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('slot_start', $date)
            ->whereIn('status', ['pending','checked_in','in_progress'])
            ->pluck('slot_start')
            ->map(fn($dt) => CarbonImmutable::parse($dt)->format('H:i'))
            ->all();

        $period = CarbonPeriod::create($start, "{$slotLen} minutes", $end->subMinutes($slotLen));
        $now    = now();

        $slots = [];
        foreach ($period as $ts) {
            $inBreak = $breaks->contains(fn($br) => $ts >= $br['start'] && $ts < $br['end']);
            if ($inBreak) continue;

            $hhmm = $ts->format('H:i');
            $iso  = $ts->toIso8601String();

            $available = !in_array($hhmm, $taken, true) && $ts > $now;

            $slots[] = [
                'start'     => $hhmm,
                'iso'       => $iso,
                'available' => $available,
            ];
        }

        return ['slots' => $slots, 'closed' => false];
    }
}
