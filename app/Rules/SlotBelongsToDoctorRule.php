<?php

namespace App\Rules;

use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SlotBelongsToDoctorRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __construct(private int $doctorId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dt = CarbonImmutable::parse($value);
        $sch = Schedule::query()
            ->where('doctor_id', $this->doctorId)
            ->whereDate('date', $dt->toDateString())
            ->first();

        if (!$sch || $sch->is_closed) {
            $fail('Для выбранной даты у врача нет активного расписания.');
            return;
        }
    }
}
