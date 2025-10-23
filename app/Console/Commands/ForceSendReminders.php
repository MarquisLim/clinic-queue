<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Events\AppointmentReminder;
use Illuminate\Console\Command;

class ForceSendReminders extends Command
{
    protected $signature = 'reminders:force 
                            {--minutes=10 : За сколько минут до приема отправлять}
                            {--status=pending,checked_in : Статусы записей для отправки}';
    
    protected $description = 'Принудительно отправить напоминания для всех подходящих записей';

    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $statuses = explode(',', $this->option('status'));
        
        $this->info("🔍 Поиск записей для отправки напоминаний...");
        $this->line("   Временной диапазон: {$minutes} минут до приема");
        $this->line("   Статусы: " . implode(', ', $statuses));

        // Find all appointments that should receive reminders
        $appointments = Appointment::query()
            ->with(['patient', 'doctor.user', 'doctor.specialty'])
            ->whereIn('status', $statuses)
            ->where('slot_start', '>', now())
            ->where('slot_start', '<=', now()->addMinutes($minutes + 5)) // +5 minutes tolerance
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("📭 Нет записей для отправки напоминаний");
            return 0;
        }

        $this->info("📋 Найдено записей: {$appointments->count()}");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($appointments as $appointment) {
            $timeToAppointment = now()->diffInMinutes($appointment->slot_start);
            
            $this->line("---");
            $this->line("Запись ID: {$appointment->id}");
            $this->line("Пациент: {$appointment->patient->name}");
            $this->line("Врач: {$appointment->doctor->user->name}");
            $this->line("Время: {$appointment->slot_start->format('Y-m-d H:i')}");
            $this->line("До приема: {$timeToAppointment} минут");
            $this->line("Напоминание отправлено: " . ($appointment->reminder_sent ? 'Да' : 'Нет'));

            // Check if reminder should be sent
            if ($appointment->reminder_sent) {
                $this->warn("   ⏭️  Пропущено (уже отправлялось)");
                $skippedCount++;
                continue;
            }

            if ($timeToAppointment > $minutes + 5) {
                $this->warn("   ⏭️  Пропущено (слишком рано)");
                $skippedCount++;
                continue;
            }

            try {
                // Send reminder to patient
                $this->sendReminderToPatient($appointment);
                
                // Send reminder to doctor
                $this->sendReminderToDoctor($appointment);
                
                // Mark as sent
                $appointment->update(['reminder_sent' => true]);
                
                $sentCount++;
                $this->info("   ✅ Напоминание отправлено");
                
            } catch (\Exception $e) {
                $this->error("   ❌ Ошибка: " . $e->getMessage());
            }
        }

        $this->info("📊 Результат:");
        $this->line("   Отправлено: {$sentCount}");
        $this->line("   Пропущено: {$skippedCount}");
        $this->line("   Всего обработано: " . ($sentCount + $skippedCount));

        return 0;
    }

    private function sendReminderToPatient(Appointment $appointment)
    {
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

        event(new AppointmentReminder($appointment, 'patient'));
    }

    private function sendReminderToDoctor(Appointment $appointment)
    {
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

        event(new AppointmentReminder($appointment, 'doctor'));
    }
}
