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

        <!-- Список врачей -->
        <div v-if="doctors && doctors.length > 0" class="flex flex-col gap-6">
            <DoctorCard
                v-for="doc in doctors"
                :key="doc.id"
                :doctor="doc"
                :selected-spec-id="props.selectedSpec"
            />
        </div>

        <!-- Сообщение когда нет врачей -->
        <div v-else class="text-center py-12">
            <div class="max-w-md mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Врачи пока не добавлены</h3>
                <p class="text-gray-500 mb-6">
                    <span v-if="selectedSpec">Для выбранной специальности врачи еще не добавлены.</span>
                    <span v-else>В системе пока нет зарегистрированных врачей.</span>
                </p>
                <div class="text-sm text-gray-400">
                    Обратитесь к администратору для добавления врачей
                </div>
            </div>
        </div>
    </AppLayout>
</template>
