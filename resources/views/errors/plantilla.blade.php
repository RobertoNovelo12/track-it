{{-- ============================================================
    PLANTILLA DE PÁGINAS DE ERROR (404, 403, 500)
    ------------------------------------------------------------
    Independiente del layout principal a propósito:
    - Estilos en línea (no depende de Vite).
    - Tema y logotipo se leen dentro de try/catch: si el error
      viene justo de la base de datos o de la sesión, la página
      se sigue mostrando con el tema del navegador y el logotipo
      predeterminado, en lugar de quedar en blanco.

    Variables: $codigo, $titulo, $mensaje, $tono (primary|warning|danger)
============================================================ --}}
@php
    $temaServidor = null;
    $logoUrl = asset(\App\Services\OrganizacionService::LOGO_PREDETERMINADO);
    $logoAlt = 'Grand Palladium';
    $logoModoOscuro = 'original';
    $logoAncho = 170;
    $inicioUrl = url('/');

    try {
        if (auth()->check()) {
            $temaServidor = app(\App\Services\PreferenciasUsuarioService::class)->obtener()['tema'] ?? null;
        }
    } catch (\Throwable $e) {
        $temaServidor = null;
    }

    try {
        $organizacion = app(\App\Services\OrganizacionService::class);
        $logoUrl = $organizacion->logoUrl();
        $logoAlt = $organizacion->nombre();
        $logoModoOscuro = $organizacion->logoModoOscuro();
        $logoAncho = $organizacion->logoAncho(170);
    } catch (\Throwable $e) {
        // Se conserva el logotipo predeterminado.
    }

    try {
        $inicioUrl = route('dashboard');
    } catch (\Throwable $e) {
        $inicioUrl = url('/');
    }

    $tono = $tono ?? 'primary';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">

    <title>{{ $codigo }} · {{ $titulo }} | Track-IT</title>

    {{-- Tema antes de pintar (mismo criterio que el layout principal) --}}
    <script>
        (() => {
            const servidor = @json($temaServidor);
            let preferencia = ['light', 'dark', 'system'].includes(servidor) ? servidor : null;

            if (!preferencia) {
                try {
                    const guardado = localStorage.getItem('trackit_theme');
                    preferencia = ['light', 'dark', 'system'].includes(guardado) ? guardado : 'system';
                } catch (error) {
                    preferencia = 'system';
                }
            }

            const resuelto = preferencia === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : preferencia;

            document.documentElement.classList.toggle('dark', resuelto === 'dark');
            document.documentElement.style.colorScheme = resuelto;
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Logotipo en modo oscuro (original / invertir / blanco) --}}
    @include('layouts.partials.organizacion-logo-styles')

    <style>
        :root {
            --theme-bg: #FFFCFF;
            --theme-surface: #FFFFFF;
            --theme-surface-soft: #F6F6F6;
            --theme-text: #50514F;
            --theme-text-strong: #25344A;
            --theme-text-muted: #7A7B79;
            --theme-border: rgba(80, 81, 79, 0.10);
            --theme-border-strong: rgba(80, 81, 79, 0.20);
            --theme-primary: #247BA0;
            --theme-primary-hover: #1D6688;
            --theme-primary-soft: rgba(36, 123, 160, 0.10);
            --theme-danger: #EF4444;
            --theme-danger-soft: rgba(239, 68, 68, 0.08);
            --theme-warning: #CB8B2A;
            --theme-warning-soft: rgba(203, 139, 42, 0.15);
        }

        html.dark {
            --theme-bg: #111416;
            --theme-surface: #181C1F;
            --theme-surface-soft: #202529;
            --theme-text: #D5D8D9;
            --theme-text-strong: #F3F5F6;
            --theme-text-muted: #969DA1;
            --theme-border: rgba(255, 255, 255, 0.08);
            --theme-border-strong: rgba(255, 255, 255, 0.15);
            --theme-primary: #3694BC;
            --theme-primary-hover: #46A4CC;
            --theme-primary-soft: rgba(54, 148, 188, 0.12);
            --theme-danger: #F87171;
            --theme-danger-soft: rgba(248, 113, 113, 0.12);
            --theme-warning: #E0A84A;
            --theme-warning-soft: rgba(224, 168, 74, 0.14);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { margin: 0; min-height: 100%; }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: var(--theme-bg);
            color: var(--theme-text);
            font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .error-logo {
            display: block;
            width: var(--org-logo-w, 170px);
            max-width: 70vw;
            max-height: 6rem;
            height: auto;
            object-fit: contain;
            margin: 0 auto 28px;
        }

        .error-card {
            width: 100%;
            max-width: 460px;
            padding: 36px 28px 28px;
            border: 1px solid var(--theme-border);
            border-radius: 16px;
            background: var(--theme-surface);
            text-align: center;
            animation: entrada 220ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .error-icono {
            width: 56px;
            height: 56px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 9999px;
        }

        .tono-primary .error-icono { background: var(--theme-primary-soft); color: var(--theme-primary); }
        .tono-warning .error-icono { background: var(--theme-warning-soft); color: var(--theme-warning); }
        .tono-danger  .error-icono { background: var(--theme-danger-soft);  color: var(--theme-danger); }

        .error-codigo {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--theme-text-muted);
        }

        .error-titulo {
            margin: 6px 0 0;
            font-size: 22px;
            line-height: 1.25;
            font-weight: 600;
            color: var(--theme-text-strong);
        }

        .error-mensaje {
            margin: 10px auto 0;
            max-width: 360px;
            font-size: 14px;
            line-height: 1.6;
            color: var(--theme-text-muted);
        }

        .error-acciones {
            margin-top: 26px;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
        }

        @media (min-width: 480px) {
            .error-acciones { flex-direction: row; justify-content: center; }
        }

        .boton {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 42px;
            padding: 0 18px;
            border-radius: 10px;
            border: 1px solid transparent;
            font: inherit;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease;
        }

        .boton:focus-visible {
            outline: 2px solid var(--theme-primary);
            outline-offset: 2px;
        }

        .boton-primario { background: var(--theme-primary); color: #fff; }
        .boton-primario:hover { background: var(--theme-primary-hover); }

        .boton-secundario {
            background: var(--theme-surface);
            border-color: var(--theme-border-strong);
            color: var(--theme-text);
        }
        .boton-secundario:hover { background: var(--theme-surface-soft); }

        .error-pie {
            margin-top: 22px;
            font-size: 12px;
            color: var(--theme-text-muted);
        }

        @keyframes entrada {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-card { animation: none; }
        }
    </style>
</head>

<body>
    <main class="error-card tono-{{ $tono }}">
        <img
            src="{{ $logoUrl }}"
            alt="{{ $logoAlt }}"
            data-dark-mode="{{ $logoModoOscuro }}"
            style="--org-logo-w: {{ $logoAncho }}px;"
            class="org-logo error-logo"
        >

        <div class="error-icono" aria-hidden="true">
            {{ $icono }}
        </div>

        <p class="error-codigo">Error {{ $codigo }}</p>

        <h1 class="error-titulo">{{ $titulo }}</h1>

        <p class="error-mensaje">{{ $mensaje }}</p>

        <div class="error-acciones">
            <button type="button" class="boton boton-secundario" onclick="history.length > 1 ? history.back() : (window.location.href = @js($inicioUrl))">
                Volver atrás
            </button>

            <a href="{{ $inicioUrl }}" class="boton boton-primario">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10.5L12 3l9 7.5"/>
                    <path d="M5 9.5V21h14V9.5"/>
                </svg>
                Regresar al inicio
            </a>
        </div>
    </main>

    <p class="error-pie">Track-IT · Sistema de Control de Activos TI</p>
</body>
</html>
