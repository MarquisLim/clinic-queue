<?php

namespace App\Rules;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SlotNotTakenRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function __construct(private int $doctorId, private \DateTimeInterface $slotStart) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Appointment::query()
            ->where('doctor_id', $this->doctorId)
            ->where('slot_start', CarbonImmutable::parse($value))
            ->whereIn('status', ['pending','checked_in','in_progress'])
            ->exists();

        if ($exists) {
            $fail('Slot is already taken.');
        }
    }
}
