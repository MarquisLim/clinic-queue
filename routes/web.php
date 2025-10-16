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
    });

    // Профиль и настройки
    Route::get('/profile', [WebProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [WebProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [WebProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [WebProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

require __DIR__.'/auth.php';
