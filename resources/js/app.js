import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// ── Service Worker (PWA) ─────────────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((reg) => console.info('[FactPro SW] scope:', reg.scope))
            .catch((err) => console.warn('[FactPro SW] failed:', err));
    });
}

// ── Diagnostic: log Inertia navigation errors with full stack ────────────────
window.addEventListener('unhandledrejection', (event) => {
    console.error('[FactPro] Unhandled rejection:', event.reason?.message ?? event.reason);
    console.error('[FactPro] Stack:', event.reason?.stack ?? '(no stack)');
});

// ── Track current navigation target for fallback ─────────────────────────────
let _pendingHref = null;
let _pendingMethod = 'get';
router.on('start', (event) => {
    _pendingHref   = event.detail?.visit?.url?.href ?? null;
    _pendingMethod = event.detail?.visit?.method   ?? 'get';
});
router.on('finish', () => {
    _pendingHref   = null;
    _pendingMethod = 'get';
});

// ── Catch Inertia navigation exceptions → fall back to full-page reload ──────
// Only redirect on GET navigations — POST/PUT/PATCH/DELETE must NOT be replayed
// as a GET (would cause 405 Method Not Allowed).
router.on('exception', (event) => {
    const err = event.detail?.exception;
    console.error('[FactPro] Inertia exception during navigation:', err);
    event.preventDefault(); // prevent unhandled-rejection
    if (_pendingHref && _pendingMethod === 'get') {
        console.warn('[FactPro] Falling back to full-page load:', _pendingHref);
        window.location.href = _pendingHref;
    }
});

// ── App ───────────────────────────────────────────────────────────────────────
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        vueApp.config.errorHandler = (err, _instance, info) => {
            console.error('[FactPro Vue]', info, err);
        };

        return vueApp.mount(el);
    },
    progress: { color: '#4B5563' },
});
