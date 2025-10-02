import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function getInitialTheme() {
    const saved = localStorage.getItem('theme');
    if (saved) return saved;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light';
}
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}
applyTheme(getInitialTheme());

window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener?.('change', e => {
    const saved = localStorage.getItem('theme');
    if (!saved) applyTheme(e.matches ? 'dark' : 'light');
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

document.addEventListener('change', (e) => {
    const el = e.target;
    if (el?.classList?.contains('theme-controller')) {
        const next = el.type === 'checkbox'
            ? (el.checked ? 'dark' : 'light')
            : el.value;
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    }
});

