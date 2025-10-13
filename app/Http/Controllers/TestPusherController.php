<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

class TestPusherController extends Controller
{
    public function testConnection()
    {
        try {
            $pusher = new \Pusher\Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $data = [
                'message' => 'Тест подключения к Pusher',
                'timestamp' => now()->toISOString(),
                'app_id' => env('PUSHER_APP_ID')
            ];

            $result = $pusher->trigger('test-channel', 'test-event', $data);

            return response()->json([
                'success' => true,
                'message' => 'Pusher подключение работает!',
                'data' => $data,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка подключения к Pusher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testAppointmentEvent()
    {
        try {
            $pusher = new \Pusher\Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $data = [
                'appointment_id' => 999,
                'doctor_id' => 1,
                'patient_id' => 1,
                'ticket_no' => 'T001',
                'slot_start' => now()->toISOString(),
                'status' => 'pending'
            ];

            // Отправляем на разные каналы
            $pusher->trigger('appointments', 'appointment.created', $data);
            $pusher->trigger('doctor.1', 'appointment.created', $data);
            $pusher->trigger('patient.1', 'appointment.created', $data);

            return response()->json([
                'success' => true,
                'message' => 'Тестовое событие записи отправлено!',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка отправки события: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getConfig()
    {
        return response()->json([
            'pusher_config' => [
                'app_id' => config('broadcasting.connections.pusher.app_id'),
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
                'useTLS' => config('broadcasting.connections.pusher.options.useTLS'),
            ],
            'env_vars' => [
                'BROADCAST_CONNECTION' => env('BROADCAST_CONNECTION'),
                'BROADCAST_DRIVER' => env('BROADCAST_DRIVER'),
                'PUSHER_APP_ID' => env('PUSHER_APP_ID'),
                'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
                'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
            ]
        ]);
    }
}
