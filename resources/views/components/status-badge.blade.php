@props(['status'])

@php
    $normalized = \Illuminate\Support\Str::of($status)
        ->lower()
        ->ascii()
        ->toString();

    $styles = match (true) {

        str_contains($normalized, 'activo')
            && !str_contains($normalized, 'inactivo')
                => 'bg-emerald-500/15 text-emerald-500',

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
                => 'bg-amber-500/15 text-amber-500',

        default
                => 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
    };
@endphp

<span
    class="
        inline-flex
        items-center

        px-2.5
        py-1

        rounded-full

        text-xs
        font-medium

        {{ $styles }}
    "
>
    {{ $status }}
</span>