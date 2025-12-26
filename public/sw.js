/**
 * Service Worker for caching API responses and static assets
 * This enables offline functionality and faster subsequent loads
 */

const CACHE_VERSION = 'v1.0.4';
const CACHE_NAME = `app-plotter-${CACHE_VERSION}`;

// API endpoints to cache
const API_CACHE_PATTERNS = [
    /^\/api\/site-settings$/,
    /^\/api\/navigations/,
    /^\/api\/navigations\/categories/,
];

// Static assets to cache
const STATIC_ASSETS = [
    '/',
    '/favicon.ico',
    '/favicon.svg',
    '/logo.jpg',
];

// Paths to exclude from service worker caching (let browser handle these)
const EXCLUDE_PATTERNS = [
    /^\/build\/assets\//,  // Vite build assets (already cached by browser)
    /^\/hot$/,             // Vite HMR endpoint
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[Service Worker] Caching static assets');
            return cache.addAll(STATIC_ASSETS).catch((err) => {
                console.warn('[Service Worker] Failed to cache some assets:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip excluded paths (let browser handle Vite assets)
    const isExcluded = EXCLUDE_PATTERNS.some((pattern) => pattern.test(url.pathname));
    if (isExcluded) {
        return; // Let the browser handle this request normally
    }

    // Check if it's an API request we want to cache
    const isApiRequest = API_CACHE_PATTERNS.some((pattern) => pattern.test(url.pathname));

    // Check if it's a static asset
    const isStaticAsset = STATIC_ASSETS.some((asset) => url.pathname === asset);

    if (isApiRequest || isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                // Return cached version if available
                if (cachedResponse) {
                    console.log('[Service Worker] Serving from cache:', url.pathname);
                    
                    // For API requests, also fetch in background to update cache
                    if (isApiRequest) {
                        fetch(request)
                            .then((response) => {
                                if (response.ok) {
                                    const responseClone = response.clone();
                                    caches.open(CACHE_NAME).then((cache) => {
                                        cache.put(request, responseClone);
                                    });
                                }
                            })
                            .catch(() => {
                                // Silently fail background update
                            });
                    }
                    
                    return cachedResponse;
                }

                // Fetch from network
                console.log('[Service Worker] Fetching from network:', url.pathname);
                return fetch(request)
                    .then((response) => {
                        // Don't cache if not ok
                        if (!response.ok) {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        // Cache the response
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });

                        return response;
                    })
                    .catch((error) => {
                        console.error('[Service Worker] Fetch failed:', error);
                        throw error;
                    });
            })
        );
    }
});

