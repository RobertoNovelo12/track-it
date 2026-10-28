{{-- ============================================================
    BOTÓN PRINCIPAL DE UN APARTADO (Administración)
    Con href se muestra como enlace; sin href, como botón.
============================================================ --}}
@props(['href' => null, 'icon' => 'plus'])

@php
    $clases = 'inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md border border-transparent bg-[var(--theme-primary)] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:opacity-60 sm:w-auto';
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate.hover {{ $attributes->class($clases) }}>
@else
    <button type="button" {{ $attributes->class($clases) }}>
@endif

    @if ($icon === 'plus')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>
    @endif

    {{ $slot }}

@if ($href)
    </a>
@else
    </button>
@endif
