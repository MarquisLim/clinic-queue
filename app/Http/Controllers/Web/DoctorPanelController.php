<?php

namespace App\Http\Controllers\Web;

use App\Events\AppointmentStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StatusLog;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DoctorPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$request->user()->isDoctor()) {
                abort(403, 'Доступ только для врачей');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            abort(404, 'Профиль врача не найден');
        }

        $appointments = Appointment::query()
            ->with(['patient:id,name', 'specialty:id,name'])
            ->todayForDoctor($doctor->id)
            ->whereIn('status', ['pending', 'checked_in', 'in_progress'])
            ->orderBy('slot_start')
            ->get();

        $queueService = app(QueueService::class);
        $appointments->transform(function ($appointment) use ($queueService) {
            $appointment->queue_position = $queueService->position($appointment);
            return $appointment;
        });

        return Inertia::render('Doctor/Panel', [
            'doctor' => $doctor->load('user'),
            'appointments' => $appointments,
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        $doctor = $user->doctor;

        if (!$doctor || $appointment->doctor_id !== $doctor->id) {
            abort(403, 'Нет доступа к этой записи');
        }

        $request->validate([
            'status' => 'required|in:checked_in,in_progress,done',
        ]);

        $newStatus = $request->status;
        $oldStatus = $appointment->status;

        if ($oldStatus === $newStatus) {
            return back()->with('ok', 'Статус уже установлен');
        }

        DB::transaction(function () use ($appointment, $oldStatus, $newStatus, $user) {
            $appointment->update([
                'status' => $newStatus,
                'started_at' => $newStatus === 'in_progress' ? now() : $appointment->started_at,
                'finished_at' => $newStatus === 'done' ? now() : $appointment->finished_at,
            ]);

            // Log status change
            StatusLog::create([
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_at' => now(),
                'meta' => [
                    'changed_by' => 'doctor',
                    'doctor_id' => $user->doctor->id,
                ],
            ]);

            // Dispatch event
            event(new AppointmentStatusChanged($appointment, $oldStatus, $newStatus));
        });

        $statusMessages = [
            'checked_in' => 'Пациент отмечен как прибывший',
            'in_progress' => 'Приём начат',
            'done' => 'Приём завершён',
        ];

        return back()->with('ok', $statusMessages[$newStatus] ?? 'Статус обновлён');
    }
}

