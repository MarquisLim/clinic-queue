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
                'image' => 'https://picsum.photos/400/400?random=100'
            ],
            [
                'name' => 'Педиатр',
                'description' => 'Быстрая помощь вашему ребёнку',
                'image' => 'https://picsum.photos/400/400?random=101'
            ],
            [
                'name' => 'Хирург',
                'description' => 'Поможет при травмах и операциях',
                'image' => 'https://picsum.photos/400/400?random=102'
            ],
            [
                'name' => 'Кардиолог',
                'description' => 'Забота о вашем сердце',
                'image' => 'https://picsum.photos/400/400?random=103'
            ],
            [
                'name' => 'Невролог',
                'description' => 'Поддержка нервной системы',
                'image' => 'https://picsum.photos/400/400?random=104'
            ],
            [
                'name' => 'Стоматолог',
                'description' => 'Здоровье и красота ваших зубов',
                'image' => 'https://picsum.photos/400/400?random=105'
            ],
            [
                'name' => 'ЛОР',
                'description' => 'Поможет при заболеваниях уха, горла и носа',
                'image' => 'https://picsum.photos/400/400?random=106'
            ],
            [
                'name' => 'Офтальмолог',
                'description' => 'Забота о вашем зрении и глазах',
                'image' => 'https://picsum.photos/400/400?random=107'
            ],
            [
                'name' => 'Ветеринар',
                'description' => 'Поможет вашему питомцу',
                'image' => 'https://picsum.photos/400/400?random=108'
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
     * Скачивает и сохраняет изображение по URL
     */
    private function downloadAndSaveImage(string $url, string $folder, string $name): string
    {
        try {
            $contents = file_get_contents($url);
            if ($contents === false) {
                throw new \Exception("Не удалось скачать изображение с URL: {$url}");
            }

            $filename = strtolower(str_replace([' ', '.'], ['_', ''], $name)) . '.jpg';
            $path = "{$folder}/{$filename}";
            
            Storage::disk('public')->put($path, $contents);
            
            return $path;
        } catch (\Exception $e) {
            // Если не удалось скачать, используем fallback
            \Log::warning("Не удалось скачать изображение для специальности {$name}: " . $e->getMessage());
            return 'https://via.placeholder.com/400x400/4F46E5/FFFFFF?text=' . urlencode($name);
        }
    }
}