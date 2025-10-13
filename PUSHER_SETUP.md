# Настройка Pusher для Real-time обновлений

## Настройка переменных окружения

Добавьте следующие переменные в ваш `.env` файл:

```env
BROADCAST_CONNECTION=pusher
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=1990242
PUSHER_APP_KEY=9946ac56431f163d68eb
PUSHER_APP_SECRET=5add5fcc...
PUSHER_APP_CLUSTER=ap2
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
```

## Установленные пакеты

- `laravel-echo` - для работы с WebSocket соединениями
- `pusher-js` - клиент Pusher для JavaScript

## Реализованные функции

### 1. События Broadcasting
- `AppointmentCreated` - при создании новой записи
- `AppointmentCancelled` - при отмене записи
- `AppointmentStatusChanged` - при изменении статуса записи

### 2. Панель врача (`/doctor/panel`)
- Просмотр списка пациентов на сегодня
- Управление статусами: "Отметить прибытие", "Начать приём", "Завершить приём"
- Real-time обновления через Pusher
- Статистика по записям

### 3. Панель регистратора (`/registrar/panel`)
- Просмотр всех врачей и их очередей
- Чек-ин пациентов
- Отмена записей
- Real-time обновления
- Ссылка на журналы изменений

### 4. Журналы статусов (`/registrar/status-logs`)
- История всех изменений статусов
- Информация о том, кто изменил статус
- Время изменения
- Причины отмены

### 5. Real-time обновления
- Обновление позиций в очереди в реальном времени
- Индикаторы подключения к WebSocket
- Автоматическое обновление данных при изменениях

## Каналы Broadcasting

- `appointments` - общие события записей
- `doctor.{doctor_id}` - события для конкретного врача
- `patient.{patient_id}` - события для конкретного пациента

## Использование

1. Убедитесь, что Pusher настроен в `.env`
2. Запустите `npm run dev` для компиляции фронтенда
3. Откройте панели врача или регистратора
4. Индикатор "🔴 Live обновления" покажет, что WebSocket подключен
5. Изменения статусов будут отображаться в реальном времени

## Тестирование

Для тестирования real-time функций:
1. Откройте панель врача в одном браузере
2. Откройте панель регистратора в другом браузере
3. Измените статус записи в одной панели
4. Увидите обновления в другой панели без перезагрузки

