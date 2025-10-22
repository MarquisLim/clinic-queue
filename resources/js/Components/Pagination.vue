<template>
    <nav v-if="links && links.length > 1" class="flex items-center justify-center gap-1 mt-6 select-none">
        <component
            v-for="(link, idx) in links"
            :key="idx"
            :is="resolveTag(link)"
            :href="link.url || undefined"
            class="min-w-9 h-9 inline-flex items-center justify-center rounded-md px-3 text-sm"
            :class="buttonClass(link)"
            preserve-scroll
            preserve-state
            replace
        >
            <span v-html="label(link)"></span>
        </component>
    </nav>
    <div v-else class="mt-6"></div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: { type: Array, required: true }
})

const resolveTag = (link) => {
    if (!link.url || link.active) return 'span'
    return Link
}

const label = (link) => {
    if (link.label === '&laquo; Previous') return '‹'
    if (link.label === 'Next &raquo;') return '›'
    return link.label
}

const buttonClass = (link) => {
    if (!link.url) return 'cursor-not-allowed text-gray-400 bg-base-200'
    if (link.active) return 'bg-primary text-primary-content'
    return 'bg-base-200 hover:bg-base-300 text-base-content'
}
</script>

