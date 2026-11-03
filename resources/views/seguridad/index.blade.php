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
            items-center
            justify-between
            gap-2
        ">
            @include('layouts.partials.boton-volver')



            <div class="flex items-center gap-2">
            {{-- Persona sin acceso (solo inventario) --}}
            <a
                href="{{ route('usuarios.create-sin-acceso') }}"
                wire:navigate.hover
                title="Registrar a alguien solo para asignarle equipos; no podrá iniciar sesión"
                class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:px-4 sm:text-sm"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                <span class="hidden sm:inline">Persona sin acceso</span>
                <span class="sm:hidden">Sin acceso</span>
            </a>

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
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-6aed230') }}"></use></svg>

                Nuevo usuario
            </a>
            </div>

        </div>


        {{-- Usuario creado: se muestra como aviso global (layouts/app). --}}


        {{-- ============================================================
        CONTENIDO LIVEWIRE
    ============================================================ --}}
        <livewire:seguridad-index lazy />

    </div>

@endsection
