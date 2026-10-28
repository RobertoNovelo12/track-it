/*
 * Track IT — service worker
 * ------------------------------------------------------------------
 * Permite instalar la aplicación (PWA) y muestra una página sin
 * conexión cuando no hay red. No guarda en caché páginas con datos
 * (todo requiere sesión): solo la página sin conexión y sus íconos.
 *
 * Al cambiar este archivo, sube VERSION para renovar la caché.
 */
const VERSION = 'track-it-v2';
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
                claves.filter((clave) => clave !== VERSION).map((clave) => caches.delete(clave))
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

self.addEventListener('fetch', (evento) => {
    const peticion = evento.request;

    // Solo navegaciones (páginas): si no hay red, la página sin conexión.
    if (peticion.mode !== 'navigate' || peticion.method !== 'GET') {
        return;
    }

    evento.respondWith(
        fetch(peticion).catch(() => caches.match(SIN_CONEXION))
    );
});
