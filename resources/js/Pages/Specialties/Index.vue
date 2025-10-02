<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SpecialtyCard from '@/Components/SpecialtyCard.vue';

const props = defineProps({
    items: Array
});
</script>

<template>
    <AppLayout title="Выбор специализации">
        <h1 class="text-2xl font-bold mb-4">Выберите специализацию</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <SpecialtyCard
                v-for="spec in props.items"
                :key="spec.id"
                :specialty="spec"
            />
        </div>
    </AppLayout>
</template>
