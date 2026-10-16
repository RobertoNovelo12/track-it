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
                sm:justify-between

                gap-4
            "
        >

            {{-- ========================================================
                TÍTULO / BREADCRUMBS
            ======================================================== --}}
            <div
                class="
                    min-w-0
                "
            >

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Editar tipo de equipo
                </h1>


                <div
                    class="
                        mt-1

                        flex
                        flex-wrap
                        items-center

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >

                    <a
                        href="{{ route('catalogos.index') }}"

                        wire:navigate.hover

                        class="
                            hover:text-[var(--theme-primary)]

                            transition-colors
                        "
                    >
                        Administración
                    </a>


                    <span
                        class="
                            mx-2
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ›
                    </span>


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
                            hover:text-[var(--theme-primary)]

                            transition-colors
                        "
                    >
                        Equipos
                    </a>


                    <span
                        class="
                            mx-2
                            text-[var(--theme-text-muted)]
                        "
                    >
                        ›
                    </span>


                    <span
                        class="
                            text-[var(--theme-text)]
                        "
                    >
                        Editar
                    </span>

                </div>

            </div>


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