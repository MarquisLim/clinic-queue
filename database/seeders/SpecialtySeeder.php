<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Педиатр',
                'description' => 'Быстрая помощь вашему ребёнку',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Хирург',
                'description' => 'Поможет при травмах и операциях',
                'image' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Кардиолог',
                'description' => 'Забота о вашем сердце',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Невролог',
                'description' => 'Поддержка нервной системы',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Стоматолог',
                'description' => 'Здоровье и красота ваших зубов',
                'image' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'ЛОР',
                'description' => 'Поможет при заболеваниях уха, горла и носа',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Офтальмолог',
                'description' => 'Забота о вашем зрении и глазах',
                'image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=400&h=400&fit=crop&auto=format'
            ],
            [
                'name' => 'Ветеринар',
                'description' => 'Поможет вашему питомцу',
                'image' => 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=400&h=400&fit=crop&auto=format'
            ],
        ];

        foreach ($specialties as $spec) {
            // Скачиваем и сохраняем изображение специальности
            $imagePath = $this->downloadAndSaveImage($spec['image'], 'specialties', $spec['name']);

            Specialty::updateOrCreate(
                ['name' => $spec['name']], // ищем по имени
                [
                    'description' => $spec['description'],
                    'image_url' => $imagePath,
                ]
            );
        }
    }

    /**
     * Получает fallback изображение для специальности
     */
    private function getFallbackImage(string $name): string
    {
        // Создаем простое изображение с названием специальности
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=400&background=4F46E5&color=FFFFFF&format=png';
    }

    /**
     * Скачивает и сохраняет изображение по URL
     */
    private function downloadAndSaveImage(string $url, string $folder, string $name): string
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
            
            echo "✓ Загружено изображение для специальности {$name}\n";
            return $path;
        } catch (\Exception $e) {
            // Если не удалось скачать, используем fallback
            echo "⚠ Не удалось загрузить изображение для специальности {$name}, используем fallback\n";
            
            // Создаем простое изображение с названием специальности
            $fallbackUrl = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=400&background=4F46E5&color=FFFFFF&format=png";
            
            try {
                $fallbackContents = @file_get_contents($fallbackUrl);
                if ($fallbackContents !== false) {
                    $filename = strtolower(str_replace([' ', '.'], ['_', ''], $name)) . '.png';
                    $path = "{$folder}/{$filename}";
                    Storage::disk('public')->put($path, $fallbackContents);
                    return $path;
                }
            } catch (\Exception $fallbackException) {
                echo "⚠ Не удалось создать fallback изображение для специальности {$name}\n";
            }
            
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=400&background=4F46E5&color=FFFFFF';
        }
    }
}