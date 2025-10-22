<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RegistrarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrars = [
            [
                'name' => 'Регистратор Анна Петровна',
                'email' => 'registrar1@clinic.local',
                'password' => '123456zaza'
            ],
            [
                'name' => 'Регистратор Мария Ивановна',
                'email' => 'registrar2@clinic.local',
                'password' => '123456zaza'
            ],
        ];

        foreach ($registrars as $registrar) {
            $user = User::firstOrCreate(
                ['email' => $registrar['email']],
                [
                    'name' => $registrar['name'],
                    'password' => Hash::make($registrar['password']),
                ]
            );

            $user->assignRole('registrar');
        }
    }
}

