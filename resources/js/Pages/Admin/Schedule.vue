<template>
    <Head title="Управление расписанием" />

    <AppLayout title="Управление расписанием">
        <!-- Заголовок с инструкциями -->
        <div class="flex justify-between items-start mb-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold mb-2">Управление расписанием</h1>
                <p class="text-gray-600 mb-4">Настройка рабочего времени врачей</p>
                
                <!-- Инструкции -->
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
            <div>
                        <h3 class="font-bold">Как использовать:</h3>
                        <ol class="list-decimal list-inside space-y-1 text-sm">
                            <li>Выберите врача из списка</li>
                            <li>Выберите неделю с помощью календаря</li>
                            <li>Нажмите "Загрузить расписание" для просмотра</li>
                            <li>Используйте "Сгенерировать неделю" для массового создания</li>
                        </ol>
                    </div>
                </div>
            </div>
            <Link href="/admin" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Назад к админке
            </Link>
        </div>

        <!-- Панель управления -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100-4m0 4v2m0-6V4" />
                    </svg>
                    Настройки расписания
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <!-- Выбор врача -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Врач</span>
                        </label>
                        <select 
                            v-model="selectedDoctor" 
                            class="select select-bordered w-full"
                        >
                            <option value="">Выберите врача</option>
                            <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                {{ doctor.user.name }} - {{ doctor.specialty.name }}
                            </option>
                        </select>
                        <div class="label">
                            <span class="label-text-alt">Выберите врача для настройки расписания</span>
                        </div>
                    </div>

                    <!-- Выбор недели -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Неделя</span>
                        </label>
                        <input 
                            type="week" 
                            v-model="selectedWeek"
                            class="input input-bordered w-full"
                        />
                        <div class="label">
                            <span class="label-text-alt">Выберите неделю для просмотра/редактирования</span>
                        </div>
                    </div>

                    <!-- Навигация по неделям -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Навигация</span>
                        </label>
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <button 
                                    @click="previousWeek"
                                    class="btn btn-outline btn-sm flex-1"
                                    :disabled="!selectedWeek"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span class="hidden sm:inline">Предыдущая</span>
                                </button>
                                <button 
                                    @click="nextWeek"
                                    class="btn btn-outline btn-sm flex-1"
                                    :disabled="!selectedWeek"
                                >
                                    <span class="hidden sm:inline">Следующая</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                            <button 
                                @click="goToCurrentWeek"
                                class="btn btn-primary btn-sm w-full"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Текущая неделя</span>
                                <span class="sm:hidden">Текущая</span>
                            </button>
                        </div>
                        <div class="label">
                            <span class="label-text-alt">Переключение между неделями</span>
                        </div>
                    </div>

                    <!-- Действия -->
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Действия</span>
                        </label>
                        <div class="space-y-2">
                            <button 
                                @click="loadSchedule"
                                :disabled="!selectedDoctor || !selectedWeek"
                                class="btn btn-primary btn-sm w-full"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden sm:inline">Загрузить расписание</span>
                                <span class="sm:hidden">Загрузить</span>
                            </button>
                        <button 
                            @click="openGenerateModal"
                                :disabled="!selectedDoctor || !selectedWeek"
                                class="btn btn-secondary btn-sm w-full"
                        >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                                <span class="hidden sm:inline">Сгенерировать неделю</span>
                                <span class="sm:hidden">Сгенерировать</span>
                        </button>
                        </div>
                    </div>
                </div>

                <!-- Информация о выбранной неделе -->
                <div v-if="selectedWeek && selectedWeek.trim()" class="mt-4 p-4 bg-base-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-semibold">Выбранная неделя:</span>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-primary">{{ formatWeekDisplay(selectedWeek) || 'Неопределено' }}</div>
                            <div class="text-sm text-base-content/70">
                                <span v-if="getWeekStartDate(selectedWeek) && getWeekEndDate(selectedWeek)">
                                    {{ getWeekStartDate(selectedWeek) }} - {{ getWeekEndDate(selectedWeek) }}
                                </span>
                                <span v-else class="text-warning">
                                    Ошибка отображения дат
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Расписание на неделю -->
        <div v-if="selectedDoctor" class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Расписание на неделю
                </h2>
                    <div class="badge badge-primary">
                        {{ schedules.length }} {{ schedules.length === 1 ? 'день' : 'дней' }}
                    </div>
                    <div class="badge badge-outline text-xs">
                        Неделя: {{ selectedWeek }}
                    </div>
                </div>
                
                <!-- Отладочная информация -->
                <div v-if="!selectedWeek" class="alert alert-warning mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <span>Выберите неделю для отображения расписания</span>
                </div>
                
                <div v-else-if="weekDays.length === 0" class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Не удалось сгенерировать дни недели для выбранной недели</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4">
                    <template v-for="(day, index) in weekDays" :key="index">
                        <div 
                            v-if="day && day.date"
                        class="card bg-base-200 shadow-sm"
                            :class="{ 'ring-2 ring-primary': isToday(day.date) }"
                    >
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <h3 class="font-bold text-lg mb-1">
                                {{ day.name }}
                            </h3>
                                <div class="text-sm text-gray-500">
                                    {{ day.date }}
                                </div>
                            </div>
                            
                            <div v-if="getScheduleForDay(day.date)" class="space-y-2">
                                <!-- Время работы -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-primary">
                                        {{ getScheduleForDay(day.date).start_time }} - {{ getScheduleForDay(day.date).end_time }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Слот: {{ getScheduleForDay(day.date).slot_len_min || 30 }} мин
                                    </div>
                                </div>
                                
                                <!-- Перерывы -->
                                <div v-if="getScheduleForDay(day.date).breaks && getScheduleForDay(day.date).breaks.length > 0" class="text-center">
                                    <div class="text-xs text-warning font-semibold mb-1">Перерывы:</div>
                                    <div v-for="(breakItem, idx) in getScheduleForDay(day.date).breaks" :key="idx" class="text-xs text-warning">
                                        {{ breakItem.start }} - {{ breakItem.end }}
                                    </div>
                                </div>
                                
                                <!-- Действия -->
                                <div class="flex gap-1">
                                    <button 
                                        @click="editSchedule(getScheduleForDay(day.date))"
                                        class="btn btn-xs btn-outline flex-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click="deleteSchedule(getScheduleForDay(day.date))"
                                        class="btn btn-xs btn-error flex-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-else-if="getAnyScheduleForDay(day.date) && !getAnyScheduleForDay(day.date).is_working_day" class="space-y-2">
                                <!-- Нерабочий день -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-warning">
                                        Нерабочий день
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ getAnyScheduleForDay(day.date).start_time }} - {{ getAnyScheduleForDay(day.date).end_time }}
                                    </div>
                                </div>
                                
                                <!-- Действия -->
                                <div class="flex gap-1">
                                    <button 
                                        @click="editSchedule(getAnyScheduleForDay(day.date))"
                                        class="btn btn-xs btn-outline flex-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click="deleteSchedule(getAnyScheduleForDay(day.date))"
                                        class="btn btn-xs btn-error flex-1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-else class="space-y-2">
                                <!-- Нет расписания -->
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-gray-400">
                                        Нет расписания
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Расписание не создано
                                    </div>
                                </div>
                                
                                <!-- Действия -->
                                <div class="flex gap-1">
                                <button 
                                    @click="addSchedule(day.date)"
                                        class="btn btn-xs btn-primary flex-1"
                                >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div v-else-if="selectedDoctor" class="card bg-base-100 shadow-xl">
            <div class="card-body text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Расписание не найдено</h3>
                <p class="mt-1 text-sm text-gray-500">Создайте расписание для выбранной недели.</p>
                <div class="mt-6">
                    <button 
                        @click="openGenerateModal"
                        class="btn btn-primary"
                    >
                        Сгенерировать расписание
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно для редактирования расписания -->
        <div class="modal" :class="{ 'modal-open': showScheduleModal }">
            <div class="modal-box max-w-2xl w-full mx-4">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ editingSchedule ? 'Редактировать расписание' : 'Добавить расписание' }}
                </h3>

                <form @submit.prevent="saveSchedule" class="space-y-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Дата</span>
                        </label>
                        <input 
                            type="date" 
                            v-model="scheduleForm.date"
                            class="input input-bordered w-full"
                            required
                        />
                        <div class="label">
                            <span class="label-text-alt">Выберите дату для расписания</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Время начала</span>
                            </label>
                            <input 
                                type="time" 
                                v-model="scheduleForm.start_time"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Время окончания</span>
                            </label>
                            <input 
                                type="time" 
                                v-model="scheduleForm.end_time"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Длительность слота (минуты)</span>
                        </label>
                        <input 
                            type="number" 
                            v-model="scheduleForm.slot_len_min"
                            class="input input-bordered w-full"
                            min="5"
                            max="180"
                            placeholder="30"
                        />
                        <div class="label">
                            <span class="label-text-alt">Сколько минут длится один прием</span>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text font-semibold">Рабочий день</span>
                            <input 
                                type="checkbox" 
                                v-model="scheduleForm.is_working_day"
                                class="toggle toggle-primary"
                            />
                        </label>
                        <div class="label">
                            <span class="label-text-alt">Включить этот день в рабочее расписание</span>
                        </div>
                    </div>

                    <!-- Перерывы -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Перерывы</h4>
                        <div class="space-y-3">
                            <div v-for="(breakItem, index) in scheduleForm.breaks" :key="index" class="flex gap-2 items-end">
                                <div class="form-control flex-1">
                                    <label class="label">
                                        <span class="label-text text-sm">Начало перерыва</span>
                                    </label>
                                    <input
                                        type="time"
                                        v-model="breakItem.start"
                                        class="input input-bordered input-sm"
                                    />
                                </div>
                                <div class="form-control flex-1">
                                    <label class="label">
                                        <span class="label-text text-sm">Конец перерыва</span>
                                    </label>
                                    <input
                                        type="time"
                                        v-model="breakItem.end"
                                        class="input input-bordered input-sm"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeScheduleBreak(index)"
                                    class="btn btn-error btn-sm"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                type="button"
                                @click="addScheduleBreak"
                                class="btn btn-outline btn-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Добавить перерыв
                            </button>
                        </div>
                    </div>

                    <div class="modal-action">
                        <button 
                            type="button"
                            @click="closeScheduleModal"
                            class="btn btn-ghost"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            class="btn btn-primary"
                            :disabled="scheduleForm.processing"
                        >
                            <svg v-if="scheduleForm.processing" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ editingSchedule ? 'Обновить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно для генерации недели -->
        <div class="modal" :class="{ 'modal-open': showGenerateModal }">
            <div class="modal-box max-w-3xl w-full mx-4">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Сгенерировать расписание на неделю
                </h3>

                <div class="alert alert-warning mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <h4 class="font-bold">Внимание!</h4>
                        <p class="text-sm">Это действие создаст расписание для всех выбранных дней недели. Существующее расписание будет заменено.</p>
                    </div>
                </div>

                <form @submit.prevent="generateWeek" class="space-y-6">
                    <!-- Время работы -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Время работы</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label">
                                    <span class="label-text font-semibold">Время начала</span>
                            </label>
                            <input 
                                type="time" 
                                v-model="generateForm.start_time"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                    <span class="label-text font-semibold">Время окончания</span>
                            </label>
                            <input 
                                type="time" 
                                v-model="generateForm.end_time"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>
                        </div>
                    </div>

                    <!-- Рабочие дни -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Рабочие дни</h4>
                        
                        <!-- Быстрые кнопки -->
                        <div class="flex gap-2 mb-4">
                            <button 
                                type="button"
                                @click="selectAllDays"
                                class="btn btn-sm btn-outline"
                            >
                                Все дни
                            </button>
                            <button 
                                type="button"
                                @click="selectWeekdays"
                                class="btn btn-sm btn-outline"
                            >
                                Пн-Пт
                            </button>
                            <button 
                                type="button"
                                @click="clearAllDays"
                                class="btn btn-sm btn-outline"
                            >
                                Очистить
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                            <template v-for="(day, index) in weekDays" :key="index">
                                <label 
                                    v-if="day && day.name" 
                                    class="flex flex-col items-center p-3 rounded-lg border-2 transition-all cursor-pointer hover:shadow-md" 
                                    :class="generateForm.working_days.includes(index + 1) ? 'border-primary bg-primary/20 shadow-sm' : 'border-base-300 hover:border-primary/50'"
                                >
                                <input 
                                    type="checkbox" 
                                    :value="index + 1"
                                    v-model="generateForm.working_days"
                                        class="checkbox checkbox-primary mb-2"
                                    />
                                    <span class="text-sm font-semibold">{{ day.shortName || 'День' }}</span>
                                    <span class="text-xs text-base-content/70">{{ day.name || 'Неделя' }}</span>
                                    <div class="text-xs text-base-content/50 mt-1">
                                        {{ day.date }}
                                    </div>
                            </label>
                            </template>
                        </div>
                        
                        <div class="mt-3 p-2 bg-base-100 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold">Выбрано дней:</span>
                                <span class="badge badge-primary">{{ generateForm.working_days.length }}</span>
                            </div>
                            <div v-if="generateForm.working_days.length > 0" class="text-xs text-base-content/70 mt-1">
                                Дни: {{ getSelectedDaysNames() }}
                            </div>
                        </div>
                    </div>

                    <!-- Перерывы -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Перерывы</h4>
                        <div class="space-y-3">
                            <div v-for="(breakItem, index) in generateForm.breaks" :key="index" class="flex gap-2 items-end">
                                <div class="form-control flex-1">
                                    <label class="label">
                                        <span class="label-text text-sm">Начало перерыва</span>
                                    </label>
                                    <input 
                                        type="time" 
                                        v-model="breakItem.start"
                                        class="input input-bordered input-sm"
                                    />
                                </div>
                                <div class="form-control flex-1">
                                    <label class="label">
                                        <span class="label-text text-sm">Конец перерыва</span>
                                    </label>
                                    <input 
                                        type="time" 
                                        v-model="breakItem.end"
                                        class="input input-bordered input-sm"
                                    />
                                </div>
                                <button 
                                    type="button"
                                    @click="removeBreak(index)"
                                    class="btn btn-error btn-sm"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <button 
                                type="button"
                                @click="addBreak"
                                class="btn btn-outline btn-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Добавить перерыв
                            </button>
                        </div>
                    </div>

                    <!-- Дополнительные настройки -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Дополнительные настройки</h4>
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Длительность слота (минуты)</span>
                            </label>
                            <input 
                                type="number" 
                                v-model="generateForm.slot_len_min"
                                class="input input-bordered w-full"
                                min="5"
                                max="180"
                                placeholder="30"
                            />
                            <div class="label">
                                <span class="label-text-alt">Сколько минут длится один прием</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-action">
                        <button 
                            type="button"
                            @click="closeGenerateModal"
                            class="btn btn-ghost"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            class="btn btn-primary"
                            :disabled="generateForm.processing || generateForm.working_days.length === 0"
                        >
                            <svg v-if="generateForm.processing" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Сгенерировать расписание
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { makeAuthenticatedRequest } from '@/utils/csrf'

const props = defineProps({
    doctors: Array
})

const schedules = ref([])
const selectedDoctor = ref('')
const selectedWeek = ref('')
const editingSchedule = ref(null)

const showScheduleModal = ref(false)
const showGenerateModal = ref(false)

const scheduleForm = useForm({
    date: '',
    start_time: '09:00',
    end_time: '18:00',
    slot_len_min: 30,
    is_working_day: true,
    breaks: []
})

const generateForm = useForm({
    start_time: '09:00',
    end_time: '18:00',
    slot_len_min: 30,
    working_days: [1, 2, 3, 4, 5],
    breaks: []
})

const weekDays = computed(() => {
    if (!selectedWeek.value) return []
    
    try {
        console.log('weekDays: selectedWeek.value =', selectedWeek.value)
        
        // Парсим неделю в формате YYYY-WWW
        const [year, week] = selectedWeek.value.split('-W')
        console.log('weekDays: year =', year, 'week =', week)
        
        if (!year || !week) {
            console.log('weekDays: Неверный формат недели')
            return []
        }
        
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        console.log('weekDays: weekStart =', weekStart)
        
        if (isNaN(weekStart.getTime())) {
            console.log('weekDays: Неверная дата начала недели')
            return []
        }
        
    const days = []
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart)
        date.setDate(weekStart.getDate() + i)
        console.log(`weekDays: день ${i}, дата =`, date)
            
        // Проверяем, что дата валидна
        if (isNaN(date.getTime())) {
            console.log(`weekDays: неверная дата для дня ${i}`)
            continue
        }
        
        const dayInfo = {
            name: date.toLocaleDateString('ru-RU', { 
                weekday: 'long',
                timeZone: 'Asia/Tashkent'
            }),
            shortName: date.toLocaleDateString('ru-RU', { 
                weekday: 'short',
                timeZone: 'Asia/Tashkent'
            }),
            date: date.toISOString().split('T')[0]
        }
        
        console.log(`weekDays: день ${i} =`, dayInfo)
        days.push(dayInfo)
    }
    
    console.log('Сгенерировано дней недели:', days.length)
    console.log('Дни недели:', days)
    return days
    } catch (error) {
        console.error('Ошибка в weekDays:', error)
        return []
    }
})

// Автоматическая загрузка расписания при изменении врача или недели
watch([selectedDoctor, selectedWeek], () => {
    if (selectedDoctor.value && selectedWeek.value) {
        console.log('Автоматическая загрузка расписания для врача:', selectedDoctor.value, 'неделя:', selectedWeek.value)
        loadSchedule()
    }
}, { immediate: false })

onMounted(() => {
    try {
        console.log('onMounted: Инициализация недели')
        
        // Получаем текущую дату в правильном часовом поясе
    const now = new Date()
        const tashkentTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Tashkent"}))
        console.log('onMounted: tashkentTime =', tashkentTime)
        
        // Получаем начало текущей недели (понедельник)
        const weekStart = new Date(tashkentTime)
        const dayOfWeek = tashkentTime.getDay()
        const daysToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek
        weekStart.setDate(tashkentTime.getDate() + daysToMonday)
        console.log('onMounted: weekStart =', weekStart)
        
        // Проверяем, что дата валидна
        if (isNaN(weekStart.getTime())) {
            throw new Error('Invalid week start calculation')
        }
    
    const year = weekStart.getFullYear()
    const weekNumber = getWeekNumber(weekStart)
        console.log('onMounted: year =', year, 'weekNumber =', weekNumber)
        
        // Проверяем, что номер недели валиден
        if (weekNumber < 1 || weekNumber > 53) {
            throw new Error('Invalid week number')
        }
        
    selectedWeek.value = `${year}-W${weekNumber.toString().padStart(2, '0')}`
        console.log('onMounted: selectedWeek.value =', selectedWeek.value)
    } catch (error) {
        console.error('Ошибка инициализации недели:', error)
        // Fallback к текущей дате
        const now = new Date()
        selectedWeek.value = `${now.getFullYear()}-W01`
    }
})

const getWeekNumber = (date) => {
    try {
        if (!date || isNaN(date.getTime())) {
            throw new Error('Invalid date')
        }
        
        const year = date.getFullYear()
        const jan1 = new Date(year, 0, 1)
        
        if (isNaN(jan1.getTime())) {
            throw new Error('Invalid year')
        }
        
        // Находим первый понедельник года
        const firstMonday = new Date(jan1)
        const dayOfWeek = jan1.getDay()
        const daysToMonday = dayOfWeek === 0 ? 1 : 1 - dayOfWeek
        firstMonday.setDate(jan1.getDate() + daysToMonday)
        
        // Вычисляем количество дней с первого понедельника
        const daysDiff = Math.floor((date - firstMonday) / (24 * 60 * 60 * 1000))
        
        // Вычисляем номер недели
        const weekNumber = Math.floor(daysDiff / 7) + 1
        
        // Проверяем, что номер недели в разумных пределах
        if (weekNumber < 1 || weekNumber > 53) {
            throw new Error('Invalid week number')
        }
        
        return weekNumber
    } catch (error) {
        console.error('Ошибка в getWeekNumber:', error)
        return 1 // Fallback к первой неделе
    }
}

// Навигация по неделям
const previousWeek = () => {
    if (!selectedWeek.value) return
    
    try {
        // Получаем текущую дату начала недели
        const currentWeekStart = getCurrentWeekStart()
        if (!currentWeekStart) return
        
        // Переходим на предыдущую неделю
        const previousWeekStart = new Date(currentWeekStart)
        previousWeekStart.setDate(currentWeekStart.getDate() - 7)
        
        // Обновляем selectedWeek
        updateSelectedWeek(previousWeekStart)
        loadSchedule()
    } catch (error) {
        console.error('Ошибка в previousWeek:', error)
    }
}

const nextWeek = () => {
    if (!selectedWeek.value) return
    
    try {
        // Получаем текущую дату начала недели
        const currentWeekStart = getCurrentWeekStart()
        if (!currentWeekStart) return
        
        // Переходим на следующую неделю
        const nextWeekStart = new Date(currentWeekStart)
        nextWeekStart.setDate(currentWeekStart.getDate() + 7)
        
        // Обновляем selectedWeek
        updateSelectedWeek(nextWeekStart)
        loadSchedule()
    } catch (error) {
        console.error('Ошибка в nextWeek:', error)
    }
}

// Получение текущей даты начала недели
const getCurrentWeekStart = () => {
    if (!selectedWeek.value) return null
    
    try {
        const [year, week] = selectedWeek.value.split('-W')
        if (!year || !week) return null
        
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        return isNaN(weekStart.getTime()) ? null : weekStart
    } catch (error) {
        console.error('Ошибка в getCurrentWeekStart:', error)
        return null
    }
}

// Обновление selectedWeek на основе даты
const updateSelectedWeek = (date) => {
    try {
        const year = date.getFullYear()
        const week = getWeekNumber(date)
        
        if (week >= 1 && week <= 53) {
            selectedWeek.value = `${year}-W${week.toString().padStart(2, '0')}`
        }
    } catch (error) {
        console.error('Ошибка в updateSelectedWeek:', error)
    }
}

// Переход к текущей неделе
const goToCurrentWeek = () => {
    try {
        // Получаем текущую дату в правильном часовом поясе
        const now = new Date()
        const tashkentTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Tashkent"}))
        
        // Получаем начало текущей недели (понедельник)
        const weekStart = new Date(tashkentTime)
        const dayOfWeek = tashkentTime.getDay()
        const daysToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek
        weekStart.setDate(tashkentTime.getDate() + daysToMonday)
        
        // Обновляем selectedWeek
        updateSelectedWeek(weekStart)
        loadSchedule()
    } catch (error) {
        console.error('Ошибка в goToCurrentWeek:', error)
    }
}

// Форматирование отображения недели
const formatWeekDisplay = (weekString) => {
    if (!weekString) return ''
    
    try {
        const [year, week] = weekString.split('-W')
        if (!year || !week) return ''
        
        const weekNum = parseInt(week)
        const yearNum = parseInt(year)
        
        if (isNaN(weekNum) || isNaN(yearNum)) return ''
        
        // Получаем даты начала и конца недели для более понятного отображения
        const weekStart = getDateFromWeek(yearNum, weekNum)
        const weekEnd = new Date(weekStart)
        weekEnd.setDate(weekStart.getDate() + 6)
        
        const startDate = weekStart.toLocaleDateString('ru-RU', { 
            day: 'numeric', 
            month: 'short',
            timeZone: 'Asia/Tashkent'
        })
        const endDate = weekEnd.toLocaleDateString('ru-RU', { 
            day: 'numeric', 
            month: 'short',
            timeZone: 'Asia/Tashkent'
        })
        
        return `${startDate} - ${endDate} (неделя ${weekNum})`
    } catch (error) {
        console.error('Ошибка в formatWeekDisplay:', error)
        return ''
    }
}

// Получение дат начала и конца недели
const getWeekStartDate = (weekString) => {
    if (!weekString) return ''
    
    try {
        const [year, week] = weekString.split('-W')
        if (!year || !week) return ''
        
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        if (isNaN(weekStart.getTime())) return ''
        
        return weekStart.toLocaleDateString('ru-RU', { timeZone: 'Asia/Tashkent' })
    } catch (error) {
        console.error('Ошибка в getWeekStartDate:', error)
        return ''
    }
}

const getWeekEndDate = (weekString) => {
    if (!weekString) return ''
    
    try {
        const [year, week] = weekString.split('-W')
        if (!year || !week) return ''
        
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        if (isNaN(weekStart.getTime())) return ''
        
        const weekEnd = new Date(weekStart)
        weekEnd.setDate(weekStart.getDate() + 6)
        
        if (isNaN(weekEnd.getTime())) return ''
        
        return weekEnd.toLocaleDateString('ru-RU', { timeZone: 'Asia/Tashkent' })
    } catch (error) {
        console.error('Ошибка в getWeekEndDate:', error)
        return ''
    }
}

// Проверка, является ли день сегодняшним
const isToday = (dateString) => {
    const today = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Tashkent' })
    return dateString === today
}

const loadSchedule = async () => {
    if (!selectedDoctor.value || !selectedWeek.value) return
    
    try {
        const [year, week] = selectedWeek.value.split('-W')
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        
        const response = await makeAuthenticatedRequest(
            `/admin/schedule/get?doctor_id=${selectedDoctor.value}&week_start=${weekStart.toISOString().split('T')[0]}`
        )
        
        if (response.ok) {
        const data = await response.json()
            console.log('Загружено расписаний:', data.length)
            console.log('Данные расписания:', data)
        schedules.value = data
        } else {
            console.error('Ошибка загрузки расписания:', response.status, response.statusText)
        }
    } catch (error) {
        console.error('Ошибка загрузки расписания:', error)
    }
}

const getDateFromWeek = (year, week) => {
    try {
        console.log(`getDateFromWeek: year = ${year}, week = ${week}`)
        
        // Проверяем валидность входных параметров
        if (!year || !week || year < 1900 || year > 2100 || week < 1 || week > 53) {
            throw new Error('Invalid year or week')
        }
        
        // Используем простой подход: находим первый понедельник года
        const jan1 = new Date(year, 0, 1)
        console.log(`getDateFromWeek: jan1 = ${jan1}`)
        
        if (isNaN(jan1.getTime())) {
            throw new Error('Invalid year')
        }
        
        // Находим первый понедельник года
        const firstMonday = new Date(jan1)
        const dayOfWeek = jan1.getDay() // 0 = воскресенье, 1 = понедельник, ..., 6 = суббота
        const daysToMonday = dayOfWeek === 0 ? 1 : 1 - dayOfWeek // Если воскресенье, то +1, иначе 1 - день недели
        firstMonday.setDate(jan1.getDate() + daysToMonday)
        console.log(`getDateFromWeek: firstMonday = ${firstMonday}`)
        
        // Вычисляем начало нужной недели
        const weekStart = new Date(firstMonday)
        weekStart.setDate(firstMonday.getDate() + (week - 1) * 7)
        console.log(`getDateFromWeek: weekStart = ${weekStart}`)
        
        // Проверяем, что дата валидна
        if (isNaN(weekStart.getTime())) {
            throw new Error('Invalid week calculation')
        }
        
        return weekStart
    } catch (error) {
        console.error('Ошибка в getDateFromWeek:', error)
        // Возвращаем текущую дату как fallback
        return new Date()
    }
}

const getScheduleForDay = (date) => {
    const schedule = schedules.value.find(schedule => schedule.date === date && (schedule.is_working_day === true || schedule.is_working_day === 1))
    if (schedule) {
        console.log(`Найдено расписание для ${date}:`, schedule.start_time, '-', schedule.end_time)
    } else {
        console.log(`Расписание для ${date} не найдено. Всего расписаний:`, schedules.value.length)
        console.log('Доступные даты в расписаниях:', schedules.value.map(s => s.date))
        console.log('Проверяем is_working_day для всех расписаний:', schedules.value.map(s => ({date: s.date, is_working_day: s.is_working_day})))
    }
    return schedule
}

const getAnyScheduleForDay = (date) => {
    return schedules.value.find(schedule => schedule.date === date)
}

const addSchedule = (date) => {
    scheduleForm.date = date
    editingSchedule.value = null
    showScheduleModal.value = true
}

const editSchedule = (schedule) => {
    editingSchedule.value = schedule
    scheduleForm.date = schedule.date
    scheduleForm.start_time = schedule.start_time
    scheduleForm.end_time = schedule.end_time
    scheduleForm.slot_len_min = schedule.slot_len_min || 30
    scheduleForm.is_working_day = schedule.is_working_day
    scheduleForm.breaks = schedule.breaks || []
    showScheduleModal.value = true
}

const deleteSchedule = async (schedule) => {
    if (!confirm('Вы уверены, что хотите удалить это расписание?')) return
    
    try {
        const response = await makeAuthenticatedRequest(`/admin/schedule/${schedule.id}`, { 
            method: 'DELETE'
        })
        
        if (response.ok) {
        loadSchedule()
        } else {
            const errorData = await response.json()
            console.error('Ошибка удаления расписания:', errorData)
            alert('Ошибка при удалении расписания: ' + (errorData.message || 'Неизвестная ошибка'))
        }
    } catch (error) {
        console.error('Ошибка удаления расписания:', error)
        alert('Ошибка при удалении расписания: ' + error.message)
    }
}

const saveSchedule = async () => {
    try {
    const url = editingSchedule.value 
        ? `/admin/schedule/${editingSchedule.value.id}`
        : '/admin/schedule'
    
        const method = editingSchedule.value ? 'PUT' : 'POST'
    const data = {
        ...scheduleForm.data(),
        doctor_id: selectedDoctor.value
    }

        const response = await makeAuthenticatedRequest(url, {
            method: method,
            body: JSON.stringify(data)
        })
        
        if (response.ok) {
            const result = await response.json()
            console.log('Расписание сохранено:', result)
        loadSchedule()
        closeScheduleModal()
        } else {
            const errorData = await response.json()
            console.error('Ошибка сохранения расписания:', errorData)
            alert('Ошибка при сохранении расписания: ' + (errorData.message || 'Неизвестная ошибка'))
        }
    } catch (error) {
        console.error('Ошибка сохранения расписания:', error)
        alert('Ошибка при сохранении расписания: ' + error.message)
    }
}

const closeScheduleModal = () => {
    showScheduleModal.value = false
    scheduleForm.reset()
    scheduleForm.breaks = []
    editingSchedule.value = null
}

const openGenerateModal = () => {
    showGenerateModal.value = true
}

const closeGenerateModal = () => {
    showGenerateModal.value = false
    generateForm.reset()
    generateForm.working_days = [1, 2, 3, 4, 5]
    generateForm.breaks = []
}

// Управление перерывами
const addBreak = () => {
    generateForm.breaks.push({
        start: '12:00',
        end: '13:00'
    })
}

const removeBreak = (index) => {
    generateForm.breaks.splice(index, 1)
}

// Управление перерывами в расписании
const addScheduleBreak = () => {
    scheduleForm.breaks.push({
        start: '12:00',
        end: '13:00'
    })
}

const removeScheduleBreak = (index) => {
    scheduleForm.breaks.splice(index, 1)
}

// Быстрый выбор дней
const selectAllDays = () => {
    generateForm.working_days = [1, 2, 3, 4, 5, 6, 7]
}

const selectWeekdays = () => {
    generateForm.working_days = [1, 2, 3, 4, 5]
}

const clearAllDays = () => {
    generateForm.working_days = []
}

// Получение названий выбранных дней
const getSelectedDaysNames = () => {
    if (!weekDays.value || weekDays.value.length === 0) return ''
    
    return generateForm.working_days
        .map(dayNumber => {
            const dayIndex = dayNumber - 1
            const day = weekDays.value[dayIndex]
            return day ? day.shortName : `День ${dayNumber}`
        })
        .join(', ')
}

const generateWeek = async () => {
    try {
    const [year, week] = selectedWeek.value.split('-W')
    const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
    
        const response = await makeAuthenticatedRequest('/admin/schedule/generate', {
            method: 'POST',
            body: JSON.stringify({
        doctor_id: selectedDoctor.value,
        week_start: weekStart.toISOString().split('T')[0],
                start_time: generateForm.start_time,
                end_time: generateForm.end_time,
                slot_len_min: generateForm.slot_len_min,
                working_days: generateForm.working_days,
                breaks: generateForm.breaks || []
            })
        })
        
        if (response.ok) {
            const data = await response.json()
            console.log('Расписание сгенерировано:', data)
        loadSchedule()
        closeGenerateModal()
        } else {
            let errorMessage = 'Неизвестная ошибка'
            try {
                const errorData = await response.json()
                errorMessage = errorData.error || errorData.message || errorMessage
                console.error('Ошибка генерации расписания:', errorData)
            } catch (parseError) {
                console.error('Ошибка парсинга ответа:', parseError)
                errorMessage = `HTTP ${response.status}: ${response.statusText}`
            }
            alert('Ошибка при генерации расписания: ' + errorMessage)
        }
    } catch (error) {
        console.error('Ошибка генерации расписания:', error)
        alert('Ошибка при генерации расписания: ' + error.message)
    }
}
</script>