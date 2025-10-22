<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['user', 'specialty'])->get();
        return Inertia::render('Admin/Schedule', [
            'doctors' => $doctors
        ]);
    }

    public function getSchedule(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'week_start' => 'required|date',
        ]);

        $weekStart = Carbon::parse($request->week_start)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $schedules = Schedule::where('doctor_id', $request->doctor_id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_working_day' => 'boolean',
        ]);

        $schedule = Schedule::create([
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_working_day' => $request->is_working_day ?? true,
        ]);

        return response()->json($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_working_day' => 'boolean',
        ]);

        $schedule->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_working_day' => $request->is_working_day ?? true,
        ]);

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return response()->json(['message' => 'Расписание удалено']);
    }

    public function generateWeek(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'week_start' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'working_days' => 'required|array',
            'working_days.*' => 'integer|between:1,7', // 1 = Monday, 7 = Sunday
        ]);

        $weekStart = Carbon::parse($request->week_start)->startOfWeek();
        $doctor = Doctor::findOrFail($request->doctor_id);

        // Удаляем существующее расписание на эту неделю
        Schedule::where('doctor_id', $request->doctor_id)
            ->whereBetween('date', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->delete();

        $schedules = [];

        // Создаем расписание для каждого рабочего дня
        foreach ($request->working_days as $dayOfWeek) {
            $date = $weekStart->copy()->addDays($dayOfWeek - 1);
            
            $schedule = Schedule::create([
                'doctor_id' => $request->doctor_id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_working_day' => true,
            ]);

            $schedules[] = $schedule;
        }

        return response()->json($schedules);
    }
}

