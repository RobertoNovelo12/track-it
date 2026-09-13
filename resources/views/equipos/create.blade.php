@extends('layouts.app')

@section('title', 'Nueva Alta de Equipo')

@section('content')

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
                sm:text-xl
                font-semibold
                leading-tight
                text-[#50514F]
            "
        >
            Nueva Alta de equipo
        </h1>

        <p
            class="
                text-xs
                text-[#50514F]/60
                mt-1
                leading-relaxed
            "
        >
            <span>Equipos Tecnológicos</span>

            <span class="mx-1">
                ›
            </span>

            <span>
                Nuevo activo
            </span>
        </p>

    </div>


    {{-- Importar --}}
    <button
        type="button"
        class="
            shrink-0
            flex
            items-center
            justify-center
            gap-2
            h-10
            px-3
            sm:px-4
            border
            border-[#50514F]/20
            rounded-md
            bg-white
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
            stroke-width="1.5"
        >
            <path d="M12 3v12"/>
            <path d="M7 10l5 5 5-5"/>
            <path d="M4 21h16"/>
        </svg>

        <span>
            Importar
        </span>

    </button>

</div>


{{-- Formulario Livewire --}}
<livewire:equipo-create />

@endsection