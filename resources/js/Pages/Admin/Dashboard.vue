<template>
    <Head title="Админ панель" />

    <AppLayout title="Админ панель">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Админ панель</h1>
            <p class="text-gray-600">Управление системой клиники</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Специальности</div>
                    <div class="stat-value text-primary">{{ stats.specialties }}</div>
                    <div class="stat-desc">Медицинские направления</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Врачи</div>
                    <div class="stat-value text-secondary">{{ stats.doctors }}</div>
                    <div class="stat-desc">Работающих специалистов</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Пользователи</div>
                    <div class="stat-value text-accent">{{ stats.users }}</div>
                    <div class="stat-desc">Зарегистрированных</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Записи сегодня</div>
                    <div class="stat-value text-info">{{ stats.appointments_today }}</div>
                    <div class="stat-desc">На прием</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Специальности
                    </h2>
                    <p>Управление медицинскими специальностями</p>
                    <div class="card-actions justify-end">
                        <Link href="/admin/specialties" class="btn btn-primary">Управление</Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Врачи
                    </h2>
                    <p>Управление врачами и их профилями</p>
                    <div class="card-actions justify-end">
                        <Link href="/admin/doctors" class="btn btn-secondary">Управление</Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        Пользователи
                    </h2>
                    <p>Управление ролями пользователей</p>
                    <div class="card-actions justify-end">
                        <Link href="/admin/users" class="btn btn-accent">Управление</Link>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Расписание
                    </h2>
                    <p>Управление расписанием врачей</p>
                    <div class="card-actions justify-end">
                        <Link href="/admin/schedule" class="btn btn-info">Управление</Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Последние записи</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Пациент</th>
                                    <th>Врач</th>
                                    <th>Время</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="appointment in recentAppointments" :key="appointment.id">
                                    <td>{{ appointment.patient.name }}</td>
                                    <td>{{ appointment.doctor.user.name }}</td>
                                    <td>{{ formatDateTime(appointment.slot_start) }}</td>
                                    <td>
                                        <div class="badge" :class="getStatusBadgeClass(appointment.status)">
                                            {{ getStatusText(appointment.status) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    stats: Object,
    recentAppointments: Array
})

const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleString('ru-RU')
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'badge-warning',
        'checked_in': 'badge-info',
        'in_progress': 'badge-primary',
        'completed': 'badge-success',
        'cancelled': 'badge-error',
        'no_show': 'badge-neutral'
    }
    return classes[status] || 'badge-neutral'
}

const getStatusText = (status) => {
    const texts = {
        'pending': 'Ожидает',
        'checked_in': 'Прибыл',
        'in_progress': 'На приеме',
        'completed': 'Завершен',
        'cancelled': 'Отменен',
        'no_show': 'Не явился'
    }
    return texts[status] || status
}
</script>