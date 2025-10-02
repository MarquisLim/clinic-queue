<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $specialtyId = $request->integer('specialty_id');

        $query = Doctor::query()
            ->with(['user:id,name', 'specialties:id,name'])
            ->where('is_active', true);

        if ($specialtyId) {
            $query->whereHas('specialties', fn($q) => $q->where('specialty_id', $specialtyId));
        }

        $doctors = $query
            ->orderBy('id')
            ->get(['id','user_id','room','avg_duration_min','is_active','photo_url']);

        return \Inertia\Inertia::render('Doctors/Index', [
            'doctors'      => $doctors,
            'selectedSpec' => $specialtyId,
        ]);
    }
}
