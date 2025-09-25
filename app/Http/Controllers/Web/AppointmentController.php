<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Rules\SlotBelongsToDoctorRule;
use App\Rules\SlotNotInPastRule;
use App\Rules\SlotNotTakenRule;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function mine(Request $request)
    {
        $user = $request->user();

        $upcoming = Appointment::query()
            ->with(['doctor.user:id,name'])
            ->where('patient_id', $user->id)
            ->whereIn('status', ['pending','checked_in','in_progress'])
            ->orderBy('slot_start')
            ->get();

        $history = Appointment::query()
            ->with(['doctor.user:id,name'])
            ->where('patient_id', $user->id)
            ->whereIn('status', ['done','cancelled'])
            ->orderByDesc('slot_start')
            ->limit(50)
            ->get();

        return Inertia::render('Appointments/Mine', [
            'upcoming' => $upcoming,
            'history'  => $history,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'  => ['required','integer','exists:doctors,id'],
            'slot_start' => ['required','date'], // ISO строка
            'slot_len'   => ['nullable','integer','min:5','max:180'],
        ]);

        $doctorId  = (int) $request->doctor_id;
        $slotStart = CarbonImmutable::parse($request->slot_start);

        // проверки через правила
        $request->validate([
            'slot_start' => [
                new SlotBelongsToDoctorRule($doctorId),
                new SlotNotInPastRule(),
                new SlotNotTakenRule($doctorId, $slotStart),
            ],
        ]);

        $user = $request->user();

        try {
            $appt = DB::transaction(function () use ($user, $doctorId, $slotStart, $request) {
                $slotLen = $request->integer('slot_len') ?:  (int) optional(
                    Doctor::find($doctorId)
                )->avg_duration_min ?: 15;

                return Appointment::create([
                    'patient_id'    => $user->id,
                    'doctor_id'     => $doctorId,
                    'slot_start'    => $slotStart,
                    'slot_len_min'  => $slotLen,
                    'status'        => 'pending',
                    'ticket_no'     => $this->generateTicket($doctorId, $slotStart),
                    'complaint'     => (string) $request->input('complaint', ''),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['slot_start' => 'Слот уже занят или недоступен.'])->withInput();
        }

        return redirect()->route('appointments.mine')->with('ok', 'Вы записаны.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $late = now()->diffInMinutes($appointment->slot_start, false) < 10;

        $appointment->update([
            'status' => 'cancelled',
            'late_cancel' => $late,
        ]);

        return back()->with('ok', 'Запись отменена.');
    }

    private function generateTicket(int $doctorId, \DateTimeInterface $slotStart): string
    {
        return sprintf('D%03d-%s', $doctorId, date('ymd-Hi', $slotStart->getTimestamp()));
    }
}
