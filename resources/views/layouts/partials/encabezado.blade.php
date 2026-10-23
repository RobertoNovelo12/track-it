{{-- ============================================================
    ENCABEZADO DE PÁGINA: título del módulo y ruta de navegación
    ------------------------------------------------------------
    Datos: App\Support\Encabezado (según la ruta actual).
    $variante: 'barra' (barra superior, escritorio) o 'movil'
    (arriba del contenido).

    Una pantalla puede agregar su sección activa al final de la
    ruta sin recargar (p. ej. Administración):
        $dispatch('encabezado-seccion', { miga: 'Hoteles' })
============================================================ --}}
@php
    $encabezado ??= \App\Support\Encabezado::actual();
    $esBarra = ($variante ?? 'barra') === 'barra';
@endphp

@if ($encabezado)
    @unless ($esBarra)
        {{-- Móvil: «Volver» (solo la flecha) a la izquierda del título --}}
        <div class="mb-5 flex items-center gap-3 md:hidden">
            @include('layouts.partials.boton-volver', ['variante' => 'movil'])
    @endunless

    <div
        x-data="{ extra: '' }"
        x-on:encabezado-seccion.window="extra = String($event.detail?.miga ?? '')"
        @class([
            'min-w-0',
            'hidden md:block' => $esBarra,
            'min-w-0 flex-1' => ! $esBarra,
        ])
    >
        <h1
            @class([
                'truncate font-semibold leading-tight text-[var(--theme-text-strong)]',
                'text-base' => $esBarra,
                'text-xl' => ! $esBarra,
            ])
            title="{{ $encabezado['titulo'] }}"
        >
            {{ $encabezado['titulo'] }}
        </h1>

        <nav
            aria-label="Ruta de navegación"
            @class([
                'flex min-w-0 items-center text-xs text-[var(--theme-text-muted)]',
                'mt-0.5 overflow-hidden whitespace-nowrap' => $esBarra,
                'mt-1 flex-wrap gap-y-0.5' => ! $esBarra,
            ])
        >
            @foreach ($encabezado['migas'] as $miga)
                @if (! $loop->first)
                    <span class="mx-1.5 shrink-0" aria-hidden="true">›</span>
                @endif

                @if (! empty($miga['href']))
                    <a
                        href="{{ $miga['href'] }}"
                        wire:navigate.hover
                        class="truncate transition-colors hover:text-[var(--theme-primary)]"
                    >{{ $miga['label'] }}</a>
                @else
                    <span
                        class="truncate text-[var(--theme-text)]"
                        @if ($loop->last) aria-current="page" @endif
                    >{{ $miga['label'] }}</span>
                @endif
            @endforeach

            {{-- Sección activa enviada por la pantalla --}}
            <template x-if="extra !== ''">
                <span class="flex min-w-0 items-center">
                    <span class="mx-1.5 shrink-0" aria-hidden="true">›</span>
                    <span class="truncate text-[var(--theme-text)]" aria-current="page" x-text="extra"></span>
                </span>
            </template>
        </nav>
    </div>

    @unless ($esBarra)
        </div>
    @endunless
@endif
