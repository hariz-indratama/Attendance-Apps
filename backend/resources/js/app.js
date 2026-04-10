import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';

// Setup global session interceptor for 401 responses
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    const response = await originalFetch(...args);

    if (response.status === 401) {
        const Toast = window.Toast || null;
        if (Toast) {
            Toast.error('Session expired. Please login again.');
        }

        setTimeout(() => {
            window.location.href = '/login?reason=session_expired';
        }, 500);
    }

    return response;
};

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
});
