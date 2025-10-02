<script setup>
import { Head } from '@inertiajs/vue3';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';

const props = defineProps({
    title: { type: String, default: 'ClinicQueue' },
});
</script>

<template>
    <Head :title="props.title" />

    <div class="min-h-screen flex flex-col bg-base-100">
        <AppHeader />

        <main class="container mx-auto max-w-6xl px-4 py-6 grow">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
