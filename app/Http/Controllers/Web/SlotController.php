<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\SlotService;
use Carbon\CarbonImmutable;
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

    public function availability(Request $request, SlotService $slots)
    {
        $request->validate([
            'doctor_id' => ['required','integer','exists:doctors,id'],
            'from'      => ['nullable','date_format:Y-m-d'],
            'days'      => ['nullable','integer','min:1','max:31'],
        ]);

        $doctor = Doctor::findOrFail((int)$request->doctor_id);
        $from   = $request->get('from') ?: now()->toDateString();
        $start  = CarbonImmutable::parse($from);
        $days   = $request->integer('days') ?: 10;

        $out = [];
        for ($i=0; $i<$days; $i++) {
            $d = $start->addDays($i)->format('Y-m-d');
            $has = $slots->hasAnySlot($doctor, $d);
            $out[] = ['date' => $d, 'available' => $has];
        }
        return response()->json($out);
    }

    public function day(Request $request, SlotService $slots)
    {
        $request->validate([
            'doctor_id' => ['required','integer','exists:doctors,id'],
            'date'      => ['required','date_format:Y-m-d'],
        ]);

        $doctor = Doctor::findOrFail((int)$request->doctor_id);
        $date   = $request->date;

        return response()->json(
            $slots->forDoctorDate($doctor, $date)
        );
    }
}
