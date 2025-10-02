<script setup>
import { ref, onMounted } from 'vue';

const themes = ["light","dark","retro","cyberpunk","valentine","aqua"];
const theme = ref(localStorage.getItem('theme') || 'light');

function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('theme', t);
    theme.value = t;
}

onMounted(() => {
    const current = document.documentElement.getAttribute('data-theme') || theme.value;
    applyTheme(current);
});
</script>

<template>
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle" title="Theme">
            <!-- простая иконка -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21.64 13A9 9 0 1111 2.36 7 7 0 0021.64 13z"/>
            </svg>
        </div>

        <ul tabindex="0" class="dropdown-content bg-base-300 rounded-box z-[1] w-52 p-2 shadow-2xl">
            <li v-for="t in themes" :key="t">
                <label class="w-full">
                    <input
                        type="radio"
                        name="theme-dropdown"
                        class="theme-controller w-full btn btn-sm btn-block btn-ghost justify-start capitalize"
                        :aria-label="t"
                        :value="t"
                        :checked="theme===t"
                        @change="applyTheme(t)"
                    />
                </label>
            </li>
        </ul>
    </div>
</template>
