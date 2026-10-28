{{-- ============================================================
    ESTADO VACÍO / SIN RESULTADOS / ERROR
    ------------------------------------------------------------
    <x-ui.empty-state
        title="Todavía no hay equipos"
        description="Cuando registres un equipo aparecerá aquí."
    >
        <x-slot:actions>
            <a href="..." class="...">Añadir equipo</a>
        </x-slot:actions>
    </x-ui.empty-state>

    <x-ui.empty-state variant="search" title="Sin resultados"
        description="Prueba con otros filtros." />

    Error con reintento (llama a una acción de Livewire):
    <x-ui.empty-state variant="error" retry="refreshTable" />

    Props:
    - variant      empty | search | error
    - title        Título (cada variante tiene uno por defecto).
    - description  Texto de apoyo.
    - retry        Método de Livewire para el botón «Reintentar».
    - compact      Menos espacio vertical (dentro de tarjetas pequeñas).
    - Slot icon    Icono propio (svg de 20 px) en lugar del de la variante.
============================================================ --}}
@props([
    'variant' => 'empty',
    'title' => null,
    'description' => null,
    'retry' => null,
    'compact' => false,
])

@php
    $predeterminados = [
        'empty' => ['Sin registros', 'Todavía no hay información para mostrar.'],
        'search' => ['Sin resultados', 'No encontramos coincidencias. Prueba con otros filtros o términos.'],
        'error' => ['No se pudo cargar la información', 'Revisa tu conexión e inténtalo de nuevo.'],
    ];

    [$tituloBase, $descripcionBase] = $predeterminados[$variant] ?? $predeterminados['empty'];

    $esError = $variant === 'error';
@endphp

<div
    {{ $attributes->class([
        'flex flex-col items-center justify-center text-center',
        'px-6 py-12' => ! $compact,
        'px-4 py-6' => $compact,
    ]) }}
    @if ($esError) role="alert" @endif
>
    <span @class([
        'flex h-12 w-12 items-center justify-center rounded-full',
        'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]' => $esError,
        'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]' => ! $esError,
    ])>
        @isset($icon)
            {{ $icon }}
        @else
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            @if ($variant === 'search')
                <circle cx="11" cy="11" r="7"/>
                <path d="M20 20l-4-4"/>
            @elseif ($esError)
                <path d="M12 9v4M12 16.5v.5"/>
                <path d="M10.3 3.9L2.6 17.3A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.7L13.7 3.9a2 2 0 0 0-3.4 0z"/>
            @else
                <path d="M3 7l9-4 9 4-9 4-9-4z"/>
                <path d="M3 7v10l9 4 9-4V7"/>
                <path d="M12 11v10"/>
            @endif
        </svg>
        @endisset
    </span>

    <p class="mt-4 text-sm font-semibold text-[var(--theme-text-strong)]">
        {{ $title ?? $tituloBase }}
    </p>

    <p class="mt-1 max-w-sm text-xs leading-relaxed text-[var(--theme-text-muted)]">
        {{ $description ?? $descripcionBase }}
    </p>

    @if ($retry || isset($actions))
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            @if ($retry)
                <x-ui.button
                    variant="secondary"
                    size="sm"
                    wire:click="{{ $retry }}"
                    :target="$retry"
                    loading-text="Reintentando…"
                >
                    <x-slot:icon>
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-aa117c8') }}"></use></svg>
                    </x-slot:icon>

                    Reintentar
                </x-ui.button>
            @endif

            {{ $actions ?? '' }}
        </div>
    @endif
</div>
