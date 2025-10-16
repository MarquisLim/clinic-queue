<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketCard from '@/Components/TicketCard.vue';
import { useQueueUpdates } from '@/composables/useQueueUpdates';
import { ref, computed } from 'vue';

const props = defineProps({
    upcoming: Array,
    history: Array
});

// Получаем ID текущего пользователя из первого upcoming appointment
const currentUserId = computed(() => {
    return props.upcoming?.[0]?.patient_id || null;
});

// Используем real-time обновления для страницы пациента
const { isConnected, refreshQueueData } = useQueueUpdates(currentUserId.value, null);
</script>

<template>
    <AppLayout title="Мои записи">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Предстоящие визиты</h1>
            <div v-if="isConnected && props.upcoming.length" class="badge badge-success">
                🔴 Live обновления
            </div>
        </div>

        <div v-if="!props.upcoming.length" class="text-gray-500">Нет активных записей.</div>
        <div class="grid gap-4">
            <TicketCard v-for="appt in props.upcoming" :key="appt.id" :appointment="appt" />
        </div>

        <h1 class="text-2xl font-bold mt-8 mb-4">История</h1>
        <div v-if="!props.history.length" class="text-gray-500">История пуста.</div>
        <div class="grid gap-4">
            <TicketCard v-for="appt in props.history" :key="appt.id" :appointment="appt" history />
        </div>
    </AppLayout>
</template>
