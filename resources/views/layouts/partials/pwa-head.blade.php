{{-- ============================================================
    APLICACIÓN INSTALABLE (PWA)
    ------------------------------------------------------------
    - public/manifest.json: nombre, íconos y modo «standalone».
    - public/sw.js: service worker (instalación y página sin
      conexión, public/offline.html).
    - Íconos en public/images/track-it/.

    Con esto Chrome y Edge (PC y Android) ofrecen «Instalar».
    En iPhone se instala desde Safari: Compartir › Agregar a
    pantalla de inicio (la hoja «Más» lo explica).
============================================================ --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- Llave pública para las notificaciones push (config/webpush.php) --}}
@if (filled(config('webpush.public_key')))
    <meta name="vapid-public-key" content="{{ config('webpush.public_key') }}">
@endif

<meta name="theme-color" content="#FFFCFF" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#111416" media="(prefers-color-scheme: dark)">

<meta name="application-name" content="Track IT">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Track IT">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<link rel="icon" type="image/svg+xml" href="{{ asset('images/track-it/track-it-icono.svg') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/track-it/favicon-32.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/track-it/apple-touch-icon.png') }}">

<script>
    (() => {
        /*
         * Instalación: se guarda el aviso del navegador para el botón
         * «Instalar aplicación» (hoja «Más» y menú lateral). No se
         * cancela, así el navegador también puede recomendarla.
         */
        window.trackitInstalacion = window.trackitInstalacion ?? null;

        window.addEventListener('beforeinstallprompt', (evento) => {
            window.trackitInstalacion = evento;
            window.dispatchEvent(new CustomEvent('trackit-instalable'));
        });

        window.addEventListener('appinstalled', () => {
            window.trackitInstalacion = null;
            window.dispatchEvent(new CustomEvent('trackit-instalada'));
        });

        window.trackitAppInstalada = () =>
            window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;

        window.trackitEsIos = () =>
            /iphone|ipad|ipod/i.test(window.navigator.userAgent)
            || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

        window.trackitInstalarApp = async () => {
            const aviso = window.trackitInstalacion;

            if (! aviso) {
                return false;
            }

            aviso.prompt();

            const { outcome } = await aviso.userChoice;

            window.trackitInstalacion = null;
            window.dispatchEvent(new CustomEvent('trackit-instalable'));

            return outcome === 'accepted';
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' })
                    .catch(() => { /* Sin service worker la app sigue funcionando. */ });
            });
        }
    })();
</script>
