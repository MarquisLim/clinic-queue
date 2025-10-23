<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем специальность
        $specialty = Specialty::firstOrCreate([
            'name' => 'Терапевт'
        ]);

        // Создаем врача
        $doctorUser = User::firstOrCreate([
            'email' => 'doctor@test.com'
        ], [
            'name' => 'Доктор Иванов',
            'password' => bcrypt('password'),
        ]);
        $doctorUser->assignRole('doctor');

        $doctor = Doctor::firstOrCreate([
            'user_id' => $doctorUser->id
        ], [
            'speciality_id' => $specialty->id,
            'room' => '101',
            'avg_duration_min' => 30,
            'is_active' => true,
        ]);

        // Создаем пациента
        $patient = User::firstOrCreate([
            'email' => 'patient@test.com'
        ], [
            'name' => 'Пациент Петров',
            'password' => bcrypt('password'),
        ]);
        $patient->assignRole('patient');

        // Создаем тестовую запись на прием через 10 минут
        $appointment = Appointment::firstOrCreate([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'specialty_id' => $specialty->id,
            'slot_start' => now()->addMinutes(10),
            'slot_len_min' => 30,
            'status' => 'pending',
            'ticket_no' => 'D001-' . now()->format('ymd-Hi'),
            'reminder_sent' => false,
        ]);

        // Создаем тестовое уведомление
        $patient->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'appointment_reminder',
            'data' => [
                'appointment_id' => $appointment->id,
                'doctor_name' => $doctor->user->name,
                'specialty' => $specialty->name,
                'slot_start' => $appointment->slot_start->format('H:i'),
                'ticket_no' => $appointment->ticket_no,
                'message' => "Через 10 минут ваша очередь к врачу {$doctor->user->name}",
            ],
            'read_at' => null,
        ]);

        $this->command->info('Test data created successfully!');
        $this->command->info('Patient: patient@test.com / password');
        $this->command->info('Doctor: doctor@test.com / password');
        $this->command->info('Appointment scheduled for: ' . $appointment->slot_start->format('Y-m-d H:i:s'));
    }
}
