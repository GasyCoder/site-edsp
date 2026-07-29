const VERSION = 'edsp-pwa-v1';
const STATIC_CACHE = `${VERSION}-static`;
const RUNTIME_CACHE = `${VERSION}-runtime`;
const OFFLINE_URL = '/offline.html';
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/images/pwa/icon-192.png',
    '/images/pwa/icon-512.png',
    '/images/pwa/maskable-512.png',
    '/images/pwa/apple-touch-icon.png',
];
const BLOCKED_PREFIXES = [
    '/admin',
    '/administration',
    '/edition',
    '/login',
    '/logout',
    '/newsletter',
];
const MAX_RUNTIME_ENTRIES = 80;
const MAX_RESPONSE_BYTES = 5 * 1024 * 1024;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('edsp-pwa-') && ![STATIC_CACHE, RUNTIME_CACHE].includes(key))
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

async function trimRuntimeCache(cache) {
    const keys = await cache.keys();

    if (keys.length <= MAX_RUNTIME_ENTRIES) {
        return;
    }

    await Promise.all(keys.slice(0, keys.length - MAX_RUNTIME_ENTRIES).map((key) => cache.delete(key)));
}

function canStore(response) {
    if (!response || !response.ok || response.type !== 'basic') {
        return false;
    }

    if ((response.headers.get('cache-control') || '').includes('no-store')) {
        return false;
    }

    const contentLength = Number(response.headers.get('content-length') || 0);

    return contentLength === 0 || contentLength <= MAX_RESPONSE_BYTES;
}

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (canStore(response)) {
        const cache = await caches.open(RUNTIME_CACHE);
        await cache.put(request, response.clone());
        await trimRuntimeCache(cache);
    }

    return response;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin || BLOCKED_PREFIXES.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    if (['font', 'image', 'script', 'style'].includes(request.destination)) {
        event.respondWith(cacheFirst(request));
    }
});
