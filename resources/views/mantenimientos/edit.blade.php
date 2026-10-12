@extends('layouts.app')

@section('title', 'Editar mantenimiento')

@section('content')

<div>

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div
        class="
            mb-6
            flex
            flex-col
            gap-4

            sm:flex-row
            sm:items-start
            sm:justify-between
        "
    >
        <div class="min-w-0">

            {{-- Título --}}
            <h1
                class="
                    text-2xl
                    font-bold
                    text-[var(--theme-text-strong)]
                "
            >
                Editar mantenimiento
            </h1>


            {{-- Breadcrumb --}}
            <div
                class="
                    mt-1

                    flex
                    items-center
                    flex-wrap
                    gap-2

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                {{-- Vista General --}}
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="
                        transition-colors
                        hover:text-[var(--theme-primary)]
                    "
                >
                    Vista General
                </a>


                {{-- Separador --}}
                <svg
                    class="w-3 h-3 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M9 6l6 6-6 6"/>
                </svg>


                {{-- Mantenimientos --}}
                <a
                    href="{{ route('mantenimientos.index') }}"
                    wire:navigate
                    class="
                        transition-colors
                        hover:text-[var(--theme-primary)]
                    "
                >
                    Mantenimientos
                </a>


                {{-- Separador --}}
                <svg
                    class="w-3 h-3 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M9 6l6 6-6 6"/>
                </svg>


                {{-- Página actual --}}
                <span>
                    Editar mantenimiento
                </span>
            </div>


            {{-- Descripción --}}
            <p
                class="
                    mt-2
                    text-sm
                    text-[var(--theme-text-muted)]
                "
            >
                Modifica la información del mantenimiento #{{ $mantenimientoId }}.
            </p>

        </div>


        {{-- Botón Volver --}}
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
                py-2.5

                text-sm
                font-medium
                text-[var(--theme-text)]

                transition-colors

                hover:bg-[var(--theme-surface-soft)]
            "
        >
            Volver
        </a>

    </div>


    {{-- ============================================================
        FORMULARIO DE EDICIÓN
    ============================================================ --}}
    <livewire:mantenimiento-edit
        :mantenimiento-id="$mantenimientoId"
    />

</div>

@endsection