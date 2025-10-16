<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Каналы для записей на прием
Broadcast::channel('appointments', function () {
    return true; // Публичный канал для всех записей
});

// Канал для конкретного врача
Broadcast::channel('doctor.{doctorId}', function ($user, $doctorId) {
    // Разрешаем доступ врачам и регистраторам
    return $user->hasRole(['doctor', 'registrar']);
});

// Канал для конкретного пациента
Broadcast::channel('patient.{patientId}', function ($user, $patientId) {
    // Разрешаем доступ пациенту, врачу и регистратору
    return $user->hasRole(['patient', 'doctor', 'registrar']) || 
           $user->id == $patientId;
});
