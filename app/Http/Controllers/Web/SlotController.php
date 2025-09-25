<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SlotController extends Controller
{
    public function index(Request $request, SlotService $slots)
    {
        $request->validate([
            'doctor_id' => ['required','integer','exists:doctors,id'],
            'date'      => ['required','date_format:Y-m-d'],
        ]);

        $doctor = Doctor::with('schedules')->findOrFail((int)$request->doctor_id);
        $date   = $request->date;

        $data = $slots->forDoctorDate($doctor, $date);

        return Inertia::render('Slots/Index', [
            'doctor' => [
                'id' => $doctor->id,
                'room' => $doctor->room,
                'avg_duration_min' => $doctor->avg_duration_min,
            ],
            'date'   => $date,
            'slots'  => $data['slots'],
            'closed' => $data['closed'],
            'reason' => $data['closed_reason'] ?? null,
        ]);
    }
}
