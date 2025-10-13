<?php

require_once 'vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Pusher\Pusher;

// Настройки Pusher
$options = [
    'cluster' => $_ENV['PUSHER_APP_CLUSTER'],
    'useTLS' => true
];

$pusher = new Pusher(
    $_ENV['PUSHER_APP_KEY'],
    $_ENV['PUSHER_APP_SECRET'],
    $_ENV['PUSHER_APP_ID'],
    $options
);

// Тестовое сообщение
$data = [
    'message' => 'Тест подключения к Pusher',
    'timestamp' => date('Y-m-d H:i:s')
];

// Отправляем на канал appointments
$result = $pusher->trigger('appointments', 'test.event', $data);

if ($result) {
    echo "✅ Pusher работает! Сообщение отправлено.\n";
    echo "Данные: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "❌ Ошибка отправки сообщения в Pusher\n";
}

