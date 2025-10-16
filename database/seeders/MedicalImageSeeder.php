<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MedicalImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->updateSpecialtyImages();
        $this->updateDoctorImages();
    }

    /**
     * Обновляет изображения специальностей с медицинскими изображениями
     */
    private function updateSpecialtyImages(): void
    {
        $specialtyImages = [
            'Терапевт' => [
                'url' => 'https://picsum.photos/400/400?random=300',
                'fallback' => 'https://via.placeholder.com/400x400/4F46E5/FFFFFF?text=Терапевт'
            ],
            'Педиатр' => [
                'url' => 'https://picsum.photos/400/400?random=301',
                'fallback' => 'https://via.placeholder.com/400x400/10B981/FFFFFF?text=Педиатр'
            ],
            'Хирург' => [
                'url' => 'https://picsum.photos/400/400?random=302',
                'fallback' => 'https://via.placeholder.com/400x400/EF4444/FFFFFF?text=Хирург'
            ],
            'Кардиолог' => [
                'url' => 'https://picsum.photos/400/400?random=303',
                'fallback' => 'https://via.placeholder.com/400x400/F59E0B/FFFFFF?text=Кардиолог'
            ],
            'Невролог' => [
                'url' => 'https://picsum.photos/400/400?random=304',
                'fallback' => 'https://via.placeholder.com/400x400/8B5CF6/FFFFFF?text=Невролог'
            ],
            'Стоматолог' => [
                'url' => 'https://picsum.photos/400/400?random=305',
                'fallback' => 'https://via.placeholder.com/400x400/06B6D4/FFFFFF?text=Стоматолог'
            ],
            'ЛОР' => [
                'url' => 'https://picsum.photos/400/400?random=306',
                'fallback' => 'https://via.placeholder.com/400x400/84CC16/FFFFFF?text=ЛОР'
            ],
            'Офтальмолог' => [
                'url' => 'https://picsum.photos/400/400?random=307',
                'fallback' => 'https://via.placeholder.com/400x400/F97316/FFFFFF?text=Офтальмолог'
            ],
            'Ветеринар' => [
                'url' => 'https://picsum.photos/400/400?random=308',
                'fallback' => 'https://via.placeholder.com/400x400/EC4899/FFFFFF?text=Ветеринар'
            ],
        ];

        foreach ($specialtyImages as $specialtyName => $imageData) {
            try {
                $specialty = Specialty::where('name', $specialtyName)->first();
                if ($specialty) {
                    $imagePath = $this->downloadAndSaveImage($imageData['url'], 'specialties', $specialtyName, $imageData['fallback']);
                    $specialty->update(['image_url' => $imagePath]);
                    echo "Updated image for specialty: {$specialtyName}\n";
                } else {
                    echo "Specialty not found: {$specialtyName}\n";
                }
            } catch (\Exception $e) {
                echo "Error updating specialty {$specialtyName}: " . $e->getMessage() . "\n";
                // Продолжаем выполнение для остальных специальностей
            }
        }
    }

    /**
     * Обновляет изображения врачей с медицинскими изображениями
     */
    private function updateDoctorImages(): void
    {
        $doctorImages = [
            'Терапевт' => [
                'url' => 'https://picsum.photos/400/400?random=400',
                'fallback' => 'https://via.placeholder.com/400x400/4F46E5/FFFFFF?text=Доктор'
            ],
            'Кардиолог' => [
                'url' => 'https://picsum.photos/400/400?random=401',
                'fallback' => 'https://via.placeholder.com/400x400/10B981/FFFFFF?text=Доктор'
            ],
            'Хирург' => [
                'url' => 'https://picsum.photos/400/400?random=402',
                'fallback' => 'https://via.placeholder.com/400x400/EF4444/FFFFFF?text=Доктор'
            ],
            'Педиатр' => [
                'url' => 'https://picsum.photos/400/400?random=403',
                'fallback' => 'https://via.placeholder.com/400x400/F59E0B/FFFFFF?text=Доктор'
            ],
            'Невролог' => [
                'url' => 'https://picsum.photos/400/400?random=404',
                'fallback' => 'https://via.placeholder.com/400x400/8B5CF6/FFFFFF?text=Доктор'
            ],
            'Стоматолог' => [
                'url' => 'https://picsum.photos/400/400?random=405',
                'fallback' => 'https://via.placeholder.com/400x400/06B6D4/FFFFFF?text=Доктор'
            ],
            'ЛОР' => [
                'url' => 'https://picsum.photos/400/400?random=406',
                'fallback' => 'https://via.placeholder.com/400x400/84CC16/FFFFFF?text=Доктор'
            ],
            'Офтальмолог' => [
                'url' => 'https://picsum.photos/400/400?random=407',
                'fallback' => 'https://via.placeholder.com/400x400/F97316/FFFFFF?text=Доктор'
            ],
        ];

        $doctors = Doctor::with('specialty')->get();
        
        foreach ($doctors as $doctor) {
            try {
                if ($doctor->specialty) {
                    $imageData = $doctorImages[$doctor->specialty->name] ?? $doctorImages['Терапевт'];
                    $imagePath = $this->downloadAndSaveImage($imageData['url'], 'doctors', $doctor->user->name, $imageData['fallback']);
                    $doctor->update(['photo_url' => $imagePath]);
                    echo "Updated image for doctor: {$doctor->user->name}\n";
                } else {
                    echo "Doctor {$doctor->user->name} has no specialty\n";
                }
            } catch (\Exception $e) {
                echo "Error updating doctor {$doctor->user->name}: " . $e->getMessage() . "\n";
                // Продолжаем выполнение для остальных врачей
            }
        }
    }

    /**
     * Скачивает и сохраняет изображение по URL с fallback
     */
    private function downloadAndSaveImage(string $url, string $folder, string $name, string $fallback = null): string
    {
        try {
            $contents = file_get_contents($url);
            if ($contents === false) {
                throw new \Exception("Не удалось скачать изображение с URL: {$url}");
            }

            $filename = strtolower(str_replace([' ', '.'], ['_', ''], $name)) . '.jpg';
            $path = "{$folder}/{$filename}";
            
            Storage::disk('public')->put($path, $contents);
            
            return Storage::url($path);
        } catch (\Exception $e) {
            // Если не удалось скачать, используем fallback
            \Log::warning("Не удалось скачать изображение для {$name}: " . $e->getMessage());
            return $fallback ?? 'https://via.placeholder.com/400x400/4F46E5/FFFFFF?text=' . urlencode(substr($name, 0, 2));
        }
    }
}
