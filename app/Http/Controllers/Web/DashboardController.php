<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Получаем статистику для главной страницы
        $stats = [
            'specialties_count' => Specialty::count(),
            'doctors_count' => Doctor::with('user')->count(),
            'featured_specialties' => Specialty::with('doctors')->take(6)->get(),
        ];

        return Inertia::render('Dashboard/Index', [
            'title' => 'Клиника - Запись к врачу',
            'stats' => $stats,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Определяем куда перенаправить пользователя в зависимости от его роли
        if ($user->isDoctor()) {
            return redirect()->route('doctor.panel');
        } elseif ($user->isRegistrar()) {
            return redirect()->route('registrar.panel');
        } else {
            // Обычный пользователь - показываем дашборд с записями
            return Inertia::render('Dashboard/UserDashboard', [
                'title' => 'Мои записи',
                'user' => $user,
            ]);
        }
    }
}
