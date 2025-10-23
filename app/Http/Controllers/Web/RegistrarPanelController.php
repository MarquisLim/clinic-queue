<?php

namespace App\Http\Controllers\Web;

use App\Events\AppointmentStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\StatusLog;
use App\Models\Schedule;
use App\Services\QueueService;
use App\Services\SlotService;
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

        // Get queue positions for each doctor
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

            // Log status change
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

            // Dispatch event
            event(new AppointmentStatusChanged($appointment, $oldStatus, 'checked_in'));
        });

        return back()->with('ok', 'Пациент отмечен как прибывший');
    }

    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        try {
            $user = $request->user();

            if ($appointment->status === 'cancelled') {
                return back()->with('ok', 'Запись уже отменена');
            }

            if ($appointment->status === 'done') {
                return back()->withErrors(['appointment' => 'Нельзя отменить завершённую запись']);
            }

            DB::transaction(function () use ($appointment, $user, $request) {
                $oldStatus = $appointment->status;
                $appointment->update([
                    'status' => 'cancelled',
                    'late_cancel' => false,
                ]);

                // Log status change
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

                // Dispatch event
                event(new AppointmentStatusChanged($appointment, $oldStatus, 'cancelled'));
            });

            return back()->with('ok', 'Запись отменена');
        } catch (\Exception $e) {
            \Log::error('Registrar appointment cancellation error', [
                'appointment_id' => $appointment->id,
                'registrar_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'appointment' => 'Произошла ошибка при отмене записи. Попробуйте еще раз.'
            ]);
        }
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

    /**
     * Получить доступные слоты для переназначения
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $appointment = Appointment::with(['doctor.specialty'])->findOrFail($request->appointment_id);
        
        // Get doctors with the same specialty
        $doctors = Doctor::with(['user', 'specialty'])
            ->where('specialty_id', $appointment->doctor->specialty_id)
            ->active()
            ->get();

        $slotService = app(SlotService::class);
        $availableSlots = [];

        foreach ($doctors as $doctor) {
            $slots = $slotService->getAvailableSlots($doctor->id, $request->date);
            if (!empty($slots)) {
                $availableSlots[] = [
                    'doctor' => $doctor,
                    'slots' => $slots
                ];
            }
        }

        return response()->json($availableSlots);
    }

    /**
     * Переназначить запись
     */
    public function rescheduleAppointment(Request $request, Appointment $appointment)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'slot_start' => 'required|date',
            'slot_end' => 'required|date|after:slot_start',
        ]);

        // Check if new slot is available
        $slotService = app(SlotService::class);
        $isAvailable = $slotService->isSlotAvailable(
            $request->doctor_id,
            $request->slot_start,
            $request->slot_end
        );

        if (!$isAvailable) {
            return response()->json(['error' => 'Выбранный слот недоступен'], 422);
        }

        // Check if new slot is not in the past
        if (now()->gt($request->slot_start)) {
            return response()->json(['error' => 'Нельзя переносить запись в прошлое'], 422);
        }

        // Check if new doctor has the same specialty
        $newDoctor = Doctor::findOrFail($request->doctor_id);
        if ($newDoctor->specialty_id !== $appointment->doctor->specialty_id) {
            return response()->json(['error' => 'Врач должен быть той же специальности'], 422);
        }

        DB::transaction(function () use ($appointment, $request) {
            // Update appointment
            $oldDoctor = $appointment->doctor;
            $oldSlotStart = $appointment->slot_start;
            $oldSlotEnd = $appointment->slot_end;

            $appointment->update([
                'doctor_id' => $request->doctor_id,
                'slot_start' => $request->slot_start,
                'slot_end' => $request->slot_end,
                'status' => 'pending', // Reset status
                'reminder_sent' => false, // Reset reminder flag
            ]);

            // Log change
            StatusLog::create([
                'appointment_id' => $appointment->id,
                'old_status' => $appointment->getOriginal('status'),
                'new_status' => 'pending',
                'changed_by' => auth()->id(),
                'reason' => 'Переназначение записи',
                'notes' => "Перенесено с {$oldDoctor->user->name} ({$oldSlotStart}) на {$newDoctor->user->name} ({$request->slot_start})"
            ]);

            // Dispatch event
            broadcast(new AppointmentStatusChanged($appointment, 'rescheduled'));
        });

        return response()->json(['message' => 'Запись успешно переназначена']);
    }
}

