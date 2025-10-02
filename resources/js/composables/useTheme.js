import { ref } from 'vue';

const theme = ref(localStorage.getItem('theme') || 'light');
const themes = ["light", "dark", "corporate", "cupcake"];

function setTheme(next) {
    theme.value = next;
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}

export function useTheme() {
    return { theme, themes, setTheme };
}
