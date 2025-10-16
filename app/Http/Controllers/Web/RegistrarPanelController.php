<?php

namespace App\Http\Controllers\Web;

use App\Events\AppointmentStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\StatusLog;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegistrarPanelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$request->user()->isRegistrar() && !$request->user()->isAdmin()) {
                abort(403, 'Доступ только для регистраторов и администраторов');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $doctors = Doctor::query()
            ->with(['user:id,name', 'specialty:id,name'])
            ->active()
            ->get();

        $queueService = app(QueueService::class);

        // Получаем очереди для каждого врача
        $doctors->transform(function ($doctor) use ($queueService) {
            $appointments = Appointment::query()
                ->with(['patient:id,name'])
                ->todayForDoctor($doctor->id)
                ->whereIn('status', ['pending', 'checked_in', 'in_progress'])
                ->orderBy('slot_start')
                ->get();

            $appointments->transform(function ($appointment) use ($queueService) {
                $appointment->queue_position = $queueService->position($appointment);
                return $appointment;
            });

            $doctor->appointments = $appointments;
            $doctor->queue_count = $appointments->count();
            $doctor->current_patient = $appointments->where('status', 'in_progress')->first();

            return $doctor;
        });

        return Inertia::render('Registrar/Panel', [
            'doctors' => $doctors,
        ]);
    }

    public function checkIn(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['appointment' => 'Пациент уже отмечен как прибывший или приём уже начат']);
        }

        DB::transaction(function () use ($appointment, $user) {
            $oldStatus = $appointment->status;
            $appointment->update(['status' => 'checked_in']);

            // Логируем изменение статуса
            StatusLog::create([
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'from_status' => $oldStatus,
                'to_status' => 'checked_in',
                'changed_at' => now(),
                'meta' => [
                    'changed_by' => 'registrar',
                    'registrar_id' => $user->id,
                ],
            ]);

            // Отправляем событие
            event(new AppointmentStatusChanged($appointment, $oldStatus, 'checked_in'));
        });

        return back()->with('ok', 'Пациент отмечен как прибывший');
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        if ($appointment->status === 'cancelled') {
            return back()->with('ok', 'Запись уже отменена');
        }

        if ($appointment->status === 'done') {
            return back()->withErrors(['appointment' => 'Нельзя отменить завершённую запись']);
        }

        DB::transaction(function () use ($appointment, $user) {
            $oldStatus = $appointment->status;
            $appointment->update([
                'status' => 'cancelled',
                'late_cancel' => false,
            ]);

            // Логируем изменение статуса
            StatusLog::create([
                'appointment_id' => $appointment->id,
                'user_id' => $user->id,
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'changed_at' => now(),
                'meta' => [
                    'changed_by' => 'registrar',
                    'registrar_id' => $user->id,
                    'reason' => $request->input('reason', 'Отменено регистратором'),
                ],
            ]);

            // Отправляем событие
            event(new AppointmentStatusChanged($appointment, $oldStatus, 'cancelled'));
        });

        return back()->with('ok', 'Запись отменена');
    }

    public function statusLogs(Request $request)
    {
        $logs = StatusLog::query()
            ->with(['appointment.patient:id,name', 'appointment.doctor.user:id,name', 'user:id,name'])
            ->orderByDesc('changed_at')
            ->limit(100)
            ->get();

        return Inertia::render('Registrar/StatusLogs', [
            'logs' => $logs,
        ]);
    }
}

