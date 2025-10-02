<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Запись к врачу">
        <div class="prose max-w-none">
            <h1>Запись к врачу</h1>
            <p>Выберите специальность, затем врача и подходящий слот.</p>
            <div class="mt-4">
                <a class="btn btn-primary" href="/specialties">Перейти к специальностям</a>
            </div>
        </div>
    </AppLayout>
</template>
