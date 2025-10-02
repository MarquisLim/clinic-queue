<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketCard from '@/Components/TicketCard.vue';

const props = defineProps({
    upcoming: Array,
    history: Array
});
</script>

<template>
    <AppLayout title="Мои записи">
        <h1 class="text-2xl font-bold mb-4">Предстоящие визиты</h1>

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
