<?php

namespace App\Console\Commands;

use App\Events\AppointmentStatusChanged;
use App\Models\Appointment;
use Illuminate\Console\Command;

class TestBroadcasting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:broadcasting {appointment_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирует broadcasting событий';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointmentId = $this->argument('appointment_id');
        
        if ($appointmentId) {
            $appointment = Appointment::find($appointmentId);
            if (!$appointment) {
                $this->error("Запись с ID {$appointmentId} не найдена");
                return 1;
            }
        } else {
            $appointment = Appointment::first();
            if (!$appointment) {
                $this->error("Нет записей в базе данных");
                return 1;
            }
        }

        $this->info("Тестируем broadcasting для записи ID: {$appointment->id}");
        $this->info("Талон: {$appointment->ticket_no}");
        $this->info("Врач: {$appointment->doctor->user->name}");
        $this->info("Пациент: {$appointment->patient->name}");

        // Отправляем тестовое событие
        event(new AppointmentStatusChanged($appointment, 'pending', 'checked_in'));

        $this->info("✅ Событие отправлено!");
        $this->info("Проверьте панели врача и регистратора на наличие обновлений");

        return 0;
    }
}

