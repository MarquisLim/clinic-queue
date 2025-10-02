<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    specialty: { type: Object, required: true } // {id, name, description, image_url}
});
</script>

<template>
    <Link
        :href="route('doctors.index', { specialty_id: specialty.id })"
        class="card bg-base-100 rounded-2xl shadow-md hover:shadow-lg transition-all duration-200 w-80 h-64 p-6 flex flex-col justify-between gap-4"
    >
        <div class="space-y-1">
            <h2 class="text-xl font-bold text-base-content">{{ specialty.name }}</h2>
            <p v-if="specialty.description" class="text-sm text-base-content/70 leading-snug">
                {{ specialty.description }}
            </p>
        </div>

        <div class="flex justify-end items-end">
            <img :src="specialty.image_url" :alt="specialty.name" class="w-20 h-20 object-cover rounded-full shadow" />
        </div>
    </Link>
</template>
