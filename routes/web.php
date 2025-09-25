<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AppointmentController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DoctorController;
use App\Http\Controllers\Web\SlotController;
use App\Http\Controllers\Web\SpecialtyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/specialties', [SpecialtyController::class, 'index'])->name('specialties.index');
    Route::get('/doctors',     [DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/slots',       [SlotController::class, 'index'])->name('slots.index');

    Route::get('/appointments/mine', [AppointmentController::class, 'mine'])->name('appointments.mine');
    Route::post('/appointments',     [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])
        ->name('appointments.destroy');
});

require __DIR__.'/auth.php';
