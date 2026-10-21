@extends('layouts.app')


@section(
    'title',
    'Editar tipo de equipo'
)


@section('content')

    <div
        class="
            w-full
            max-w-[1600px]
            mx-auto
        "
    >

        {{-- ============================================================
            ENCABEZADO
        ============================================================ --}}
        <div
            class="
                mb-6

                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-end

                gap-4
            "
        >



            {{-- ========================================================
                VOLVER
            ======================================================== --}}
            <a
                href="{{ route(
                    'catalogos.index',
                    [
                        'section' =>
                            'equipos',
                    ]
                ) }}"

                wire:navigate.hover

                class="
                    h-10

                    px-4

                    shrink-0

                    inline-flex
                    items-center
                    justify-center
                    gap-2

                    rounded-md

                    border
                    border-[var(--theme-border-strong)]

                    bg-[var(--theme-surface)]

                    text-sm
                    font-medium
                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text-strong)]

                    transition-colors
                "
            >
                <svg
                    class="
                        w-4
                        h-4
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M15 18l-6-6 6-6"/>
                </svg>

                Volver
            </a>

        </div>


        {{-- ============================================================
            EDITOR DEL TIPO DE EQUIPO
        ============================================================ --}}
        <livewire:catalogos.tipo-equipo-editor
            :tipo-equipo-id="$tipoEquipoId"
            :key="'tipo-equipo-editor-' . $tipoEquipoId"
        />

    </div>

@endsection