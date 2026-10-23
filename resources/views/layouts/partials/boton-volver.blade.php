{{-- ============================================================
    BOTÓN «VOLVER» (arriba a la izquierda, en todas las pantallas)
    ------------------------------------------------------------
    - $variante 'fila' (por omisión): botón «‹ Volver» en la fila
      superior del contenido; solo en escritorio (md+).
    - $variante 'movil': solo la flecha, a la izquierda del título
      (layouts/partials/encabezado, móvil).
    - Destino: la pantalla anterior dentro del sistema (si no fue un
      formulario); si no hay, el nivel anterior de la ruta de
      navegación (App\Support\Encabezado) o $destino.
      Lógica: window.trackitVolver (layouts/partials/volver-script).
    - El layout lo agrega solo; una pantalla que ya lo incluye en su
      propia fila de acciones evita el duplicado:
          @include('layouts.partials.boton-volver')
          @include('layouts.partials.boton-volver', ['destino' => route('...')])
============================================================ --}}
@php
    $esMovilVolver = ($variante ?? 'fila') === 'movil';

    // Avisa al layout que la pantalla ya tiene su «Volver» de escritorio.
    if (! $esMovilVolver) {
        app()->instance('trackit.volver.incluido', true);
    }

    if (! isset($destino)) {
        $destino = null;
        $encabezadoVolver = \App\Support\Encabezado::actual();
        $urlActualVolver = rtrim(url()->current(), '/');

        foreach (array_reverse($encabezadoVolver['migas'] ?? []) as $migaVolver) {
            $hrefVolver = $migaVolver['href'] ?? null;

            if ($hrefVolver && rtrim(strtok($hrefVolver, '?'), '/') !== $urlActualVolver) {
                $destino = $hrefVolver;
                break;
            }
        }
    }
@endphp

<a
    href="{{ $destino ?? url('/') }}"
    x-data="{ visible: {{ $destino ? 'true' : 'false' }} || Boolean(window.trackitPaginaPrevia?.()) }"
    x-show="visible"
    @unless ($destino) x-cloak @endunless
    x-on:click.prevent="window.trackitVolver ? window.trackitVolver(@js($destino)) : (window.location.href = $el.href)"
    @class([
        'shrink-0 items-center justify-center gap-2 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]',
        'flex h-9 w-9 md:hidden' => $esMovilVolver,
        'hidden h-10 px-4 md:inline-flex' => ! $esMovilVolver,
    ])
    aria-label="Volver"
    title="Volver"
>
    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M15 6l-6 6 6 6"/>
    </svg>

    @unless ($esMovilVolver)
        <span>Volver</span>
    @endunless
</a>
