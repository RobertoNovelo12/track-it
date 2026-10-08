@extends('layouts.app')

@section('title', 'Generación de reportes')

@section('content')

<div>
    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    <div class="mb-6">

        <h1
            class="
                text-2xl
                font-bold
                text-[var(--theme-text-strong)]
            "
        >
            Generación de reportes
        </h1>


        {{-- ========================================================
            BREADCRUMB
        ======================================================== --}}
        <nav
            class="
                mt-1
                flex
                flex-wrap
                items-center
                gap-2

                text-sm
                text-[var(--theme-text-muted)]
            "
            aria-label="Breadcrumb"
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
            <span
                class="
                    text-[var(--theme-text-muted)]
                "
                aria-hidden="true"
            >
                ›
            </span>


            {{-- Página actual --}}
            <span
                class="
                    text-[var(--theme-text)]
                "
                aria-current="page"
            >
                Reportes
            </span>

        </nav>


        {{-- Descripción --}}
        <p
            class="
                mt-2
                text-sm
                text-[var(--theme-text-muted)]
            "
        >
            Busca activos, aplica filtros y genera documentos en Excel, PDF o CSV.
        </p>

    </div>


    {{-- ============================================================
        COMPONENTE DE REPORTES
    ============================================================ --}}
    <livewire:reportes-index />

</div>

@endsection