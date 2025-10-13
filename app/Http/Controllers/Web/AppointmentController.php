<?php

namespace App\Http\Controllers\Web;

use App\Events\AppointmentCreated;
use App\Events\AppointmentCancelled;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Rules\SlotBelongsToDoctorRule;
use App\Rules\SlotNotInPastRule;
use App\Rules\SlotNotTakenRule;
use App\Services\QueueService;
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

        $qs = app(QueueService::class);
        $upcoming->transform(function ($a) use ($qs) {
            $a->queue_position = $qs->position($a);
            return $a;
        });

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
            'doctor_id'    => ['required','integer','exists:doctors,id'],
            'specialty_id' => ['required','integer','exists:specialties,id'],
            'slot_start'   => ['required','date'],
            'slot_len'     => ['nullable','integer','min:5','max:180'],
        ]);

        $doctorId  = (int) $request->doctor_id;
        $specId    = (int) $request->specialty_id;
        $slotStart = CarbonImmutable::parse($request->slot_start, config('app.timezone'));


        $doctor = Doctor::with('specialties:id')->findOrFail($doctorId);
        $hasSpec = $doctor->specialties->contains('id', $specId);
        if (!$hasSpec) {
            return back()->withErrors(['specialty_id' => 'У врача нет выбранной специальности.'])->withInput();
        }

        $request->validate([
            'slot_start' => [
                new SlotBelongsToDoctorRule($doctorId),
                new SlotNotInPastRule(),
                new SlotNotTakenRule($doctorId, $slotStart),
            ],
        ]);

        $user = $request->user();

        try {
            $appt = DB::transaction(function () use ($user, $doctorId, $specId, $slotStart, $request, $doctor) {
                $slotLen = $request->integer('slot_len') ?: ((int) $doctor->avg_duration_min ?: 15);

                $appointment = Appointment::create([
                    'patient_id'    => $user->id,
                    'doctor_id'     => $doctorId,
                    'specialty_id'  => $specId,
                    'slot_start'    => $slotStart,
                    'slot_len_min'  => $slotLen,
                    'status'        => 'pending',
                    'ticket_no'     => $this->generateTicket($doctorId, $slotStart),
                    'complaint'     => (string) $request->input('complaint', ''),
                ]);

                event(new AppointmentCreated($appointment));

                return $appointment;
            });
        }  catch (\Throwable $e) {
            \Log::warning('Appointment store fail', [
                'doctor_id' => $doctorId,
                'slot_start'=> (string)$slotStart,
                'msg'       => $e->getMessage(),
            ]);
            return back()->withErrors(['slot_start' => 'Слот уже занят или недоступен.'])->withInput();
        }

        return redirect()->route('appointments.mine')->with('ok', 'Вы записаны.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        if ($appointment->status === 'cancelled') {
            return back()->with('ok', 'Запись уже отменена.');
        }

        $limitMin = 10;
        if (now()->diffInMinutes($appointment->slot_start, false) < $limitMin) {
            return back()->withErrors([
                'appointment' => "Отменить запись можно не позднее чем за {$limitMin} минут."
            ]);
        }

        DB::transaction(function () use ($appointment) {
            $a = Appointment::whereKey($appointment->id)->lockForUpdate()->first();
            if ($a->status !== 'cancelled') {
                $a->update([
                    'status' => 'cancelled',
                    'late_cancel' => false,
                ]);
                
                event(new AppointmentCancelled($a));
            }
        });

        return back()->with('ok', 'Запись отменена.');
    }

    private function generateTicket(int $doctorId, \DateTimeInterface $slotStart): string
    {
        return sprintf('D%03d-%s', $doctorId, date('ymd-Hi', $slotStart->getTimestamp()));
    }
}
