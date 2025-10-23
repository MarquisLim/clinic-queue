<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class ScheduleController extends Controller
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
            ->workingDays()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json($schedules);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_len_min' => 'nullable|integer|min:5|max:180',
            'is_working_day' => 'boolean',
            'breaks' => 'nullable|array',
            'breaks.*.start' => 'required_with:breaks|date_format:H:i',
            'breaks.*.end' => 'required_with:breaks|date_format:H:i|after:breaks.*.start',
        ]);

        // Проверяем, что дата не в прошлом
        $date = Carbon::parse($request->date);
        if ($date->isPast() && !$date->isToday()) {
            return response()->json(['error' => 'Нельзя создавать расписание в прошлом'], 422);
        }

        // Проверяем, что время работы не превышает 24 часа
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        if ($startTime->diffInHours($endTime) > 24) {
            return response()->json(['error' => 'Рабочий день не может превышать 24 часа'], 422);
        }

        // Проверяем, что нет пересечений с существующим расписанием
        $existingSchedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('date', $request->date)
            ->first();

        if ($existingSchedule) {
            return response()->json(['error' => 'Расписание на эту дату уже существует'], 422);
        }

        $schedule = Schedule::create([
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_len_min' => $request->slot_len_min ?? 30,
            'is_working_day' => $request->is_working_day ?? true,
            'breaks' => $request->breaks ?? [],
        ]);

        return response()->json($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_len_min' => 'nullable|integer|min:5|max:180',
            'is_working_day' => 'boolean',
            'breaks' => 'nullable|array',
            'breaks.*.start' => 'required_with:breaks|date_format:H:i',
            'breaks.*.end' => 'required_with:breaks|date_format:H:i|after:breaks.*.start',
        ]);

        // Проверяем, что время работы не превышает 24 часа
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        if ($startTime->diffInHours($endTime) > 24) {
            return response()->json(['error' => 'Рабочий день не может превышать 24 часа'], 422);
        }

        // Проверяем, что нет конфликтов с записями на прием
        if ($schedule->date >= now()->toDateString()) {
            $hasAppointments = $schedule->appointments()->whereIn('status', ['pending', 'checked_in', 'in_progress'])->exists();
            if ($hasAppointments) {
                return response()->json(['error' => 'Нельзя изменить расписание, на которое есть записи'], 422);
            }
        }

        $schedule->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_len_min' => $request->slot_len_min ?? $schedule->slot_len_min,
            'is_working_day' => $request->is_working_day ?? true,
            'breaks' => $request->breaks ?? $schedule->breaks,
        ]);

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        // Проверяем, что нет активных записей на прием
        if ($schedule->date >= now()->toDateString()) {
            $hasAppointments = $schedule->appointments()->whereIn('status', ['pending', 'checked_in', 'in_progress'])->exists();
            if ($hasAppointments) {
                return response()->json(['error' => 'Нельзя удалить расписание, на которое есть записи'], 422);
            }
        }

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
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'integer|between:1,7', // 1 = Monday, 7 = Sunday
            'slot_len_min' => 'nullable|integer|min:5|max:180',
            'breaks' => 'nullable|array',
            'breaks.*.start' => 'required_with:breaks|date_format:H:i',
            'breaks.*.end' => 'required_with:breaks|date_format:H:i|after:breaks.*.start',
        ]);

        $weekStart = Carbon::parse($request->week_start)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $doctor = Doctor::findOrFail($request->doctor_id);

        // Разрешаем редактирование всех недель (включая прошлые)
        // Убрано ограничение на прошлые недели для удобства управления

        // Проверяем, что время работы не превышает 24 часа
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);
        if ($startTime->diffInHours($endTime) > 24) {
            return response()->json(['error' => 'Рабочий день не может превышать 24 часа'], 422);
        }

        // Проверяем только критические записи (в процессе или подтвержденные)
        $hasCriticalAppointments = \App\Models\Appointment::where('doctor_id', $request->doctor_id)
            ->whereBetween('slot_start', [$weekStart, $weekEnd])
            ->whereIn('status', ['checked_in', 'in_progress'])
            ->exists();

        if ($hasCriticalAppointments) {
            return response()->json(['error' => 'Нельзя изменить расписание, на которое есть записи в процессе'], 422);
        }

        // Удаляем существующее расписание на эту неделю
        Schedule::where('doctor_id', $request->doctor_id)
            ->whereBetween('date', [$weekStart, $weekEnd])
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
                'slot_len_min' => $request->slot_len_min ?? 30,
                'is_working_day' => true,
                'breaks' => $request->breaks ?? [],
            ]);

            $schedules[] = $schedule;
        }

        return response()->json([
            'message' => 'Расписание на неделю создано',
            'schedules' => $schedules,
            'count' => count($schedules)
        ]);
    }

    /**
     * Получить статистику расписания
     */
    public function getStats(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $doctorId = $request->doctor_id;
        $month = $request->month ? Carbon::parse($request->month) : now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $schedules = Schedule::where('doctor_id', $doctorId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $stats = [
            'total_days' => $schedules->count(),
            'working_days' => $schedules->where('is_working_day', true)->count(),
            'total_hours' => $schedules->sum(function ($schedule) {
                $start = Carbon::parse($schedule->start_time);
                $end = Carbon::parse($schedule->end_time);
                return $start->diffInHours($end);
            }),
            'avg_slot_duration' => $schedules->avg('slot_len_min'),
            'schedules_by_day' => $schedules->groupBy(function ($schedule) {
                return Carbon::parse($schedule->date)->format('l');
            })->map->count()
        ];

        return response()->json($stats);
    }

    /**
     * Копировать расписание с одной недели на другую
     */
    public function copyWeek(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'from_week' => 'required|date',
            'to_week' => 'required|date|after:from_week',
        ]);

        $fromWeek = Carbon::parse($request->from_week)->startOfWeek();
        $toWeek = Carbon::parse($request->to_week)->startOfWeek();

        // Получаем расписание исходной недели
        $sourceSchedules = Schedule::where('doctor_id', $request->doctor_id)
            ->whereBetween('date', [$fromWeek, $fromWeek->copy()->endOfWeek()])
            ->get();

        if ($sourceSchedules->isEmpty()) {
            return response()->json(['error' => 'Нет расписания для копирования'], 404);
        }

        // Удаляем существующее расписание целевой недели
        Schedule::where('doctor_id', $request->doctor_id)
            ->whereBetween('date', [$toWeek, $toWeek->copy()->endOfWeek()])
            ->delete();

        $copiedSchedules = [];

        foreach ($sourceSchedules as $sourceSchedule) {
            $sourceDate = Carbon::parse($sourceSchedule->date);
            $dayOfWeek = $sourceDate->dayOfWeek;
            $targetDate = $toWeek->copy()->addDays($dayOfWeek);

            $copiedSchedule = Schedule::create([
                'doctor_id' => $sourceSchedule->doctor_id,
                'date' => $targetDate->format('Y-m-d'),
                'start_time' => $sourceSchedule->start_time,
                'end_time' => $sourceSchedule->end_time,
                'slot_len_min' => $sourceSchedule->slot_len_min,
                'is_working_day' => $sourceSchedule->is_working_day,
                'breaks' => $sourceSchedule->breaks,
            ]);

            $copiedSchedules[] = $copiedSchedule;
        }

        return response()->json([
            'message' => 'Расписание скопировано',
            'schedules' => $copiedSchedules,
            'count' => count($copiedSchedules)
        ]);
    }

    /**
     * Получить доступные слоты для записи
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $doctorId = $request->doctor_id;
        $date = Carbon::parse($request->date);

        $schedule = Schedule::where('doctor_id', $doctorId)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_working_day', true)
            ->first();

        if (!$schedule) {
            return response()->json(['slots' => []]);
        }

        $slots = [];
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        $slotDuration = $schedule->slot_len_min ?? 30;

        $current = $startTime->copy();
        while ($current->addMinutes($slotDuration)->lte($endTime)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($slotDuration);

            // Проверяем, не попадает ли слот в перерыв
            $isInBreak = false;
            if ($schedule->breaks) {
                foreach ($schedule->breaks as $break) {
                    $breakStart = Carbon::parse($break['start']);
                    $breakEnd = Carbon::parse($break['end']);
                    if ($slotStart->between($breakStart, $breakEnd) || $slotEnd->between($breakStart, $breakEnd)) {
                        $isInBreak = true;
                        break;
                    }
                }
            }

            if (!$isInBreak) {
                // Проверяем, не занят ли слот
                $isBooked = \App\Models\Appointment::where('doctor_id', $doctorId)
                    ->where('slot_start', $slotStart)
                    ->whereIn('status', ['pending', 'checked_in', 'in_progress'])
                    ->exists();

                if (!$isBooked) {
                    $slots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime' => $slotStart->toISOString(),
                    ];
                }
            }

            $current->addMinutes($slotDuration);
        }

        return response()->json(['slots' => $slots]);
    }
}

