<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
use App\Events\AppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders to patients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending appointment reminders...');

        // Находим записи, которые начинаются через 10 минут
        $reminderTime = now()->addMinutes(10);
        $tolerance = 2; // Допуск в 2 минуты

        $appointments = Appointment::query()
            ->with(['patient', 'doctor.user', 'doctor.specialty'])
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CHECKED_IN])
            ->whereBetween('slot_start', [
                $reminderTime->copy()->subMinutes($tolerance),
                $reminderTime->copy()->addMinutes($tolerance)
            ])
            ->where('reminder_sent', false)
            ->get();

        $sentCount = 0;

        foreach ($appointments as $appointment) {
            try {
                // Отправляем уведомление пациенту
                $this->sendReminderToPatient($appointment);
                
                // Отправляем уведомление врачу
                $this->sendReminderToDoctor($appointment);
                
                // Отмечаем, что напоминание отправлено
                $appointment->update(['reminder_sent' => true]);
                
                $sentCount++;
                
                $this->line("Reminder sent for appointment #{$appointment->id} - {$appointment->patient->name}");
                
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for appointment #{$appointment->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$sentCount} reminders");
        
        return Command::SUCCESS;
    }

    private function sendReminderToPatient(Appointment $appointment)
    {
        // Создаем уведомление в базе данных
        $appointment->patient->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'appointment_reminder',
            'data' => [
                'appointment_id' => $appointment->id,
                'doctor_name' => $appointment->doctor->user->name,
                'specialty' => $appointment->doctor->specialty->name,
                'slot_start' => $appointment->slot_start->format('H:i'),
                'ticket_no' => $appointment->ticket_no,
                'message' => "Через 10 минут ваша очередь к врачу {$appointment->doctor->user->name}",
            ],
            'read_at' => null,
        ]);

        // Отправляем real-time уведомление
        event(new AppointmentReminder($appointment, 'patient'));
    }

    private function sendReminderToDoctor(Appointment $appointment)
    {
        // Создаем уведомление врачу
        $appointment->doctor->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'appointment_reminder',
            'data' => [
                'appointment_id' => $appointment->id,
                'patient_name' => $appointment->patient->name,
                'slot_start' => $appointment->slot_start->format('H:i'),
                'ticket_no' => $appointment->ticket_no,
                'message' => "Через 10 минут прием пациента {$appointment->patient->name}",
            ],
            'read_at' => null,
        ]);

        // Отправляем real-time уведомление
        event(new AppointmentReminder($appointment, 'doctor'));
    }
}
