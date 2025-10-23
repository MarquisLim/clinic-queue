<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'Иванов Иван Иванович', 
                'room' => '101', 
                'avg_duration_min' => 20, 
                'specialty' => 'Терапевт',
                'photo_url' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Смирнова Анна Петровна', 
                'room' => '202', 
                'avg_duration_min' => 30, 
                'specialty' => 'Кардиолог',
                'photo_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Петров Петр Сергеевич', 
                'room' => '303', 
                'avg_duration_min' => 15, 
                'specialty' => 'Хирург',
                'photo_url' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Козлова Елена Владимировна', 
                'room' => '104', 
                'avg_duration_min' => 25, 
                'specialty' => 'Педиатр',
                'photo_url' => 'https://images.unsplash.com/photo-1594824388852-8a0b1b0b0b0b?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Морозов Дмитрий Александрович', 
                'room' => '205', 
                'avg_duration_min' => 35, 
                'specialty' => 'Невролог',
                'photo_url' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Волкова Мария Игоревна', 
                'room' => '306', 
                'avg_duration_min' => 20, 
                'specialty' => 'Стоматолог',
                'photo_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Соколов Андрей Николаевич', 
                'room' => '107', 
                'avg_duration_min' => 30, 
                'specialty' => 'ЛОР',
                'photo_url' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
            [
                'name' => 'Новикова Ольга Сергеевна', 
                'room' => '208', 
                'avg_duration_min' => 25, 
                'specialty' => 'Офтальмолог',
                'photo_url' => 'https://images.unsplash.com/photo-1594824388852-8a0b1b0b0b0b?w=400&h=400&fit=crop&crop=face&auto=format'
            ],
        ];

        foreach ($doctors as $doc) {
            // Создаем английский email без пробелов
            $englishName = $this->convertToEnglish($doc['name']);
            $email = strtolower(str_replace(' ', '', $englishName)) . '@clinic.local';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $doc['name'],
                    'password' => bcrypt('123456zaza'),
                ]
            );
            $user->assignRole('doctor');

            // Получаем и сохраняем фото врача
            $imageUrl = $this->getDoctorImage($doc['specialty']);
            $photoPath = $this->downloadAndSavePhoto($imageUrl, 'doctors', $doc['name']);

            // Получаем ID специальности
            $specialty = Specialty::where('name', $doc['specialty'])->first();

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'speciality_id' => $specialty?->id,
                    'room' => $doc['room'],
                    'avg_duration_min' => $doc['avg_duration_min'],
                    'is_active' => true,
                    'photo_url' => $photoPath,
                ]
            );

            // Создаем расписание на ближайшие 5 дней
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

    /**
     * Конвертирует русские имена в английские
     */
    private function convertToEnglish(string $russianName): string
    {
        $translations = [
            'Иванов' => 'Ivanov',
            'Иван' => 'Ivan',
            'Иванович' => 'Ivanovich',
            'Смирнова' => 'Smirnova',
            'Анна' => 'Anna',
            'Петровна' => 'Petrovna',
            'Петров' => 'Petrov',
            'Петр' => 'Petr',
            'Сергеевич' => 'Sergeevich',
            'Козлова' => 'Kozlova',
            'Елена' => 'Elena',
            'Владимировна' => 'Vladimirovna',
            'Морозов' => 'Morozov',
            'Дмитрий' => 'Dmitry',
            'Александрович' => 'Alexandrovich',
            'Волкова' => 'Volkova',
            'Мария' => 'Maria',
            'Игоревна' => 'Igorevna',
            'Соколов' => 'Sokolov',
            'Андрей' => 'Andrey',
            'Николаевич' => 'Nikolaevich',
            'Новикова' => 'Novikova',
            'Ольга' => 'Olga',
            'Сергеевна' => 'Sergeevna',
        ];

        $englishName = $russianName;
        foreach ($translations as $russian => $english) {
            $englishName = str_replace($russian, $english, $englishName);
        }

        return $englishName;
    }

    /**
     * Получает fallback изображение для врача
     */
    private function getFallbackDoctorImage(string $name): string
    {
        // Создаем простое изображение с инициалами врача
        $initials = strtoupper(substr($name, 0, 2));
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&size=200&background=4F46E5&color=FFFFFF&format=png';
    }

    /**
     * Получает изображение врача с Unsplash по медицинским тегам
     */
    private function getDoctorImage(string $specialty): string
    {
        $specialtyImages = [
            'Терапевт' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=400&fit=crop&crop=face&auto=format',
            'Кардиолог' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&h=400&fit=crop&crop=face&auto=format',
            'Хирург' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&crop=face&auto=format',
            'Педиатр' => 'https://images.unsplash.com/photo-1594824388852-8a0b1b0b0b0b?w=400&h=400&fit=crop&crop=face&auto=format',
            'Невролог' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=400&fit=crop&crop=face&auto=format',
            'Стоматолог' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=400&h=400&fit=crop&crop=face&auto=format',
            'ЛОР' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&crop=face&auto=format',
            'Офтальмолог' => 'https://images.unsplash.com/photo-1594824388852-8a0b1b0b0b0b?w=400&h=400&fit=crop&crop=face&auto=format',
        ];

        return $specialtyImages[$specialty] ?? 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=400&h=400&fit=crop&crop=face&auto=format';
    }

    /**
     * Скачивает и сохраняет фото по URL
     */
    private function downloadAndSavePhoto(string $url, string $folder, string $name): string
    {
        try {
            // Создаем контекст с коротким таймаутом
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5, // Уменьшил таймаут до 5 секунд
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'method' => 'GET',
                    'header' => [
                        'Accept: image/*',
                        'Connection: close'
                    ]
                ]
            ]);
            
            $contents = @file_get_contents($url, false, $context);
            if ($contents === false || empty($contents)) {
                throw new \Exception("Не удалось скачать изображение с URL: {$url}");
            }

            // Проверяем, что это действительно изображение
            $imageInfo = @getimagesizefromstring($contents);
            if ($imageInfo === false) {
                throw new \Exception("Скачанный файл не является изображением");
            }

            $filename = strtolower(str_replace([' ', '.'], ['_', ''], $name)) . '.jpg';
            $path = "{$folder}/{$filename}";
            
            // Убеждаемся, что папка существует
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }
            
            Storage::disk('public')->put($path, $contents);
            
            echo "✓ Загружено фото для {$name}\n";
            return $path;
        } catch (\Exception $e) {
            // Если не удалось скачать, используем fallback
            echo "⚠ Не удалось загрузить фото для {$name}, используем fallback\n";
            
            // Создаем простое изображение с инициалами
            $initials = strtoupper(substr($name, 0, 2));
            $fallbackUrl = "https://ui-avatars.com/api/?name={$initials}&size=200&background=4F46E5&color=FFFFFF&format=png";
            
            try {
                $fallbackContents = @file_get_contents($fallbackUrl);
                if ($fallbackContents !== false) {
                    $filename = strtolower(str_replace([' ', '.'], ['_', ''], $name)) . '.png';
                    $path = "{$folder}/{$filename}";
                    Storage::disk('public')->put($path, $fallbackContents);
                    return $path;
                }
            } catch (\Exception $fallbackException) {
                echo "⚠ Не удалось создать fallback изображение для {$name}\n";
            }
            
            return 'https://ui-avatars.com/api/?name=' . urlencode(substr($name, 0, 2)) . '&size=200&background=4F46E5&color=FFFFFF';
        }
    }
}