<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!$request->user()->isAdmin()) {
                abort(403, 'Доступ только для администраторов');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $stats = [
            'specialties' => Specialty::count(),
            'doctors' => Doctor::count(),
            'users' => User::count(),
            'appointments_today' => Appointment::whereDate('slot_start', today())->count()
        ];

        $recentAppointments = Appointment::with(['patient', 'doctor.user', 'doctor.specialty'])
            ->orderBy('slot_start', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentAppointments' => $recentAppointments
        ]);
    }

    public function specialties()
    {
        $specialties = Specialty::withCount('doctors')->get();
        return Inertia::render('Admin/Specialties', [
            'specialties' => $specialties
        ]);
    }

    public function doctors(Request $request)
    {
        $search = (string) $request->get('search', '');
        $specialtyId = $request->get('specialty_id');

        $query = Doctor::query()
            ->with(['user:id,name,email', 'specialty'])
            ->when($specialtyId, function ($q) use ($specialtyId) {
                $q->where('speciality_id', $specialtyId);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        $doctors = $query->paginate(9)->withQueryString();

        $specialties = Specialty::orderBy('name')->get();

        return Inertia::render('Admin/Doctors', [
            'doctors' => $doctors,
            'specialties' => $specialties,
            'filters' => [
                'search' => $search,
                'specialty_id' => $specialtyId,
            ],
        ]);
    }

    public function users(Request $request)
    {
        $search = (string) $request->get('search', '');
        $role = (string) $request->get('role', '');

        $query = User::query()
            ->with([
                'roles:id,name',
                'doctor:id,user_id,speciality_id,photo_url',
                'doctor.specialty:id,name,image',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function ($q) use ($role) {
                $q->whereHas('roles', function ($rq) use ($role) {
                    $rq->where('name', $role);
                });
            })
            ->orderByDesc('id');

        $users = $query->paginate(12)->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function storeSpecialty(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:specialties',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $specialty = Specialty::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('specialties', 'public');
            $specialty->update(['image' => $path]);
        }

        return redirect()->route('admin.specialties')->with('success', 'Специальность создана');
    }

    public function updateSpecialty(Request $request, Specialty $specialty)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('specialties')->ignore($specialty->id)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $specialty->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->hasFile('image')) {
            if ($specialty->image) {
                Storage::disk('public')->delete($specialty->image);
            }
            $path = $request->file('image')->store('specialties', 'public');
            $specialty->update(['image' => $path]);
        }

        return redirect()->route('admin.specialties')->with('success', 'Специальность обновлена');
    }

    public function destroySpecialty(Specialty $specialty)
    {
        if ($specialty->doctors()->count() > 0) {
            return redirect()->route('admin.specialties')->with('error', 'Нельзя удалить специальность, к которой привязаны врачи');
        }

        if ($specialty->image) {
            Storage::disk('public')->delete($specialty->image);
        }

        $specialty->delete();
        return redirect()->route('admin.specialties')->with('success', 'Специальность удалена');
    }

    public function storeDoctor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'specialty_id' => 'required|exists:specialties,id',
            'office' => 'required|string|max:255',
            'default_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('doctor');

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'speciality_id' => $request->specialty_id,
            'room' => $request->office,
            'avg_duration_min' => $request->default_duration,
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('doctors', 'public');
            $doctor->update(['photo_url' => $path]);
        }

        return redirect()->route('admin.doctors')->with('success', 'Врач создан');
    }

    public function updateDoctor(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($doctor->user_id)],
            'password' => 'nullable|string|min:8',
            'specialty_id' => 'required|exists:specialties,id',
            'office' => 'required|string|max:255',
            'default_duration' => 'required|integer|min:15|max:120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $doctor->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $doctor->user->update(['password' => Hash::make($request->password)]);
        }

        $doctor->update([
            'speciality_id' => $request->specialty_id,
            'room' => $request->office,
            'avg_duration_min' => $request->default_duration,
        ]);

        if ($request->hasFile('photo')) {
            if ($doctor->photo_url) {
                Storage::disk('public')->delete($doctor->photo_url);
            }
            $path = $request->file('photo')->store('doctors', 'public');
            $doctor->update(['photo_url' => $path]);
        }

        return redirect()->route('admin.doctors')->with('success', 'Врач обновлен');
    }

    public function destroyDoctor(Doctor $doctor)
    {
        if ($doctor->appointments()->count() > 0) {
            return redirect()->route('admin.doctors')->with('error', 'Нельзя удалить врача, у которого есть записи');
        }

        if ($doctor->photo_url) {
            Storage::disk('public')->delete($doctor->photo_url);
        }

        $doctor->user->delete();
        return redirect()->route('admin.doctors')->with('success', 'Врач удален');
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:patient,doctor,registrar,admin',
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users')->with('success', 'Роль пользователя обновлена');
    }
}
