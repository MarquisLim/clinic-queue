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
        $specialties = [
            [
                'name' => 'Терапевт',
                'description' => 'Поможет, когда очень нужен доктор',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/male/45.png'
            ],
            [
                'name' => 'Педиатр',
                'description' => 'Быстрая помощь вашему ребёнку',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/female/32.png'
            ],
            [
                'name' => 'Хирург',
                'description' => 'Поможет при травмах и операциях',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/male/62.png'
            ],
            [
                'name' => 'Кардиолог',
                'description' => 'Забота о вашем сердце',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/female/66.png'
            ],
            [
                'name' => 'Невролог',
                'description' => 'Поддержка нервной системы',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/male/70.png'
            ],
            [
                'name' => 'Стоматолог',
                'description' => 'Здоровье и красота ваших зубов',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/female/12.png'
            ],
            [
                'name' => 'ЛОР',
                'description' => 'Поможет при заболеваниях уха, горла и носа',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/male/44.png'
            ],
            [
                'name' => 'Офтальмолог',
                'description' => 'Забота о вашем зрении и глазах',
                'image_url' => 'https://raw.githubusercontent.com/Ashwinvalento/cartoon-avatar/master/lib/images/female/21.png'
            ],
            [
                'name' => 'Ветеринар',
                'description' => 'Поможет вашему питомцу',
                'image_url' => 'https://img.icons8.com/external-flaticons-lineal-color-flat-icons/344/external-dog-animal-flaticons-lineal-color-flat-icons.png'
            ],
        ];

        foreach ($specialties as $spec) {
            Specialty::updateOrCreate(
                ['name' => $spec['name']], // ищем по имени
                [
                    'description' => $spec['description'],
                    'image_url'   => $spec['image_url'],
                ]
            );
        }
    }
}
