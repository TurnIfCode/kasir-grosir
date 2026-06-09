const CACHE_NAME = 'grosirindo-v1';

self.addEventListener('install', event => {

    console.log('Service Worker Installed');

    self.skipWaiting();

});

self.addEventListener('activate', event => {

    console.log('Service Worker Activated');

    event.waitUntil(

        caches.keys().then(cacheNames => {

            return Promise.all(

                cacheNames.map(cache => {

                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }

                })

            );

        })

    );

    self.clients.claim();

});

self.addEventListener('fetch', event => {

    // hanya GET request
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(

        fetch(event.request)

            .then(response => {

                // simpan ke cache
                const responseClone = response.clone();

                caches.open(CACHE_NAME)
                    .then(cache => {
                        cache.put(event.request, responseClone);
                    });

                return response;

            })

            .catch(() => {

                return caches.match(event.request)

                    .then(cachedResponse => {

                        if (cachedResponse) {
                            return cachedResponse;
                        }

                        // fallback halaman offline
                        if (
                            event.request.destination === 'document'
                        ) {

                            return new Response(
                                `
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>GrosirIndo Offline</title>
                                    <meta charset="utf-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1">
                                </head>
                                <body style="
                                    font-family: Arial, sans-serif;
                                    text-align:center;
                                    padding:50px;
                                ">
                                    <h2>GrosirIndo Offline</h2>
                                    <p>Tidak ada koneksi internet.</p>
                                    <p>Silakan hubungkan internet kembali.</p>
                                </body>
                                </html>
                                `,
                                {
                                    headers: {
                                        'Content-Type': 'text/html'
                                    }
                                }
                            );

                        }

                        return new Response('', {
                            status: 404,
                            statusText: 'Not Found'
                        });

                    });

            })

    );

});