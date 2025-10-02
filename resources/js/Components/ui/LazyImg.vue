<script setup>
import { ref } from 'vue';

const props = defineProps({
    src: { type: String, required: true },
    alt: { type: String, default: '' },
    // Контейнер-аспект: "video" (16/9), "banner" (21/9), "square" (1/1), "avatar" (1/1 круг)
    aspect: { type: String, default: 'video' },
    rounded: { type: String, default: 'rounded-xl' },
    // Доп. класс для <img>
    imgClass: { type: String, default: '' }
});

const loaded = ref(false);
const aspectClasses = {
    video: 'aspect-[16/9]',
    banner: 'aspect-[21/9]',
    square: 'aspect-square',
    avatar: 'aspect-square'
};
</script>

<template>
    <div class="relative overflow-hidden bg-base-200" :class="[aspectClasses[aspect], rounded]">
        <!-- skeleton -->
        <div v-show="!loaded" class="absolute inset-0 animate-pulse">
            <div class="w-full h-full bg-base-300" />
        </div>

        <!-- изображение -->
        <img
            :src="src"
            :alt="alt"
            :width="1600"
            :height="900"
            loading="lazy"
            decoding="async"
            @load="loaded = true"
            class="h-full w-full object-cover transition-opacity duration-300"
            :class="[loaded ? 'opacity-100' : 'opacity-0', imgClass]"
        />
    </div>
</template>
