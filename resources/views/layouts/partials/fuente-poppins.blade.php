{{-- ============================================================
    FUENTE POPPINS (local, sin Google Fonts)
    ------------------------------------------------------------
    Archivos en public/fonts/poppins (Fontsource 5.3.0, licencia
    OFL en OFL.txt). Solo los pesos que usa el sistema (400, 500,
    600 y 700) y solo el subconjunto latino: cubre el español
    (acentos, ñ, ¿ ¡, €) y pesa ~8 KB por archivo.

    400 y 500 se precargan: son el texto normal y la mayoría de
    etiquetas, lo primero que se pinta (LCP).
============================================================ --}}
<link rel="preload" href="{{ asset('fonts/poppins/poppins-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/poppins/poppins-latin-500-normal.woff2') }}" as="font" type="font/woff2" crossorigin>

<style>
    @foreach ([400, 500, 600, 700] as $pesoPoppins)
    @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: {{ $pesoPoppins }};
        font-display: swap;
        src: url('{{ asset('fonts/poppins/poppins-latin-' . $pesoPoppins . '-normal.woff2') }}') format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
    }
    @endforeach
</style>
