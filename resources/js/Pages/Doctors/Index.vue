<!-- resources/js/Pages/Doctors/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DoctorCard from '@/Components/DoctorCard.vue';

const props = defineProps({
    doctors: Array,
    selectedSpec: Number
});
</script>

<template>
    <AppLayout title="Список врачей">
        <h1 class="text-2xl font-bold mb-6">Список врачей</h1>

        <div class="flex flex-col gap-6">
            <DoctorCard
                v-for="doc in doctors"
                :key="doc.id"
                :doctor="doc"
                :selected-spec-id="props.selectedSpec"
            />
        </div>
    </AppLayout>
</template>
