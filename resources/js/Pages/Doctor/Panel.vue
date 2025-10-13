<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useQueueUpdates } from '@/composables/useQueueUpdates';

const props = defineProps({
    doctor: {
        type: Object,
        default: () => ({})
    },
    appointments: {
        type: Array,
        default: () => []
    }
});

// Используем real-time обновления для панели врача
const { isConnected, refreshQueueData } = useQueueUpdates(null, props.doctor?.id || null);

const statusMessages = {
    pending: 'Ожидает',
    checked_in: 'Прибыл',
    in_progress: 'Идёт приём',
    done: 'Завершён',
    cancelled: 'Отменён',
};

const statusClasses = {
    pending: 'badge-warning',
    checked_in: 'badge-accent',
    in_progress: 'badge-info',
    done: 'badge-success',
    cancelled: 'badge-ghost',
};

function updateStatus(appointment, newStatus) {
    router.patch(route('doctor.appointments.update-status', appointment.id), {
        status: newStatus
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatDateTime(str) {
    if (!str) return { dateText: '', timeText: '' };
    const [datePart, timePart] = str.split(' ');
    if (!datePart || !timePart) return { dateText: str, timeText: '' };

    const [y, m, d] = datePart.split('-');
    const [hh, mm] = timePart.split(':');
    const dateText = `${d}.${m}.${y}`;
    const timeText = `${hh}:${mm}`;
    return { dateText, timeText };
}

function getStatusActions(appointment) {
    const actions = [];
    
    switch (appointment.status) {
        case 'pending':
            actions.push({ label: 'Отметить прибытие', status: 'checked_in', class: 'btn-accent' });
            break;
        case 'checked_in':
            actions.push({ label: 'Начать приём', status: 'in_progress', class: 'btn-primary' });
            break;
        case 'in_progress':
            actions.push({ label: 'Завершить приём', status: 'done', class: 'btn-success' });
            break;
    }
    
    return actions;
}
</script>

<template>
    <AppLayout title="Панель врача">
        <div class="max-w-7xl mx-auto">
            <!-- Загрузка данных -->
            <div v-if="!doctor || !doctor.id" class="text-center py-8">
                <div class="loading loading-spinner loading-lg"></div>
                <p class="mt-4 text-base-content/70">Загрузка данных врача...</p>
            </div>
            
            <!-- Основной контент -->
            <div v-else>
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">Панель врача</h1>
                        <p class="text-base-content/70 mt-2">
                            Добро пожаловать, {{ doctor?.user?.name || 'Врач' }}! 
                            Сегодня у вас {{ appointments?.length || 0 }} записей.
                        </p>
                    </div>
                    <div v-if="isConnected" class="badge badge-success">
                        🔴 Live обновления
                    </div>
                </div>
            </div>

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Всего записей</div>
                    <div class="stat-value text-primary">{{ appointments?.length || 0 }}</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Ожидают</div>
                    <div class="stat-value text-warning">{{ appointments?.filter(a => a.status === 'pending').length || 0 }}</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Прибыли</div>
                    <div class="stat-value text-accent">{{ appointments?.filter(a => a.status === 'checked_in').length || 0 }}</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">В процессе</div>
                    <div class="stat-value text-info">{{ appointments?.filter(a => a.status === 'in_progress').length || 0 }}</div>
                </div>
            </div>

            <!-- Список пациентов -->
            <div class="bg-base-100 rounded-lg shadow-md">
                <div class="p-6 border-b border-base-300">
                    <h2 class="text-xl font-semibold">Пациенты на сегодня</h2>
                </div>
                
                <div v-if="!appointments?.length" class="p-8 text-center text-base-content/70">
                    <div class="text-6xl mb-4">📋</div>
                    <p>На сегодня записей нет</p>
                </div>

                <div v-else class="divide-y divide-base-300">
                    <div 
                        v-for="appointment in appointments" 
                        :key="appointment.id"
                        class="p-6 hover:bg-base-50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold">{{ appointment.patient?.name || 'Неизвестный пациент' }}</h3>
                                    <span class="badge" :class="statusClasses[appointment.status]">
                                        {{ statusMessages[appointment.status] }}
                                    </span>
                                    <span v-if="appointment.queue_position" class="badge badge-outline">
                                        Позиция: {{ appointment.queue_position }}
                                    </span>
                                </div>
                                
                                <div class="text-sm text-base-content/70 mb-2">
                                    <span class="font-mono">{{ appointment.ticket_no || 'N/A' }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ appointment.specialty?.name || 'Неизвестная специальность' }}</span>
                                </div>

                                <div v-if="appointment.complaint" class="text-sm bg-base-200 rounded p-2 mb-2">
                                    <strong>Жалобы:</strong> {{ appointment.complaint }}
                                </div>

                                <div class="flex items-center gap-4 text-sm text-base-content/60">
                                    <div>
                                        <span class="font-medium">Время:</span> 
                                        {{ formatDateTime(appointment.slot_start)?.timeText || 'N/A' }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Длительность:</span> 
                                        {{ appointment.slot_len_min || 0 }} мин
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <div v-for="action in getStatusActions(appointment)" :key="action.status">
                                    <button 
                                        @click="updateStatus(appointment, action.status)"
                                        class="btn btn-sm"
                                        :class="action.class"
                                    >
                                        {{ action.label }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </AppLayout>
</template>
