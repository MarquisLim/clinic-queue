<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\NotificationController;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Events\AppointmentReminder;
use Carbon\Carbon;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Тестовые API для уведомлений
Route::middleware('auth')->prefix('test')->group(function () {
    Route::get('/appointment-time', function () {
        $appointment = Appointment::where('patient_id', auth()->id())
            ->where('slot_start', '>', now())
            ->orderBy('slot_start')
            ->first();
            
        return response()->json([
            'time' => $appointment ? $appointment->slot_start->format('Y-m-d H:i:s') : 'Нет записей'
        ]);
    });

    Route::post('/send-reminder', function () {
        $appointment = Appointment::where('patient_id', auth()->id())
            ->where('slot_start', '>', now())
            ->orderBy('slot_start')
            ->first();
            
        if ($appointment) {
            // Создаем тестовое уведомление
            auth()->user()->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'appointment_reminder',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'doctor_name' => $appointment->doctor->user->name,
                    'specialty' => $appointment->doctor->specialty->name,
                    'slot_start' => $appointment->slot_start->format('H:i'),
                    'ticket_no' => $appointment->ticket_no,
                    'message' => "Тестовое напоминание: через 10 минут ваша очередь к врачу {$appointment->doctor->user->name}",
                ],
                'read_at' => null,
            ]);

            // Отправляем real-time уведомление
            event(new AppointmentReminder($appointment, 'patient'));
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'No appointment found'], 404);
    });

    Route::post('/create-appointment', function () {
        $specialty = Specialty::first();
        $doctor = Doctor::first();
        
        if (!$specialty || !$doctor) {
            return response()->json(['error' => 'No specialty or doctor found'], 404);
        }

        $appointment = Appointment::create([
            'patient_id' => auth()->id(),
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'slot_start' => now()->addMinutes(15),
            'slot_len_min' => 30,
            'status' => 'pending',
            'ticket_no' => 'TEST-' . now()->format('ymd-Hi'),
            'reminder_sent' => false,
        ]);

        return response()->json(['success' => true, 'appointment_id' => $appointment->id]);
    });
});
