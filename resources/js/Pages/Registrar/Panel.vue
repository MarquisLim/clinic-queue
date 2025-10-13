<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { useQueueUpdates } from '@/composables/useQueueUpdates';

const props = defineProps({
    doctors: Array
});

// Используем real-time обновления для панели регистратора
const { isConnected } = useQueueUpdates();

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

function checkIn(appointment) {
    router.post(route('registrar.appointments.check-in', appointment.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function cancelAppointment(appointment) {
    if (confirm('Вы уверены, что хотите отменить эту запись?')) {
        router.delete(route('registrar.appointments.cancel', appointment.id), {
            preserveState: true,
            preserveScroll: true,
        });
    }
}

function formatDateTime(str) {
    if (!str) return '';
    const [datePart, timePart] = str.split(' ');
    if (!datePart || !timePart) return str;

    const [y, m, d] = datePart.split('-');
    const [hh, mm] = timePart.split(':');
    const dateText = `${d}.${m}.${y}`;
    const timeText = `${hh}:${mm}`;
    return { dateText, timeText };
}

function getSpecialtiesText(doctor) {
    return doctor.specialties.map(s => s.name).join(', ');
}
</script>

<template>
    <AppLayout title="Панель регистратора">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">Панель регистратора</h1>
                        <p class="text-base-content/70 mt-2">
                            Управление очередями и записями пациентов
                        </p>
                    </div>
                    <div v-if="isConnected" class="badge badge-success">
                        🔴 Live обновления
                    </div>
                </div>
            </div>

            <!-- Общая статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Активных врачей</div>
                    <div class="stat-value text-primary">{{ doctors.length }}</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Всего в очередях</div>
                    <div class="stat-value text-info">{{ doctors.reduce((sum, d) => sum + d.queue_count, 0) }}</div>
                </div>
                <div class="stat bg-base-200 rounded-lg">
                    <div class="stat-title">Принимают сейчас</div>
                    <div class="stat-value text-success">{{ doctors.filter(d => d.current_patient).length }}</div>
                </div>
            </div>

            <!-- Список врачей и их очередей -->
            <div class="space-y-6">
                <div 
                    v-for="doctor in doctors" 
                    :key="doctor.id"
                    class="bg-base-100 rounded-lg shadow-md"
                >
                    <div class="p-6 border-b border-base-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold">{{ doctor.user.name }}</h2>
                                <p class="text-base-content/70">
                                    {{ getSpecialtiesText(doctor) }}
                                    <span v-if="doctor.room" class="ml-2 badge badge-outline">
                                        Кабинет {{ doctor.room }}
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-primary">{{ doctor.queue_count }}</div>
                                <div class="text-sm text-base-content/70">в очереди</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!doctor.appointments.length" class="p-8 text-center text-base-content/70">
                        <div class="text-4xl mb-2">📋</div>
                        <p>Очередь пуста</p>
                    </div>

                    <div v-else class="divide-y divide-base-300">
                        <div 
                            v-for="appointment in doctor.appointments" 
                            :key="appointment.id"
                            class="p-4 hover:bg-base-50 transition-colors"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="font-semibold">{{ appointment.patient.name }}</h3>
                                        <span class="badge" :class="statusClasses[appointment.status]">
                                            {{ statusMessages[appointment.status] }}
                                        </span>
                                        <span v-if="appointment.queue_position" class="badge badge-outline">
                                            #{{ appointment.queue_position }}
                                        </span>
                                    </div>
                                    
                                    <div class="text-sm text-base-content/70">
                                        <span class="font-mono">{{ appointment.ticket_no }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ formatDateTime(appointment.slot_start).timeText }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ appointment.slot_len_min }} мин</span>
                                    </div>

                                    <div v-if="appointment.complaint" class="text-sm text-base-content/60 mt-1">
                                        <strong>Жалобы:</strong> {{ appointment.complaint }}
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button 
                                        v-if="appointment.status === 'pending'"
                                        @click="checkIn(appointment)"
                                        class="btn btn-accent btn-sm"
                                    >
                                        Чек-ин
                                    </button>
                                    
                                    <button 
                                        v-if="appointment.status !== 'done' && appointment.status !== 'cancelled'"
                                        @click="cancelAppointment(appointment)"
                                        class="btn btn-error btn-sm"
                                    >
                                        Отменить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ссылка на журналы -->
            <div class="mt-8 text-center">
                <Link 
                    :href="route('registrar.status-logs')"
                    class="btn btn-outline"
                >
                    📊 Просмотр журналов изменений
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
