<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            ['name' => 'Иванов Иван',   'room' => '101', 'avg_duration_min' => 20, 'specialty' => 'Терапевт'],
            ['name' => 'Смирнова Анна', 'room' => '202', 'avg_duration_min' => 30, 'specialty' => 'Кардиолог'],
            ['name' => 'Петров Петр',   'room' => '303', 'avg_duration_min' => 15, 'specialty' => 'Хирург'],
        ];

        foreach ($doctors as $doc) {
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $doc['name'])) . '@clinic.local'],
                [
                    'name' => $doc['name'],
                    'password' => bcrypt('password'),
                ]
            );
            $user->assignRole('doctor');

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'room' => $doc['room'],
                    'avg_duration_min' => $doc['avg_duration_min'],
                    'is_active' => true,
                    'photo_url' => 'https://picsum.photos/seed/' . md5($doc['name']) . '/200/200',
                ]
            );

            $specialty = Specialty::where('name', $doc['specialty'])->first();
            if ($specialty) {
                $doctor->specialties()->syncWithoutDetaching([$specialty->id]);
            }

            for ($i = 0; $i < 5; $i++) {
                $date = Carbon::today()->addDays($i)->format('Y-m-d');
                Schedule::firstOrCreate(
                    ['doctor_id' => $doctor->id, 'date' => $date],
                    [
                        'start_time' => '09:00',
                        'end_time' => '17:00',
                        'slot_len_min' => $doc['avg_duration_min'],
                        'breaks' => [
                            ['start' => '12:00', 'end' => '13:00']
                        ],
                        'is_closed' => false,
                    ]
                );
            }
        }
    }
}
