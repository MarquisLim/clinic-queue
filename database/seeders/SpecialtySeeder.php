<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Терапевт','ЛОР','Офтальмолог','Стоматолог'] as $name) {
            Specialty::firstOrCreate(['name' => $name]);
        }
    }
}
