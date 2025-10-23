<template>
    <Head title="Тест расписания" />

    <AppLayout title="Тест расписания">
        <div class="max-w-6xl mx-auto">
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h1 class="card-title text-2xl mb-6">Тестирование системы расписания</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Выбор врача -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Врач</span>
                            </label>
                            <select v-model="selectedDoctor" class="select select-bordered">
                                <option value="">Выберите врача</option>
                                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                    {{ doctor.user.name }} - {{ doctor.specialty.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Выбор недели -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Неделя</span>
                            </label>
                            <input 
                                type="week" 
                                v-model="selectedWeek"
                                class="input input-bordered"
                            />
                        </div>

                        <!-- Действия -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Действия</span>
                            </label>
                            <div class="flex gap-2">
                                <button 
                                    @click="loadSchedule"
                                    :disabled="!selectedDoctor || !selectedWeek"
                                    class="btn btn-primary btn-sm"
                                >
                                    Загрузить
                                </button>
                                <button 
                                    @click="generateWeek"
                                    :disabled="!selectedDoctor || !selectedWeek"
                                    class="btn btn-secondary btn-sm"
                                >
                                    Сгенерировать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статус -->
            <div v-if="statusMessage" class="alert mb-6" :class="statusClass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ statusMessage }}</span>
            </div>

            <!-- Расписание -->
            <div v-if="schedules.length > 0" class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title mb-4">Расписание на неделю</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                        <div 
                            v-for="(day, index) in weekDays" 
                            :key="index"
                            class="card bg-base-200 shadow-sm"
                        >
                            <div class="card-body p-4">
                                <h3 class="card-title text-sm justify-center mb-3">
                                    {{ day.name }}
                                    <div class="badge badge-outline">{{ day.date }}</div>
                                </h3>
                                
                                <div v-if="getScheduleForDay(day.date)" class="space-y-2">
                                    <div class="stats stats-vertical shadow-sm">
                                        <div class="stat py-2">
                                            <div class="stat-title text-xs">Начало</div>
                                            <div class="stat-value text-sm">{{ getScheduleForDay(day.date).start_time }}</div>
                                        </div>
                                        <div class="stat py-2">
                                            <div class="stat-title text-xs">Конец</div>
                                            <div class="stat-value text-sm">{{ getScheduleForDay(day.date).end_time }}</div>
                                        </div>
                                        <div class="stat py-2">
                                            <div class="stat-title text-xs">Слот (мин)</div>
                                            <div class="stat-value text-sm">{{ getScheduleForDay(day.date).slot_len_min || 30 }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col space-y-1">
                                        <button 
                                            @click="editSchedule(getScheduleForDay(day.date))"
                                            class="btn btn-sm btn-outline"
                                        >
                                            Изменить
                                        </button>
                                        <button 
                                            @click="deleteSchedule(getScheduleForDay(day.date))"
                                            class="btn btn-sm btn-error"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center">
                                    <div class="text-gray-500 text-sm mb-2">Нет расписания</div>
                                    <button 
                                        @click="addSchedule(day.date)"
                                        class="btn btn-sm btn-primary"
                                    >
                                        Добавить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика -->
            <div v-if="stats" class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Статистика расписания</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Всего дней</div>
                            <div class="stat-value text-primary">{{ stats.total_days }}</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Рабочих дней</div>
                            <div class="stat-value text-secondary">{{ stats.working_days }}</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Всего часов</div>
                            <div class="stat-value text-accent">{{ stats.total_hours }}</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Средний слот</div>
                            <div class="stat-value text-info">{{ Math.round(stats.avg_slot_duration) }} мин</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модальное окно для редактирования -->
            <div class="modal" :class="{ 'modal-open': showModal }">
                <div class="modal-box">
                    <h3 class="font-bold text-lg mb-4">
                        {{ editingSchedule ? 'Редактировать расписание' : 'Добавить расписание' }}
                    </h3>

                    <form @submit.prevent="saveSchedule">
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text">Дата</span>
                            </label>
                            <input 
                                type="date" 
                                v-model="scheduleForm.date"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text">Время начала</span>
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
                                    <span class="label-text">Время окончания</span>
                                </label>
                                <input 
                                    type="time" 
                                    v-model="scheduleForm.end_time"
                                    class="input input-bordered w-full"
                                    required
                                />
                            </div>
                        </div>

                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text">Длительность слота (минуты)</span>
                            </label>
                            <input 
                                type="number" 
                                v-model="scheduleForm.slot_len_min"
                                class="input input-bordered w-full"
                                min="5"
                                max="180"
                            />
                        </div>

                        <div class="modal-action">
                            <button 
                                type="button"
                                @click="closeModal"
                                class="btn btn-ghost"
                            >
                                Отмена
                            </button>
                            <button 
                                type="submit"
                                class="btn btn-primary"
                                :disabled="loading"
                            >
                                {{ editingSchedule ? 'Обновить' : 'Создать' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    doctors: Array
})

const selectedDoctor = ref('')
const selectedWeek = ref('')
const schedules = ref([])
const stats = ref(null)
const loading = ref(false)
const statusMessage = ref('')
const statusClass = ref('alert-info')
const showModal = ref(false)
const editingSchedule = ref(null)

const scheduleForm = useForm({
    date: '',
    start_time: '09:00',
    end_time: '18:00',
    slot_len_min: 30,
})

const weekDays = computed(() => {
    if (!selectedWeek.value) return []
    
    const weekStart = new Date(selectedWeek.value + 'T00:00:00')
    const days = []
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(weekStart)
        date.setDate(weekStart.getDate() + i)
        
        days.push({
            name: date.toLocaleDateString('ru-RU', { weekday: 'long' }),
            shortName: date.toLocaleDateString('ru-RU', { weekday: 'short' }),
            date: date.toISOString().split('T')[0]
        })
    }
    
    return days
})

onMounted(() => {
    const now = new Date()
    const weekStart = new Date(now)
    weekStart.setDate(now.getDate() - now.getDay() + 1)
    
    const year = weekStart.getFullYear()
    const weekNumber = getWeekNumber(weekStart)
    selectedWeek.value = `${year}-W${weekNumber.toString().padStart(2, '0')}`
})

const getWeekNumber = (date) => {
    const firstDayOfYear = new Date(date.getFullYear(), 0, 1)
    const pastDaysOfYear = (date - firstDayOfYear) / 86400000
    return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7)
}

const loadSchedule = async () => {
    if (!selectedDoctor.value || !selectedWeek.value) return
    
    loading.value = true
    try {
        const [year, week] = selectedWeek.value.split('-W')
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        
        const response = await axios.get(`/admin/schedule/get?doctor_id=${selectedDoctor.value}&week_start=${weekStart.toISOString().split('T')[0]}`)
        schedules.value = response.data
        
        // Загружаем статистику
        await loadStats()
        
        updateStatus('Расписание загружено', 'success')
    } catch (error) {
        updateStatus('Ошибка загрузки расписания', 'error')
        console.error('Error loading schedule:', error)
    } finally {
        loading.value = false
    }
}

const loadStats = async () => {
    if (!selectedDoctor.value) return
    
    try {
        const response = await axios.get(`/admin/schedule/stats?doctor_id=${selectedDoctor.value}`)
        stats.value = response.data
    } catch (error) {
        console.error('Error loading stats:', error)
    }
}

const getDateFromWeek = (year, week) => {
    const simple = new Date(year, 0, 1 + (week - 1) * 7)
    const dow = simple.getDay()
    const ISOweekStart = simple
    if (dow <= 4) {
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1)
    } else {
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay())
    }
    return ISOweekStart
}

const getScheduleForDay = (date) => {
    return schedules.value.find(schedule => schedule.date === date)
}

const addSchedule = (date) => {
    scheduleForm.date = date
    editingSchedule.value = null
    showModal.value = true
}

const editSchedule = (schedule) => {
    editingSchedule.value = schedule
    scheduleForm.date = schedule.date
    scheduleForm.start_time = schedule.start_time
    scheduleForm.end_time = schedule.end_time
    scheduleForm.slot_len_min = schedule.slot_len_min || 30
    showModal.value = true
}

const deleteSchedule = async (schedule) => {
    if (!confirm('Вы уверены, что хотите удалить это расписание?')) return
    
    try {
        await axios.delete(`/admin/schedule/${schedule.id}`)
        loadSchedule()
        updateStatus('Расписание удалено', 'success')
    } catch (error) {
        updateStatus('Ошибка удаления расписания', 'error')
        console.error('Error deleting schedule:', error)
    }
}

const saveSchedule = () => {
    const url = editingSchedule.value 
        ? `/admin/schedule/${editingSchedule.value.id}`
        : '/admin/schedule'
    
    const method = editingSchedule.value ? 'put' : 'post'
    const data = {
        ...scheduleForm.data(),
        doctor_id: selectedDoctor.value
    }

    scheduleForm[method](url, data)
    .then(() => {
        loadSchedule()
        closeModal()
        updateStatus(editingSchedule.value ? 'Расписание обновлено' : 'Расписание создано', 'success')
    })
    .catch((error) => {
        updateStatus('Ошибка сохранения расписания', 'error')
        console.error('Error saving schedule:', error)
    })
}

const generateWeek = async () => {
    if (!selectedDoctor.value || !selectedWeek.value) return
    
    try {
        const [year, week] = selectedWeek.value.split('-W')
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        
        const response = await axios.post('/admin/schedule/generate', {
            doctor_id: selectedDoctor.value,
            week_start: weekStart.toISOString().split('T')[0],
            start_time: '09:00',
            end_time: '18:00',
            working_days: [1, 2, 3, 4, 5], // Пн-Пт
            slot_len_min: 30
        })
        
        loadSchedule()
        updateStatus('Расписание на неделю сгенерировано', 'success')
    } catch (error) {
        updateStatus('Ошибка генерации расписания', 'error')
        console.error('Error generating schedule:', error)
    }
}

const closeModal = () => {
    showModal.value = false
    scheduleForm.reset()
    editingSchedule.value = null
}

const updateStatus = (message, type) => {
    statusMessage.value = message
    statusClass.value = `alert-${type}`
    
    setTimeout(() => {
        statusMessage.value = ''
    }, 5000)
}
</script>
