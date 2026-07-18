/**
 * IBIG FactPro — Service Worker avancé (Phase 12 PWA hors-ligne).
 * Stratégies :
 *   - Cache-First      : assets statiques (build/, images, fonts)
 *   - Network-First    : pages Inertia (navigate)
 *   - StaleWhileReval  : données API GET (clients, produits)
 *   - Background Sync  : documents POST en file d'attente
 */

const CACHE_NAME = 'factpro-v2';

const PRECACHE = [
    '/offline.html',
    '/manifest.webmanifest',
    '/logo.svg',
    '/logo_icon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-512-maskable.png',
    '/icons/apple-touch-icon.png',
];

const STATIC_PATTERNS = [
    /^\/build\//,
    /^\/icons\//,
    /\.svg$/,
    /\.png$/,
    /\.css$/,
    /\.js$/,
    /\.webmanifest$/,
    /\.woff2?$/,
    /\.ico$/,
];

const API_CACHE_ROUTES = [
    '/api/v1/customers',
    '/api/v1/products',
    '/offline-sync/cache-data',
];

/* ── Install : pré-cache des assets essentiels ── */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

/* ── Activate : purge des anciens caches ── */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

/* ── Fetch : routeur par type de ressource ── */
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // POST/PUT/DELETE → réseau direct (jamais intercepté)
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Cross-origin → réseau direct
    if (url.origin !== self.location.origin) {
        return;
    }

    // Requêtes Inertia (XHR JSON) → réseau direct
    if (request.headers.get('X-Inertia')) {
        return;
    }

    // Vérification QR → réseau direct
    if (url.pathname.startsWith('/verify')) {
        return;
    }

    // (a) Navigation → Network-First, fallback /offline.html
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // (b) Routes API données → StaleWhileRevalidate
    const isApiCache = API_CACHE_ROUTES.some((route) => url.pathname.startsWith(route));
    if (isApiCache) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                cache.match(request).then((cached) => {
                    const network = fetch(request)
                        .then((response) => {
                            if (response && response.status === 200 && response.type === 'basic') {
                                cache.put(request, response.clone());
                            }
                            return response;
                        })
                        .catch(() => cached);
                    return cached || network;
                })
            )
        );
        return;
    }

    // (c) Assets statiques → Cache-First
    const isStatic = STATIC_PATTERNS.some((re) => re.test(url.pathname));
    if (isStatic) {
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) =>
                cache.match(request).then((cached) => {
                    if (cached) return cached;
                    return fetch(request).then((response) => {
                        if (response && response.status === 200 && response.type === 'basic') {
                            cache.put(request, response.clone());
                        }
                        return response;
                    });
                })
            )
        );
        return;
    }

    // (d) Tout le reste → réseau direct, sans interception
});

/* ── Background Sync : vider la file de documents ── */
self.addEventListener('sync', (event) => {
    if (event.tag === 'factpro-sync') {
        event.waitUntil(syncPendingDocuments());
    }
});

async function syncPendingDocuments() {
    // Envoyer un message à tous les clients pour déclencher la synchronisation
    const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: false });
    for (const client of clients) {
        client.postMessage({ type: 'BACKGROUND_SYNC', tag: 'factpro-sync' });
    }
}

/* ── Push notifications (Phase 16) ── */
self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? {};
    const title = data.title ?? 'FactPro';
    const options = {
        body:      data.body  ?? '',
        icon:      data.icon  ?? '/icons/icon-192.png',
        badge:     data.badge ?? '/icons/badge-72x72.png',
        data:      data.data  ?? {},
        tag:       data.tag   ?? 'factpro',
        renotify:  true,
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url ?? '/';
    event.waitUntil(clients.openWindow(url));
});
