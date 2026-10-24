@props(['status'])

@php
    $normalized = \Illuminate\Support\Str::of($status)
        ->lower()
        ->ascii()
        ->toString();

    /*
     * Colores del tema (no amber/emerald fijos): en modo claro el texto
     * cumple el contraste mínimo de WCAG AA.
     */
    $styles = match (true) {

        // Movimientos del historial de asignaciones.
        str_contains($normalized, 'reasign')
                => 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

        str_contains($normalized, 'asignacion')
                => 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',

        // Estados de usuario.
        str_contains($normalized, 'pendiente')
                => 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

        str_contains($normalized, 'activo')
            && !str_contains($normalized, 'inactivo')
                => 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

        str_contains($normalized, 'mantenimiento')
                => 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',

        str_contains($normalized, 'almacen')
            || str_contains($normalized, 'stock')
                => 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',

        str_contains($normalized, 'baja')
            || str_contains($normalized, 'inactivo')
                => 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

        str_contains($normalized, 'asignado')
            || str_contains($normalized, 'prestamo')
                => 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

        default
                => 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
    };
@endphp

<span
    class="
        inline-flex
        items-center

        px-2
        py-0.5

        {{-- Etiqueta rectangular discreta (antes píldora). --}}
        rounded-md

        text-xs
        font-medium

        {{ $styles }}
    "
>
    {{ $status }}
</span>