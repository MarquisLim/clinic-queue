<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AppointmentController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DoctorController;
use App\Http\Controllers\Web\DoctorPanelController;
use App\Http\Controllers\Web\ProfileController as WebProfileController;
use App\Http\Controllers\Web\RegistrarPanelController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\SlotController;
use App\Http\Controllers\Web\SpecialtyController;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Admin\ScheduleController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Главная страница (публичная)
Route::get('/', [DashboardController::class, 'index'])->name('home');

// Дашборд для авторизованных пользователей
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::get('/specialties', [SpecialtyController::class, 'index'])->name('specialties.index');
    Route::get('/doctors',     [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/slots',       [SlotController::class, 'index'])->name('slots.index');

    Route::get('/appointments/mine', [AppointmentController::class, 'mine'])->name('appointments.mine');
    Route::post('/appointments',     [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::get('/slots/availability', [SlotController::class, 'availability'])->name('slots.availability');
    Route::get('/slots/day',          [SlotController::class, 'day'])->name('slots.day');

    // Панель врача
    Route::prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/panel', [DoctorPanelController::class, 'index'])->name('panel');
        Route::patch('/appointments/{appointment}/status', [DoctorPanelController::class, 'updateStatus'])->name('appointments.update-status');
    });

    // Панель регистратора
    Route::prefix('registrar')->name('registrar.')->group(function () {
        Route::get('/panel', [RegistrarPanelController::class, 'index'])->name('panel');
        Route::post('/appointments/{appointment}/check-in', [RegistrarPanelController::class, 'checkIn'])->name('appointments.check-in');
        Route::delete('/appointments/{appointment}/cancel', [RegistrarPanelController::class, 'cancelAppointment'])->name('appointments.cancel');
        Route::get('/status-logs', [RegistrarPanelController::class, 'statusLogs'])->name('status-logs');
        Route::get('/appointments/available-slots', [RegistrarPanelController::class, 'getAvailableSlots'])->name('appointments.available-slots');
        Route::post('/appointments/{appointment}/reschedule', [RegistrarPanelController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
    });

    // Профиль и настройки
    Route::get('/profile', [WebProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [WebProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [WebProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [WebProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Уведомления
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    });

    // Админские маршруты
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/specialties', [AdminController::class, 'specialties'])->name('specialties');
        Route::get('/doctors', [AdminController::class, 'doctors'])->name('doctors');
        Route::get('/users', [AdminController::class, 'users'])->name('users');

        Route::post('/specialties', [AdminController::class, 'storeSpecialty'])->name('specialties.store');
        Route::put('/specialties/{specialty}', [AdminController::class, 'updateSpecialty'])->name('specialties.update');
        Route::delete('/specialties/{specialty}', [AdminController::class, 'destroySpecialty'])->name('specialties.destroy');

        Route::post('/doctors', [AdminController::class, 'storeDoctor'])->name('doctors.store');
        Route::put('/doctors/{doctor}', [AdminController::class, 'updateDoctor'])->name('doctors.update');
        Route::delete('/doctors/{doctor}', [AdminController::class, 'destroyDoctor'])->name('doctors.destroy');

        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');

        // Управление расписанием
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/schedule/get', [ScheduleController::class, 'getSchedule'])->name('schedule.get');
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::put('/schedule/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
        Route::post('/schedule/generate', [ScheduleController::class, 'generateWeek'])->name('schedule.generate');
        Route::get('/schedule/stats', [ScheduleController::class, 'getStats'])->name('schedule.stats');
        Route::post('/schedule/copy-week', [ScheduleController::class, 'copyWeek'])->name('schedule.copy-week');
        Route::get('/schedule/available-slots', [ScheduleController::class, 'getAvailableSlots'])->name('schedule.available-slots');
    });
});

Route::get('/debug', function () {
    return [
        'env' => config('app.env'),
        'debug' => config('app.debug'),
    ];
});

// Тестовая страница для уведомлений
Route::get('/test-notifications', function () {
    return Inertia\Inertia::render('TestNotifications');
})->middleware('auth');

// Тестовая страница для расписания
Route::get('/test-schedule', function () {
    $doctors = \App\Models\Doctor::with(['user', 'specialty'])->get();
    return Inertia\Inertia::render('TestSchedule', [
        'doctors' => $doctors
    ]);
})->middleware('auth');


require __DIR__.'/auth.php';
