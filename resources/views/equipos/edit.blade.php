@extends('layouts.app')

@section('title', 'Editar Equipo')

@section('content')

<div>

    {{-- ============================================================
        CABECERA
    ============================================================ --}}
    <div
        class="
            flex
            items-start
            justify-end

            gap-3
            sm:gap-4

            mb-5
            sm:mb-6
        "
    >



        {{-- Volver --}}
        <a
            href="{{ route('equipos.index') }}"
            wire:navigate.hover
            class="
                shrink-0

                h-10

                flex
                items-center
                justify-center
                gap-2

                border
                border-[var(--theme-border-strong)]

                rounded-md

                bg-[var(--theme-surface)]

                px-3
                sm:px-4

                text-sm
                font-medium
                text-[var(--theme-text-muted)]

                hover:bg-[var(--theme-surface-soft)]
                hover:text-[var(--theme-text)]
                hover:border-[var(--theme-primary-border)]

                transition-colors
            "
        >

            <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M15 6l-6 6 6 6"/>
            </svg>

            <span class="hidden sm:inline">
                Volver
            </span>

        </a>

    </div>


    {{-- ============================================================
        COMPONENTE DE EDICIÓN
    ============================================================ --}}
    <livewire:equipo-edit
        :equipo-id="$equipoId"
    />

</div>

@endsection