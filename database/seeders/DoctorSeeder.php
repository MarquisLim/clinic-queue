<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $user->id],
            ['room' => '101', 'avg_duration_min' => 15, 'is_active' => true]
        );

        $therapist = Specialty::firstOrCreate(['name' => 'Терапевт']);
        $lor       = Specialty::firstOrCreate(['name' => 'ЛОР']);

        $doctor->specialties()->syncWithoutDetaching([$therapist->id, $lor->id]);
    }
}
