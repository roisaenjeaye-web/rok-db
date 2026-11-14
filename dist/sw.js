/**
 * Service Worker for RoK Joki Admin PWA
 * Strategy: Network-First for HTML/APIs (always fresh data), Cache-First for static assets
 */

const CACHE_NAME = 'rok-joki-cache-v1';
const STATIC_ASSETS = [
    '/css/mobile.css',
    '/js/satset.js',
    '/icon-192.png',
    '/icon-512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://instant.page/5.2.0'
];

// Install: Cache critical static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[SW] Non-critical asset cache failed:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Network-First for dynamic content, Cache fallback
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Skip non-GET requests or browser-extension schemes
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    // Static assets: Stale-While-Revalidate
    const isStatic = STATIC_ASSETS.some(url => request.url.includes(url)) ||
                     request.url.match(/\.(png|jpg|jpeg|svg|css|js|woff2|woff|ttf)$/);

    if (isStatic) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Dynamic Pages & APIs: Network-First
    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // If offline, try to return cached page
                return caches.match(request);
            })
    );
});
