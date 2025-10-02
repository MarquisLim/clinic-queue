<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SlotList from '@/Components/SlotList.vue';

const props = defineProps({
    doctor: Object,
    date: String,
    slots: Array,
    closed: Boolean,
    reason: String
});
</script>

<template>
    <AppLayout title="Выбор времени">
        <h1 class="text-2xl font-bold mb-4">Доступные слоты</h1>

        <div v-if="props.closed" class="alert alert-warning">
            День недоступен: {{ props.reason }}
        </div>

        <SlotList v-else :slots="props.slots" :doctor="props.doctor" :date="props.date" />
    </AppLayout>
</template>
