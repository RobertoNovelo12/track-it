@extends('layouts.app')

@section('title', 'Buscar Equipos')

@section('content')

<div>

    {{-- ============================================================
        CABECERA
    ============================================================ --}}
    <div
        class="
            flex
            items-start
            justify-between
            gap-3
            sm:gap-4
            mb-5
            sm:mb-6
        "
    >

        {{-- Título y breadcrumb --}}
        <div class="min-w-0 flex-1">

            <h1
                class="
                    text-xl
                    font-semibold
                    leading-tight
                    text-[#50514F]
                "
            >
                Búsqueda de equipos
            </h1>


            <p
                class="
                    text-xs
                    text-[#50514F]/60
                    mt-1
                    leading-relaxed
                "
            >

                <a
                    href="{{ route('equipos.index') }}"
                    class="
                        hover:text-[#247BA0]
                        transition-colors
                    "
                >
                    Equipos Tecnológicos
                </a>

                <span class="mx-1">
                    ›
                </span>

                <span>
                    Resultados de búsqueda
                </span>

            </p>

        </div>


        {{-- Volver --}}
        <a
            href="{{ route('equipos.index') }}"
            class="
                shrink-0
                h-10

                flex
                items-center
                justify-center
                gap-2

                border
                border-[#50514F]/20
                rounded-md
                bg-white

                px-3
                sm:px-4

                text-sm
                font-medium
                text-[#50514F]/70

                hover:bg-[#50514F]/5
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
                Inventario
            </span>

        </a>

    </div>


    {{-- ============================================================
        COMPONENTE DE BÚSQUEDA
    ============================================================ --}}
    <livewire:equipo-search lazy />

</div>

@endsection