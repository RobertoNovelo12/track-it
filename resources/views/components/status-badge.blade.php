@props(['status'])

@php
    $normalized = \Illuminate\Support\Str::of($status)->lower()->ascii()->toString();

    $styles = match (true) {
        str_contains($normalized, 'activo') && !str_contains($normalized, 'inactivo') => 'bg-green-100 text-green-700',
        str_contains($normalized, 'mantenimiento') => 'bg-[#50514F]/10 text-[#50514F]/70',
        str_contains($normalized, 'almacen') || str_contains($normalized, 'stock') => 'bg-[#247BA0]/10 text-[#247BA0]',
        str_contains($normalized, 'baja') || str_contains($normalized, 'inactivo') => 'bg-red-100 text-red-600',
        str_contains($normalized, 'asignado') || str_contains($normalized, 'prestamo') => 'bg-[#CB8B2A]/15 text-[#CB8B2A]',
        default => 'bg-[#50514F]/10 text-[#50514F]/70',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $styles }}">
    {{ $status }}
</span>