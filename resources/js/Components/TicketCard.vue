<script setup>
import { Link } from '@inertiajs/vue3';
import { useQueueUpdates } from '@/composables/useQueueUpdates';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    appointment: { type: Object, required: true }, // { id, slot_start, status, ticket_no, doctor:{user:{name}} }
    history: { type: Boolean, default: false }
});

// Use real-time updates only for active appointments
const { isConnected } = useQueueUpdates(
    props.history ? null : props.appointment.patient_id,
    props.history ? null : props.appointment.doctor_id
);

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
const dt = formatDateTime(props.appointment.slot_start);

const statusRu = {
    pending:     'Ожидает',
    checked_in:  'Отмечен',
    in_progress: 'Идёт приём',
    done:        'Завершён',
    cancelled:   'Отменён',
};

function statusClass(s) {
    return {
        pending:     'badge-warning',
        checked_in:  'badge-accent',
        in_progress: 'badge-info',
        done:        'badge-success',
        cancelled:   'badge-ghost',
    }[s] || 'badge-ghost';
}

// Functions for handling appointment cancellation
const confirmCancel = () => {
    return confirm('Вы уверены, что хотите отменить запись?');
};

const handleCancelError = (error) => {
    console.error('Ошибка при отмене записи:', error);
    alert('Произошла ошибка при отмене записи. Попробуйте еще раз.');
};

// Check if appointment can be cancelled (at least 10 minutes before)
const canCancel = computed(() => {
    if (props.history || props.appointment.status !== 'pending') {
        return false;
    }
    
    const appointmentTime = new Date(props.appointment.slot_start);
    const now = new Date();
    const diffMinutes = (appointmentTime - now) / (1000 * 60);
    
    return diffMinutes >= 10;
});

// Get time until appointment information
const timeUntilAppointment = computed(() => {
    if (props.history || props.appointment.status !== 'pending') {
        return null;
    }
    
    const appointmentTime = new Date(props.appointment.slot_start);
    const now = new Date();
    const diffMinutes = Math.floor((appointmentTime - now) / (1000 * 60));
    
    if (diffMinutes < 0) {
        return 'Запись уже началась';
    } else if (diffMinutes < 60) {
        return `Через ${diffMinutes} мин`;
    } else {
        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;
        return `Через ${hours}ч ${minutes}мин`;
    }
});
</script>

<template>
    <!-- ticket -->
    <div
        class="relative bg-base-100 rounded-2xl shadow-md border border-base-300 overflow-hidden"
    >
        <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-px border-l border-dashed border-base-300 pointer-events-none"></div>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-8 bg-base-100 rounded-r-full border border-base-300 border-l-0"></div>
        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-8 bg-base-100 rounded-l-full border border-base-300 border-r-0"></div>

        <div class="grid grid-cols-1 md:grid-cols-2">
            <div class="p-5 flex flex-col gap-2">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <h2 class="text-lg font-bold">
                        Врач: {{ appointment.doctor?.user?.name }}
                    </h2>
                    <span class="badge" :class="statusClass(appointment.status)">
                        {{ statusRu[appointment.status] || appointment.status }}
                    </span>
                </div>

                <div class="text-sm text-base-content/70">
                    <span v-if="appointment.ticket_no" class="font-mono">Талон: {{ appointment.ticket_no }}</span>
                    <span v-if="appointment.room" class="badge badge-outline">
                        Кабинет: {{ appointment.room }}
                    </span>

                </div>
                <div v-if="appointment.queue_position" class="badge badge-outline">
                    Ваша позиция: {{ appointment.queue_position }}
                </div>
                <div v-if="!history && isConnected" class="badge badge-success badge-sm">
                    🔴 Live
                </div>
            </div>

            <div class="p-5 border-t md:border-t-0 md:border-l border-base-300">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-base-content/60">Дата</div>
                        <div class="text-2xl font-bold leading-none">{{ dt?.dateText || appointment.slot_start }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs uppercase tracking-wide text-base-content/60">Время</div>
                        <div class="text-3xl font-extrabold leading-none font-mono">{{ dt?.timeText || '' }}</div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <Link
                        v-if="canCancel"
                        :href="route('appointments.destroy', appointment.id)"
                        method="delete"
                        as="button"
                        class="btn btn-error btn-sm rounded-full"
                        :on-before="confirmCancel"
                        :on-error="handleCancelError"
                    >
                        Отменить
                    </Link>
                    <div v-else-if="!history && appointment.status === 'pending'" class="text-sm text-gray-500 text-right">
                        <div>{{ timeUntilAppointment }}</div>
                        <div class="text-xs">Отмена возможна не позднее чем за 10 минут</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
