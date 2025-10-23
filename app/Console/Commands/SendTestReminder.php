<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Appointment;
use App\Events\AppointmentReminder;
use Illuminate\Console\Command;

class SendTestReminder extends Command
{
    protected $signature = 'test:reminder 
                            {user_id : ID пользователя}
                            {--appointment_id= : ID конкретной записи (опционально)}
                            {--message= : Кастомное сообщение (опционально)}
                            {--force : Принудительно отправить, даже если уже отправлялось}';
    
    protected $description = 'Отправить тестовое напоминание пользователю в любое время';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $appointmentId = $this->option('appointment_id');
        $customMessage = $this->option('message');
        $force = $this->option('force');
        
        // Find user
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Пользователь с ID {$userId} не найден");
            return 1;
        }

        $this->info("👤 Пользователь: {$user->name} (ID: {$user->id})");

        // Find appointment
        $appointment = null;
        
        if ($appointmentId) {
            // Specific appointment by ID
            $appointment = Appointment::find($appointmentId);
            if (!$appointment) {
                $this->error("❌ Запись с ID {$appointmentId} не найдена");
                return 1;
            }
            if ($appointment->patient_id != $userId) {
                $this->error("❌ Запись не принадлежит этому пользователю");
                return 1;
            }
        } else {
            // User's nearest appointment
            $appointment = Appointment::where('patient_id', $userId)
                ->where('slot_start', '>', now())
                ->orderBy('slot_start')
                ->first();
                
            if (!$appointment) {
                $this->error("❌ У пользователя нет предстоящих записей");
                return 1;
            }
        }

        $this->info("📅 Запись найдена:");
        $this->line("   ID: {$appointment->id}");
        $this->line("   Врач: {$appointment->doctor->user->name}");
        $this->line("   Специальность: {$appointment->doctor->specialty->name}");
        $this->line("   Время: {$appointment->slot_start->format('Y-m-d H:i')}");
        $this->line("   Билет: {$appointment->ticket_no}");
        $this->line("   Напоминание отправлено: " . ($appointment->reminder_sent ? 'Да' : 'Нет'));

        // Check if reminder was already sent
        if ($appointment->reminder_sent && !$force) {
            $this->warn("⚠️  Напоминание уже отправлялось для этой записи");
            if (!$this->confirm("Отправить повторно?")) {
                $this->info("Отменено");
                return 0;
            }
        }

        // Form message
        $message = $customMessage ?: "🔔 ТЕСТОВОЕ НАПОМИНАНИЕ: Через 10 минут ваша очередь к врачу {$appointment->doctor->user->name}";

        // Create notification
        $notification = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'appointment_reminder',
            'data' => [
                'appointment_id' => $appointment->id,
                'doctor_name' => $appointment->doctor->user->name,
                'specialty' => $appointment->doctor->specialty->name,
                'slot_start' => $appointment->slot_start->format('H:i'),
                'ticket_no' => $appointment->ticket_no,
                'message' => $message,
            ],
            'read_at' => null,
        ]);

        // Send real-time notification
        event(new AppointmentReminder($appointment, 'patient'));

        // Update reminder status (only if not forced)
        if (!$force) {
            $appointment->update(['reminder_sent' => true]);
        }

        $this->info("✅ Тестовое напоминание отправлено!");
        $this->line("   ID уведомления: {$notification->id}");
        $this->line("   Сообщение: {$message}");
        $this->line("   Время: " . now()->format('Y-m-d H:i:s'));
        $this->line("   Real-time событие отправлено через WebSocket");

        return 0;
    }
}
