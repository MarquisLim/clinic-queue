# Clinic Queue - Система управления очередями в клинике

Система управления очередями для медицинской клиники, построенная на Laravel 12 с Vue 3 и real-time уведомлениями через Laravel Reverb.

## Технологии

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js
- **Real-time**: Laravel Reverb (WebSocket сервер)
- **UI**: Tailwind CSS + DaisyUI
- **База данных**: SQLite (по умолчанию)
- **Очереди**: Database Queue
- **Аутентификация**: Laravel Breeze
- **Права доступа**: Spatie Laravel Permission

## Функциональность

- Управление записями к врачам
- Real-time уведомления о статусе очереди
- Автоматические напоминания о записях
- Роли пользователей (пациент, врач, регистратор, администратор)
- Управление расписанием врачей
- Система очередей с номерами талонов

## Установка

### 1. Клонирование и установка зависимостей

```bash
# Установка PHP зависимостей
composer install

# Установка Node.js зависимостей
npm install
```

### 2. Настройка окружения

```bash
# Создание файла .env (если отсутствует)
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate
```

### 3. Настройка базы данных

```bash
# Создание SQLite базы данных
touch database/database.sqlite

# Выполнение миграций
php artisan migrate

# Заполнение базы тестовыми данными
php artisan db:seed
```

### 4. Настройка файлового хранилища

```bash
# Создание символической ссылки для файлов
php artisan storage:link
```

## Запуск приложения

### 1. Основной сервер разработки

```bash
# Запуск Laravel сервера
php artisan serve
```

Приложение будет доступно по адресу: http://localhost:8000

### 2. Frontend разработка

```bash
# Запуск Vite для сборки фронтенда
npm run dev
```

### 3. Laravel Reverb (WebSocket сервер)

```bash
# Запуск Reverb сервера для real-time уведомлений
php artisan reverb:start --host=127.0.0.1 --port=8080
```

### 4. Обработка очередей

```bash
# Запуск обработчика очередей
php artisan queue:work
```

### 5. Планировщик задач

```bash
# Запуск планировщика для автоматических напоминаний
php artisan schedule:work
```

## Команды для разработки

### Полный запуск всех сервисов

```bash
# Запуск всех сервисов одновременно
composer run dev
```

Эта команда запустит:
- Laravel сервер
- Обработчик очередей
- Логи в реальном времени
- Vite dev server

### Отдельные команды

```bash
# Очистка кэша
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Оптимизация для продакшена
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Перезапуск очередей
php artisan queue:restart
```

## Тестовые данные

После выполнения `php artisan db:seed` будут созданы:

### Пользователи
- **Администратор**: admin@clinic.local / 123456zaza
- **Врачи**: 8 врачей разных специальностей
- **Регистраторы**: 2 регистратора

### Роли
- `admin` - Администратор системы
- `doctor` - Врач
- `registrar` - Регистратор
- `patient` - Пациент

### Специальности
- Терапевт
- Педиатр
- Хирург
- Кардиолог
- Невролог
- Стоматолог
- ЛОР
- Офтальмолог
- Ветеринар

## Структура проекта

```
app/
├── Console/Commands/          # Artisan команды
├── Events/                    # События для real-time уведомлений
├── Http/Controllers/          # Контроллеры
├── Models/                    # Eloquent модели
├── Policies/                  # Политики авторизации
├── Services/                  # Бизнес-логика
└── Providers/                 # Service Providers

database/
├── migrations/               # Миграции базы данных
└── seeders/                  # Сидеры для тестовых данных

resources/
├── js/                       # Vue.js компоненты
│   ├── Components/           # Vue компоненты
│   ├── Pages/                # Страницы приложения
│   └── Layouts/              # Макеты
└── css/                      # Стили

routes/
├── web.php                   # Web маршруты
├── api.php                   # API маршруты
└── console.php               # Консольные команды
```

## Основные модели

- **User** - Пользователи системы
- **Doctor** - Врачи
- **Specialty** - Медицинские специальности
- **Appointment** - Записи к врачам
- **Schedule** - Расписание врачей
- **StatusLog** - Логи изменений статусов

## Real-time функции

Система использует Laravel Reverb для real-time уведомлений:

- Уведомления о статусе очереди
- Напоминания о записях
- Обновления в реальном времени

## Очереди и задачи

- **Database Queue** - для обработки фоновых задач
- **Автоматические напоминания** - каждую минуту проверяются записи
- **Планировщик задач** - для регулярных операций

## API Endpoints

- `GET /` - Главная страница
- `GET /login` - Страница входа
- `GET /register` - Страница регистрации
- `GET /dashboard` - Панель управления (по ролям)
- `GET /appointments` - Управление записями
- `GET /doctors` - Список врачей
- `GET /queue` - Очередь

## Разработка

### Добавление новых компонентов

```bash
# Создание нового контроллера
php artisan make:controller ControllerName

# Создание новой модели
php artisan make:model ModelName -m

# Создание миграции
php artisan make:migration create_table_name

# Создание сидера
php artisan make:seeder SeederName
```

### Тестирование

```bash
# Запуск тестов
php artisan test

# Запуск тестов с покрытием
php artisan test --coverage
```

## Продакшен

### Сборка для продакшена

```bash
# Установка только продакшен зависимостей
composer install --no-dev --optimize-autoloader

# Сборка фронтенда
npm run build

# Оптимизация Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Настройка очередей в продакшене

```bash
# Запуск обработчика очередей
php artisan queue:work --daemon

# Запуск планировщика
php artisan schedule:work
```

## Troubleshooting

### Проблемы с установкой

1. **Ошибка composer install**: Убедитесь, что Git установлен и доступен в PATH
2. **Ошибка npm install**: Проверьте версию Node.js (требуется >= 18)
3. **Ошибка миграций**: Убедитесь, что база данных создана и доступна

### Проблемы с real-time

1. **Reverb не запускается**: Проверьте, что порт 8080 свободен
2. **Уведомления не приходят**: Убедитесь, что Reverb сервер запущен
3. **Очереди не обрабатываются**: Запустите `php artisan queue:work`

## Лицензия

MIT License