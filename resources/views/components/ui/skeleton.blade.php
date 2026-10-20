{{-- ============================================================
    PIEZA DE SKELETON
    ------------------------------------------------------------
    <x-ui.skeleton />                          línea de texto (h-3, ancho completo)
    <x-ui.skeleton class="h-4 w-40" />         línea con medidas propias
    <x-ui.skeleton variant="block" class="h-32" />   bloque (gráfica, imagen)
    <x-ui.skeleton variant="circle" class="h-10 w-10" />  avatar o icono
    <x-ui.skeleton variant="pill" class="h-5 w-16" />     etiqueta de estado

    Es decorativo: los lectores de pantalla lo ignoran. El contenedor
    que carga debe indicar aria-busy="true".
============================================================ --}}
@props([
    'variant' => 'line',
])

@php
    $forma = match ($variant) {
        'circle' => 'rounded-full',
        'pill' => 'rounded-full',
        'block' => 'rounded-[var(--radius-lg)]',
        default => 'rounded-[var(--radius-sm)]',
    };

    // Medidas por defecto solo si no se indicaron.
    $clases = (string) $attributes->get('class', '');

    $alto = preg_match('/(^|\s)(\w+:)?h-/', $clases) ? '' : ($variant === 'block' ? 'h-24' : 'h-3');
    $ancho = preg_match('/(^|\s)(\w+:)?w-/', $clases) ? '' : 'w-full';
@endphp

<span
    {{ $attributes->class(['ui-skeleton', $forma, $alto, $ancho]) }}
    aria-hidden="true"
></span>
