<script setup>
import { router } from '@inertiajs/vue3';
const props = defineProps({
    slots: Array, // [{start, iso, available}]
    doctor: Object,
    date: String
});

function book(slot) {
    router.post(route('appointments.store'), {
        doctor_id: props.doctor.id,
        slot_start: slot.iso
    });
}
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <button
            v-for="s in slots"
            :key="s.iso"
            class="btn btn-sm rounded-full border-base-300"
            :class="s.available ? 'btn-outline hover:btn-success' : 'btn-disabled'"
            @click="s.available && book(s)">
            <span class="font-mono">{{ s.start }}</span>
        </button>
    </div>
</template>
