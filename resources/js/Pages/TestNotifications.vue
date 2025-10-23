<template>
    <Head title="Тест уведомлений" />

    <AppLayout title="Тест уведомлений">
        <div class="max-w-4xl mx-auto">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h1 class="card-title text-2xl mb-6">Тестирование системы уведомлений</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Информация о тестовых данных -->
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <h2 class="card-title text-lg">Тестовые данные</h2>
                                <div class="space-y-2">
                                    <p><strong>Пациент:</strong> patient@test.com / password</p>
                                    <p><strong>Врач:</strong> doctor@test.com / password</p>
                                    <p><strong>Запись на:</strong> {{ appointmentTime }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <h2 class="card-title text-lg">Действия</h2>
                                <div class="space-y-3">
                                    <button @click="sendTestReminder" class="btn btn-primary w-full">
                                        Отправить тестовое напоминание
                                    </button>
                                    <button @click="checkNotifications" class="btn btn-outline w-full">
                                        Проверить уведомления
                                    </button>
                                    <button @click="createTestAppointment" class="btn btn-secondary w-full">
                                        Создать тестовую запись
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Статус -->
                    <div class="mt-6">
                        <div class="alert" :class="statusClass">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ statusMessage }}</span>
                        </div>
                    </div>

                    <!-- Лог событий -->
                    <div v-if="events.length > 0" class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">События real-time</h3>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            <div 
                                v-for="event in events" 
                                :key="event.id"
                                class="p-3 bg-base-200 rounded-lg text-sm"
                            >
                                <div class="font-medium">{{ event.type }}</div>
                                <div class="text-base-content/70">{{ event.message }}</div>
                                <div class="text-xs text-base-content/50">{{ event.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const appointmentTime = ref('')
const statusMessage = ref('Готов к тестированию')
const statusClass = ref('alert-info')
const events = ref([])

let echo = null

onMounted(() => {
    // Получаем время ближайшей записи
    loadAppointmentTime()
    
    // Настраиваем real-time события
    if (window.Echo) {
        echo = window.Echo
        
        // Слушаем канал уведомлений
        echo.channel(`notifications.${window.Laravel.user.id}`)
            .listen('.appointment.reminder', (e) => {
                addEvent('Напоминание о записи', e.message)
                updateStatus('Получено напоминание о записи!', 'success')
            })
    }
})

onUnmounted(() => {
    if (echo) {
        echo.leave(`notifications.${window.Laravel.user.id}`)
    }
})

const loadAppointmentTime = async () => {
    try {
        const response = await axios.get('/api/test/appointment-time')
        appointmentTime.value = response.data.time
    } catch (error) {
        console.error('Error loading appointment time:', error)
    }
}

const sendTestReminder = async () => {
    try {
        updateStatus('Отправка тестового напоминания...', 'info')
        await axios.post('/api/test/send-reminder')
        updateStatus('Тестовое напоминание отправлено!', 'success')
        addEvent('Тестовое напоминание', 'Отправлено тестовое напоминание')
    } catch (error) {
        updateStatus('Ошибка отправки напоминания', 'error')
        console.error('Error sending test reminder:', error)
    }
}

const checkNotifications = async () => {
    try {
        const response = await axios.get('/notifications/recent')
        const count = response.data.length
        updateStatus(`Найдено ${count} уведомлений`, 'info')
        addEvent('Проверка уведомлений', `Найдено ${count} уведомлений`)
    } catch (error) {
        updateStatus('Ошибка проверки уведомлений', 'error')
        console.error('Error checking notifications:', error)
    }
}

const createTestAppointment = async () => {
    try {
        updateStatus('Создание тестовой записи...', 'info')
        await axios.post('/api/test/create-appointment')
        updateStatus('Тестовая запись создана!', 'success')
        addEvent('Создание записи', 'Создана новая тестовая запись')
        loadAppointmentTime()
    } catch (error) {
        updateStatus('Ошибка создания записи', 'error')
        console.error('Error creating test appointment:', error)
    }
}

const updateStatus = (message, type) => {
    statusMessage.value = message
    statusClass.value = `alert-${type}`
}

const addEvent = (type, message) => {
    events.value.unshift({
        id: Date.now(),
        type,
        message,
        time: new Date().toLocaleTimeString('ru-RU')
    })
    
    // Ограничиваем количество событий
    if (events.value.length > 10) {
        events.value = events.value.slice(0, 10)
    }
}
</script>
