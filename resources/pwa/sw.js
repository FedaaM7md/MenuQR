/*
 * MenuQR service worker.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 *
 * It caches only the public menu shell and static assets. Staff/admin pages,
 * order submissions and any non-GET request always go to the network.
 */
const VERSION = 'menuqr-v2';
const SHELL_CACHE = `${VERSION}-shell`;
const ASSET_CACHE = `${VERSION}-assets`;
const IMAGE_CACHE = `${VERSION}-images`;

const PRECACHE = [
    '/offline',
    '/manifest.webmanifest',
    '/favicon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('menuqr-') && ![SHELL_CACHE, ASSET_CACHE, IMAGE_CACHE].includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

function isAsset(url) {
    return url.pathname.startsWith('/build/') || url.pathname.startsWith('/fonts/') || url.pathname === '/favicon.svg';
}

function isImage(url) {
    return url.pathname.startsWith('/icons/') || url.pathname.startsWith('/storage/');
}

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (isAsset(url)) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                const copy = response.clone();
                caches.open(ASSET_CACHE).then((cache) => cache.put(request, copy));
                return response;
            }))
        );
        return;
    }

    if (isImage(url)) {
        event.respondWith(
            caches.open(IMAGE_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const network = fetch(request).then((response) => {
                    if (response.ok) {
                        cache.put(request, response.clone());
                    }
                    return response;
                }).catch(() => cached);

                return cached || network;
            })
        );
        return;
    }

    if (request.mode === 'navigate' && (url.pathname.startsWith('/m/') || url.pathname === '/offline')) {
        // Never cache order confirmation pages; they can contain personal details.
        if (url.pathname.includes('/orders/')) {
            return;
        }

        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const copy = response.clone();
                        caches.open(SHELL_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    const cached = await caches.match(request);
                    return cached || caches.match('/offline');
                })
        );
    }
});
