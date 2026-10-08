@extends('layouts.app')

@section('title', 'Nuevo mantenimiento')

@section('content')

<div>
    <div
        class="
            mb-6
            flex
            flex-col
            gap-4
            sm:flex-row
            sm:items-center
            sm:justify-between
        "
    >
        <div>
            <h1
                class="
                    text-2xl
                    font-bold
                    text-[var(--theme-text-strong)]
                "
            >
                Nuevo mantenimiento
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

    <a
        href="{{ route('mantenimientos.index') }}"
        wire:navigate
        class="
            hover:text-[var(--theme-primary)]
            transition-colors
        "
    >
        Mantenimientos
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
        Nuevo mantenimiento
    </span>
</div>

            <p
                class="
                    mt-1
                    text-sm
                    text-[var(--theme-text-muted)]
                "
            >
                Registra la intervención realizada a un equipo tecnológico.
            </p>
        </div>

        <a
            href="{{ route('mantenimientos.index') }}"
            wire:navigate
            class="
                inline-flex
                items-center
                justify-center
                rounded-lg
                border
                border-[var(--theme-border)]
                px-4
                py-2
                text-sm
                font-medium
                text-[var(--theme-text)]
            "
        >
            Volver
        </a>
    </div>

    <livewire:mantenimiento-create />
</div>

@endsection
