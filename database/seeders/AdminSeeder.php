<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@clinic.local'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('123456zaza'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}

