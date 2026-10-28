{{-- ============================================================
    SPINNER ÚNICO
    ------------------------------------------------------------
    <x-ui.spinner />                    mediano (16 px)
    <x-ui.spinner size="sm" />          dentro de botones (14 px)
    <x-ui.spinner size="lg" />          zonas grandes (24 px)
    <x-ui.spinner label="Cargando equipos" />  texto para lectores de pantalla

    Hereda el color del texto (currentColor).
============================================================ --}}
@props([
    'size' => 'md',
    'label' => null,
])

@php
    $dimensiones = match ($size) {
        'sm' => 'h-3.5 w-3.5',
        'lg' => 'h-6 w-6',
        default => 'h-4 w-4',
    };
@endphp

<span
    {{ $attributes->class(['inline-flex shrink-0 items-center justify-center']) }}
    @if ($label) role="status" @endif
>
    <svg
        class="ui-spinner {{ $dimensiones }}"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
    ><use href="{{ \App\Support\Icono::url('i-cfc9fd5') }}"></use></svg>

    @if ($label)
        <span class="sr-only">{{ $label }}</span>
    @endif
</span>
