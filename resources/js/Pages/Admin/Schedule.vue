<template>
    <Head title="Управление расписанием" />

    <AppLayout title="Управление расписанием">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Управление расписанием</h1>
                <p class="text-gray-600">Настройка рабочего времени врачей</p>
            </div>
            <Link href="/admin" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Назад к админке
            </Link>
        </div>

        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Врач</span>
                        </label>
                        <select 
                            v-model="selectedDoctor" 
                            @change="loadSchedule"
                            class="select select-bordered w-full"
                        >
                            <option value="">Выберите врача</option>
                            <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                {{ doctor.user.name }} - {{ doctor.specialty.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Неделя</span>
                        </label>
                        <input 
                            type="week" 
                            v-model="selectedWeek"
                            @change="loadSchedule"
                            class="input input-bordered w-full"
                        />
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Действия</span>
                        </label>
                        <button 
                            @click="openGenerateModal"
                            :disabled="!selectedDoctor"
                            class="btn btn-primary"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Сгенерировать неделю
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="selectedDoctor && schedules.length > 0" class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Расписание на неделю
                </h2>
                
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
                                </div>
                                
                                <div class="flex flex-col space-y-1">
                                    <button 
                                        @click="editSchedule(getScheduleForDay(day.date))"
                                        class="btn btn-sm btn-outline"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Изменить
                                    </button>
                                    <button 
                                        @click="deleteSchedule(getScheduleForDay(day.date))"
                                        class="btn btn-sm btn-error"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Добавить
                                </button>
                            </div>
                        </div>
                    </div>
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

        <div class="modal" :class="{ 'modal-open': showScheduleModal }">
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

                    <div class="form-control mb-4">
                        <label class="label cursor-pointer">
                            <span class="label-text">Рабочий день</span>
                            <input 
                                type="checkbox" 
                                v-model="scheduleForm.is_working_day"
                                class="toggle toggle-primary"
                            />
                        </label>
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
                            {{ editingSchedule ? 'Обновить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" :class="{ 'modal-open': showGenerateModal }">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    Сгенерировать расписание на неделю
                </h3>

                <form @submit.prevent="generateWeek">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Время начала</span>
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
                                <span class="label-text">Время окончания</span>
                            </label>
                            <input 
                                type="time" 
                                v-model="generateForm.end_time"
                                class="input input-bordered w-full"
                                required
                            />
                        </div>
                    </div>

                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Рабочие дни</span>
                        </label>
                        <div class="grid grid-cols-7 gap-2">
                            <label v-for="(day, index) in weekDays" :key="index" class="flex flex-col items-center">
                                <input 
                                    type="checkbox" 
                                    :value="index + 1"
                                    v-model="generateForm.working_days"
                                    class="checkbox checkbox-primary"
                                />
                                <span class="text-xs mt-1">{{ day.shortName }}</span>
                            </label>
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
                            :disabled="generateForm.processing"
                        >
                            Сгенерировать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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
    is_working_day: true,
})

const generateForm = useForm({
    start_time: '09:00',
    end_time: '18:00',
    working_days: [1, 2, 3, 4, 5],
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
    
    try {
        const [year, week] = selectedWeek.value.split('-W')
        const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
        
        const response = await fetch(`/admin/schedule/get?doctor_id=${selectedDoctor.value}&week_start=${weekStart.toISOString().split('T')[0]}`)
        const data = await response.json()
        schedules.value = data
    } catch (error) {
        console.error('Ошибка загрузки расписания:', error)
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
    showScheduleModal.value = true
}

const editSchedule = (schedule) => {
    editingSchedule.value = schedule
    scheduleForm.date = schedule.date
    scheduleForm.start_time = schedule.start_time
    scheduleForm.end_time = schedule.end_time
    scheduleForm.is_working_day = schedule.is_working_day
    showScheduleModal.value = true
}

const deleteSchedule = async (schedule) => {
    if (!confirm('Вы уверены, что хотите удалить это расписание?')) return
    
    try {
        await fetch(`/admin/schedule/${schedule.id}`, { method: 'DELETE' })
        loadSchedule()
    } catch (error) {
        console.error('Ошибка удаления расписания:', error)
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
        closeScheduleModal()
    })
}

const closeScheduleModal = () => {
    showScheduleModal.value = false
    scheduleForm.reset()
    editingSchedule.value = null
}

const openGenerateModal = () => {
    showGenerateModal.value = true
}

const closeGenerateModal = () => {
    showGenerateModal.value = false
    generateForm.reset()
    generateForm.working_days = [1, 2, 3, 4, 5]
}

const generateWeek = () => {
    const [year, week] = selectedWeek.value.split('-W')
    const weekStart = getDateFromWeek(parseInt(year), parseInt(week))
    
    generateForm.post('/admin/schedule/generate', {
        doctor_id: selectedDoctor.value,
        week_start: weekStart.toISOString().split('T')[0],
        ...generateForm.data()
    })
    .then(() => {
        loadSchedule()
        closeGenerateModal()
    })
}
</script>