/*
 * Track IT — service worker
 * ------------------------------------------------------------------
 * Permite instalar la aplicación (PWA) y muestra una página sin
 * conexión cuando no hay red. No guarda en caché páginas con datos
 * (todo requiere sesión): solo la página sin conexión y sus íconos.
 *
 * Al cambiar este archivo, sube VERSION para renovar la caché.
 */
const VERSION = 'track-it-v1';
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
