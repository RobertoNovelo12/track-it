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

    /*
     * Nombre del destino («← Inventario» en vez de «Volver»): el nivel
     * anterior de la ruta de navegación. Si se regresa a la pantalla
     * anterior, el navegador pone su nombre (trackitTituloPrevio).
     */
    $etiquetaVolver = null;
    $encabezadoVolver = \App\Support\Encabezado::actual();
    $urlActualVolver = rtrim(url()->current(), '/');

    foreach (array_reverse($encabezadoVolver['migas'] ?? []) as $migaVolver) {
        $hrefVolver = $migaVolver['href'] ?? null;

        if (! $hrefVolver || rtrim(strtok($hrefVolver, '?'), '/') === $urlActualVolver) {
            continue;
        }

        if (! isset($destino)) {
            $destino = $hrefVolver;
        }

        if (rtrim(strtok($hrefVolver, '?'), '/') === rtrim(strtok((string) $destino, '?'), '/')) {
            $etiquetaVolver = $migaVolver['label'] ?? null;
            break;
        }
    }

    $destino ??= null;
@endphp

<a
    href="{{ $destino ?? url('/') }}"
    x-data="{
        visible: false,
        etiqueta: @js($etiquetaVolver ?? 'Volver'),

        /*
         * Si la pantalla anterior es la misma sección de la ruta de navegación
         * (p. ej. Administración › Tipos de equipo), se usa ese nombre, más
         * específico; si no, el nombre de la pantalla anterior.
         */
        mismaSeccion(a, b) {
            try {
                const x = new URL(a, location.origin);
                const y = new URL(b, location.origin);

                return x.pathname.replace(/\/$/, '') === y.pathname.replace(/\/$/, '')
                    && (x.searchParams.get('section') ?? '') === (y.searchParams.get('section') ?? '');
            } catch (e) {
                return false;
            }
        },

        actualizar() {
            const destino = @js($destino);
            const previa = window.trackitPaginaPrevia?.();

            this.visible = Boolean(destino) || Boolean(previa);
            this.etiqueta = previa && ! (destino && this.mismaSeccion(previa, destino))
                ? (window.trackitTituloPrevio?.() || @js($etiquetaVolver ?? 'Volver'))
                : @js($etiquetaVolver ?? 'Volver');
        },
    }"
    x-init="actualizar()"
    x-on:trackit-volver-registrado.window="actualizar()"
    x-show="visible"
    @unless ($destino) x-cloak @endunless
    x-on:click.prevent="window.trackitVolver ? window.trackitVolver(@js($destino)) : (window.location.href = $el.href)"
    @class([
        'shrink-0 items-center justify-center gap-2 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-sm font-medium text-[var(--theme-text-muted)] transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]',
        'flex h-9 w-9 md:hidden' => $esMovilVolver,
        'hidden h-10 px-4 md:inline-flex' => ! $esMovilVolver,
    ])
    x-bind:aria-label="etiqueta === 'Volver' ? 'Volver' : 'Volver a ' + etiqueta"
    x-bind:title="etiqueta === 'Volver' ? 'Volver' : 'Volver a ' + etiqueta"
    aria-label="Volver"
>
    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-d889af1') }}"></use></svg>

    @unless ($esMovilVolver)
        <span class="max-w-[14rem] truncate" x-text="etiqueta">{{ $etiquetaVolver ?? 'Volver' }}</span>
    @endunless
</a>
