const CACHE_NAME = 'grosirindo-v1';

const urlsToCache = [
    '/',
];

self.addEventListener('install', event => {
    console.log('Service Worker Installed');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('activate', event => {
    console.log('Service Worker Activated');
});

self.addEventListener('fetch', event => {

    event.respondWith(

        caches.match(event.request)
            .then(response => {

                if (response) {
                    return response;
                }

                return fetch(event.request);

            })

    );

});