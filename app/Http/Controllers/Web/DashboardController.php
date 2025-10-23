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
        // Get statistics for main page
        $stats = [
            'specialties_count' => Specialty::count(),
            'doctors_count' => Doctor::with('user')->count(),
            'featured_specialties' => Specialty::take(6)->get(),
        ];

        return Inertia::render('Dashboard/Index', [
            'title' => 'Клиника - Запись к врачу',
            'stats' => $stats,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        
        // Determine where to redirect user based on their role
        if ($user->isDoctor()) {
            return redirect()->route('doctor.panel');
        } elseif ($user->isRegistrar()) {
            return redirect()->route('registrar.panel');
        } else {
            // Regular user - show dashboard with appointments
            return Inertia::render('Dashboard/UserDashboard', [
                'title' => 'Мои записи',
                'user' => $user,
            ]);
        }
    }
}
