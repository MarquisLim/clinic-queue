# Установка и настройка Pusher

## ✅ Что уже настроено

Ваш проект уже настроен для работы с Pusher! Все необходимые файлы и конфигурации обновлены под ваши параметры из `.env`.

### Настройки в .env (уже есть):
```env
BROADCAST_CONNECTION=pusher
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=1990242
PUSHER_APP_KEY=9946ac56431f163d68eb
PUSHER_APP_SECRET=5add5fcc...
PUSHER_APP_CLUSTER=ap2
```

## 🚀 Шаги для запуска

### 1. Установите зависимости
```bash
composer install
npm install
```

### 2. Скомпилируйте frontend
```bash
npm run dev
```

### 3. Запустите сервер
```bash
php artisan serve
```

### 4. Откройте браузер
Перейдите на `http://localhost:8000/test/pusher` для тестирования Pusher

## 🧪 Тестирование

### Автоматический тест
1. Откройте `/test/pusher` в браузере
2. Нажмите "Тест подключения"
3. Нажмите "Тест события записи"
4. Проверьте лог событий

### Ручное тестирование в консоли браузера
```javascript
// Подключение к тестовому каналу
Echo.channel('test-channel').listen('.test-event', (e) => {
    console.log('Получено событие:', e);
});

// Подключение к каналу записей
Echo.channel('appointments').listen('.appointment.created', (e) => {
    console.log('Новая запись:', e);
});

// Подключение к каналу врача
Echo.channel('doctor.1').listen('.status.changed', (e) => {
    console.log('Статус изменен:', e);
});
```

## 📡 Каналы и события

### Каналы:
- `appointments` - общие события записей
- `doctor.{doctor_id}` - события для конкретного врача
- `patient.{patient_id}` - события для конкретного пациента
- `test-channel` - для тестирования

### События:
- `appointment.created` - создание записи
- `appointment.cancelled` - отмена записи
- `status.changed` - изменение статуса
- `test-event` - тестовое событие

## 🔧 Файлы конфигурации

### Backend:
- `config/broadcasting.php` - настройки Pusher для Laravel
- `app/Events/` - события для broadcasting
- `app/Http/Controllers/TestPusherController.php` - тестовый контроллер

### Frontend:
- `resources/js/bootstrap.js` - настройки Echo и Pusher
- `resources/js/composables/useQueueUpdates.js` - composable для real-time обновлений
- `resources/js/Pages/Test/PusherTest.vue` - страница тестирования

## 🎯 Использование в приложении

### Панель врача (`/doctor/panel`)
- Real-time обновления очереди пациентов
- Индикатор подключения к WebSocket
- Автоматическое обновление при изменении статусов

### Панель регистратора (`/registrar/panel`)
- Real-time обновления всех очередей
- Мгновенное отображение изменений статусов
- Синхронизация между разными пользователями

## 🐛 Устранение неполадок

### Если Pusher не подключается:
1. Проверьте настройки в `.env`
2. Убедитесь, что `npm run dev` выполнен
3. Проверьте консоль браузера на ошибки
4. Используйте `/test/pusher` для диагностики

### Если события не приходят:
1. Проверьте, что события отправляются с сервера
2. Убедитесь, что каналы правильно настроены
3. Проверьте права доступа к каналам

## 📚 Дополнительная информация

- [Документация Pusher](https://pusher.com/docs)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Echo](https://laravel.com/docs/echo)

---

**Готово!** Ваш проект настроен для работы с Pusher. Все real-time функции должны работать автоматически.
