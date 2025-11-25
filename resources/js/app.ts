import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Suppress Chrome extension runtime errors that don't affect the application
if (typeof window !== 'undefined') {
    // Suppress "Unchecked runtime.lastError" messages from browser extensions
    const originalError = console.error;
    console.error = (...args: any[]) => {
        const message = args[0]?.toString() || '';
        if (
            message.includes('runtime.lastError') ||
            message.includes('message port closed')
        ) {
            // Silently ignore extension-related errors
            return;
        }
        originalError.apply(console, args);
    };

    // Handle unhandled promise rejections from extensions
    window.addEventListener('unhandledrejection', (event) => {
        const message = event.reason?.message || event.reason?.toString() || '';
        if (
            message.includes('runtime.lastError') ||
            message.includes('message port closed')
        ) {
            event.preventDefault();
        }
    });
}
