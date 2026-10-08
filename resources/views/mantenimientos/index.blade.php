@extends('layouts.app')

@section('title', 'Mantenimientos')

@section('content')

<div>
    <div
        class="
            mb-6
            flex flex-col
            gap-4
            sm:flex-row
            sm:items-center
            sm:justify-between
        "
    >
        <div>
            <h1 class="text-2xl font-bold text-[var(--theme-text-strong)]">
                Mantenimientos
            </h1>
            {{-- Breadcrumb --}}
<div
    class="
        flex
        items-center
        flex-wrap
        gap-2
        mb-2
        text-xs
        text-[var(--theme-text-muted)]
    "
>
    <a
        href="{{ route('dashboard') }}"
        wire:navigate
        class="
            hover:text-[var(--theme-primary)]
            transition-colors
        "
    >
        Vista General
    </a>

    <svg
        class="w-3 h-3"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
    >
        <path d="M9 6l6 6-6 6"/>
    </svg>

    <span>
        Mantenimientos
    </span>
</div>

            <p class="mt-1 text-sm text-[var(--theme-text-muted)]">
                Consulta, registra y administra los mantenimientos realizados a los equipos tecnológicos.
            </p>
        </div>

        <a
            href="{{ route('mantenimientos.create') }}"
            wire:navigate
            class="
                inline-flex
                items-center
                justify-center
                rounded-lg
                bg-[var(--theme-primary)]
                px-4
                py-2.5
                text-sm
                font-medium
                text-white
                transition
                hover:bg-[var(--theme-primary-hover)]
            "
        >
            Nuevo mantenimiento
        </a>
    </div>

    <livewire:mantenimientos-index />
</div>

@endsection
