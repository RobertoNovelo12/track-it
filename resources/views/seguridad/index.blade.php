@extends('layouts.app')

@section('title', 'Administración y seguridad')

@section('content')

    <div>

        {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
        <div
            class="
            mb-6

            flex
            items-start
            justify-between
            gap-4
        ">

            <div>

                <h1
                    class="
                    text-xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                ">
                    Administración y seguridad
                </h1>


                <p
                    class="
                    mt-1
                    text-xs
                    text-[var(--theme-text-muted)]
                ">
                    Inicio

                    <span class="mx-1">
                        &gt;
                    </span>

                    Seguridad y Roles
                </p>

            </div>


            {{-- Nuevo usuario --}}
            <a
                href="{{ route('usuarios.create') }}"
                wire:navigate.hover
                class="
                    inline-flex
                    h-9
                    shrink-0
                    items-center
                    justify-center
                    gap-2

                    rounded-lg

                    bg-[var(--theme-primary)]

                    px-3
                    sm:px-4

                    text-xs
                    sm:text-sm
                    font-medium
                    text-white

                    transition-colors
                    hover:bg-[var(--theme-primary-hover)]
                "
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>

                Nuevo usuario
            </a>

        </div>


        {{-- Usuario creado --}}
        @if (session('usuarioCreado'))
            <div
                x-data="{ visible: true }"
                x-init="setTimeout(() => visible = false, 6000)"
                x-show="visible"
                x-transition.opacity
                role="status"
                class="mb-5 flex items-start justify-between gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-500"
            >
                <span>{{ session('usuarioCreado') }}</span>

                <button type="button" x-on:click="visible = false" aria-label="Cerrar" class="shrink-0 opacity-70 hover:opacity-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        @endif


        {{-- ============================================================
        CONTENIDO LIVEWIRE
    ============================================================ --}}
        <livewire:seguridad-index lazy />

    </div>

@endsection
