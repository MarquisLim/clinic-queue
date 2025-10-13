<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array
});

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

function formatDateTime(str) {
    if (!str) return '';
    const date = new Date(str);
    return date.toLocaleString('ru-RU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function getChangedByText(log) {
    const meta = log.meta || {};
    const changedBy = meta.changed_by;
    
    switch (changedBy) {
        case 'doctor':
            return 'Врач';
        case 'registrar':
            return 'Регистратор';
        case 'patient':
            return 'Пациент';
        default:
            return 'Система';
    }
}
</script>

<template>
    <AppLayout title="Журналы изменений статусов">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">Журналы изменений статусов</h1>
                        <p class="text-base-content/70 mt-2">
                            История всех изменений статусов записей
                        </p>
                    </div>
                    <Link 
                        :href="route('registrar.panel')"
                        class="btn btn-outline"
                    >
                        ← Назад к панели
                    </Link>
                </div>
            </div>

            <div class="bg-base-100 rounded-lg shadow-md">
                <div class="p-6 border-b border-base-300">
                    <h2 class="text-xl font-semibold">Последние изменения</h2>
                    <p class="text-base-content/70 text-sm mt-1">
                        Показано {{ logs.length }} последних записей
                    </p>
                </div>

                <div v-if="!logs.length" class="p-8 text-center text-base-content/70">
                    <div class="text-6xl mb-4">📝</div>
                    <p>Журналы пусты</p>
                </div>

                <div v-else class="divide-y divide-base-300">
                    <div 
                        v-for="log in logs" 
                        :key="log.id"
                        class="p-6 hover:bg-base-50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold">
                                        {{ log.appointment?.patient?.name || 'Неизвестный пациент' }}
                                    </h3>
                                    <span class="badge badge-outline">
                                        {{ log.appointment?.ticket_no || 'N/A' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mb-2">
                                    <span class="badge" :class="statusClasses[log.from_status]">
                                        {{ statusMessages[log.from_status] }}
                                    </span>
                                    <span class="text-base-content/50">→</span>
                                    <span class="badge" :class="statusClasses[log.to_status]">
                                        {{ statusMessages[log.to_status] }}
                                    </span>
                                </div>

                                <div class="text-sm text-base-content/70">
                                    <div>
                                        <strong>Врач:</strong> {{ log.appointment?.doctor?.user?.name || 'Неизвестно' }}
                                    </div>
                                    <div>
                                        <strong>Изменил:</strong> {{ getChangedByText(log) }}
                                        <span v-if="log.user?.name"> ({{ log.user.name }})</span>
                                    </div>
                                    <div v-if="log.meta?.reason">
                                        <strong>Причина:</strong> {{ log.meta.reason }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-right text-sm text-base-content/60">
                                <div class="font-mono">
                                    {{ formatDateTime(log.changed_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

