/*
 * Track IT — service worker
 * ------------------------------------------------------------------
 * Permite instalar la aplicación (PWA) y muestra una página sin
 * conexión cuando no hay red. No guarda en caché páginas con datos
 * (todo requiere sesión): solo la página sin conexión y sus íconos.
 *
 * Diseño en el teléfono: CSS, JS, fuentes, logos y el script de
 * Livewire se guardan la primera vez que se descargan; después se
 * leen del teléfono y solo se espera a los datos.
 *
 * Al cambiar este archivo, sube VERSION para renovar la caché.
 */
const VERSION = 'track-it-v3';
const ESTATICOS = 'track-it-estaticos-v1';
const MAX_ESTATICOS = 120;
const SIN_CONEXION = '/offline.html';
const PRECARGA = [
    SIN_CONEXION,
    '/images/track-it/icon-192.png',
    '/images/track-it/track-it-icono.svg',
];

self.addEventListener('install', (evento) => {
    evento.waitUntil(
        caches.open(VERSION)
            .then((cache) => cache.addAll(PRECARGA))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (evento) => {
    evento.waitUntil(
        caches.keys()
            .then((claves) => Promise.all(
                claves.filter((clave) => clave !== VERSION && clave !== ESTATICOS).map((clave) => caches.delete(clave))
            ))
            .then(() => self.clients.claim())
    );
});

/*
 * Notificaciones push (App\Services\PushService): se muestran aunque
 * Track IT esté cerrado. Al tocarlas se abre la pantalla relacionada.
 */
self.addEventListener('push', (evento) => {
    let datos = {};

    try {
        datos = evento.data ? evento.data.json() : {};
    } catch (error) {
        datos = { body: evento.data ? evento.data.text() : '' };
    }

    const titulo = datos.title || 'Track IT';

    evento.waitUntil(
        self.registration.showNotification(titulo, {
            body: datos.body || '',
            icon: '/images/track-it/icon-192.png',
            badge: '/images/track-it/icon-maskable-192.png',
            tag: datos.tag || undefined,
            renotify: Boolean(datos.tag),
            data: { url: datos.url || '/dashboard' },
            lang: 'es',
        })
    );
});

self.addEventListener('notificationclick', (evento) => {
    evento.notification.close();

    const destino = new URL(evento.notification.data?.url || '/dashboard', self.location.origin).href;

    evento.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((ventanas) => {
            // Si Track IT ya está abierto, se usa esa ventana.
            for (const ventana of ventanas) {
                if (new URL(ventana.url).origin === self.location.origin && 'focus' in ventana) {
                    ventana.navigate?.(destino);

                    return ventana.focus();
                }
            }

            return self.clients.openWindow(destino);
        })
    );
});

/*
 * Archivos fijos del diseño.
 * - Con nombre versionado (Vite /build/assets, fuentes, Livewire
 *   con ?id=): se leen del teléfono y no se vuelven a pedir.
 * - Logos e íconos (/images): se muestran del teléfono al instante
 *   y se actualizan en segundo plano.
 */
const esVersionado = (url) =>
    url.pathname.startsWith('/build/assets/')
    || url.pathname.startsWith('/fonts/')
    || (url.pathname.includes('livewire') && url.pathname.endsWith('.js') && url.searchParams.has('id'));

const esImagen = (url) => url.pathname.startsWith('/images/');

const recortar = async (cache) => {
    const claves = await cache.keys();

    for (let i = 0; i < claves.length - MAX_ESTATICOS; i++) {
        await cache.delete(claves[i]);
    }
};

const guardar = async (peticion, respuesta) => {
    if (! respuesta || ! respuesta.ok || respuesta.type !== 'basic') {
        return;
    }

    const cache = await caches.open(ESTATICOS);

    await cache.put(peticion, respuesta);
    await recortar(cache);
};

const primeroCache = async (evento) => {
    const guardada = await caches.match(evento.request, { cacheName: ESTATICOS });

    if (guardada) {
        return guardada;
    }

    const respuesta = await fetch(evento.request);

    evento.waitUntil(guardar(evento.request, respuesta.clone()));

    return respuesta;
};

const cacheYActualizar = async (evento) => {
    const guardada = await caches.match(evento.request, { cacheName: ESTATICOS });

    const red = fetch(evento.request).then((respuesta) => {
        evento.waitUntil(guardar(evento.request, respuesta.clone()));

        return respuesta;
    });

    if (guardada) {
        evento.waitUntil(red.catch(() => null));

        return guardada;
    }

    return red;
};

self.addEventListener('fetch', (evento) => {
    const peticion = evento.request;

    if (peticion.method !== 'GET') {
        return;
    }

    // Páginas: siempre de la red (tienen datos y sesión). Sin red, la página sin conexión.
    if (peticion.mode === 'navigate') {
        evento.respondWith(
            fetch(peticion).catch(() => caches.match(SIN_CONEXION))
        );

        return;
    }

    const url = new URL(peticion.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (esVersionado(url)) {
        evento.respondWith(primeroCache(evento));

        return;
    }

    if (esImagen(url)) {
        evento.respondWith(cacheYActualizar(evento));
    }
});
