@extends('layouts.app')

@section('title', 'Vista general')

@section('content')

<div class="space-y-4">

    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}

    <div
        class="
            flex
            flex-col
            gap-4

            sm:flex-row
            sm:items-center
            sm:justify-between
        "
    >

        {{-- ========================================================
            TÍTULO
        ======================================================== --}}

        <div class="min-w-0">

            <h1
                class="
                    text-2xl
                    font-semibold
                    leading-tight

                    text-[var(--theme-text-strong)]
                "
            >
                Vista general
            </h1>


            <p
                class="
                    mt-1

                    text-xs

                    text-[var(--theme-text-muted)]
                "
            >
                Inicio
            </p>

        </div>



        {{-- ========================================================
            ACCIONES RÁPIDAS
        ======================================================== --}}

        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
            "
        >

            {{-- EQUIPOS --}}

            @if (\Illuminate\Support\Facades\Route::has('equipos.index'))

                <a
                    href="{{ route('equipos.index') }}"
                    wire:navigate

                    class="
                        inline-flex
                        h-9
                        items-center
                        justify-center
                        gap-2

                        rounded-lg

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-3

                        text-xs
                        font-medium

                        text-[var(--theme-text)]

                        hover:bg-[var(--theme-surface-soft)]
                    "
                >

                    <svg
                        class="
                            h-4
                            w-4
                        "

                        viewBox="0 0 24 24"

                        fill="none"

                        stroke="currentColor"

                        stroke-width="1.5"
                    >

                        <rect
                            x="3"
                            y="4"
                            width="18"
                            height="12"
                            rx="2"
                        />


                        <path
                            d="
                                M8 20
                                h8
                            "
                        />


                        <path
                            d="
                                M12 16
                                v4
                            "
                        />

                    </svg>


                    <span>
                        Equipos
                    </span>

                </a>

            @endif



            {{-- ASIGNAR --}}

            @if (\Illuminate\Support\Facades\Route::has('asignaciones.index'))

                <a
                    href="{{ route('asignaciones.index') }}"
                    wire:navigate

                    class="
                        inline-flex
                        h-9
                        items-center
                        justify-center
                        gap-2

                        rounded-lg

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-3

                        text-xs
                        font-medium

                        text-[var(--theme-text)]

                        hover:bg-[var(--theme-surface-soft)]
                    "
                >

                    <svg
                        class="
                            h-4
                            w-4
                        "

                        viewBox="0 0 24 24"

                        fill="none"

                        stroke="currentColor"

                        stroke-width="1.5"
                    >

                        <path
                            d="
                                M7 7
                                h11
                            "
                        />


                        <path
                            d="
                                M15 4
                                l3 3
                                -3 3
                            "
                        />


                        <path
                            d="
                                M17 17
                                H6
                            "
                        />


                        <path
                            d="
                                M9 14
                                l-3 3
                                3 3
                            "
                        />

                    </svg>


                    <span>
                        Asignar
                    </span>

                </a>

            @endif



            {{-- ACTUALIZAR DASHBOARD --}}

            <button
                type="button"

                onclick="
                    if (window.Livewire) {
                        Livewire.dispatch(
                            'dashboard-refresh-requested'
                        );
                    }
                "

                class="
                    inline-flex
                    h-9
                    items-center
                    justify-center
                    gap-2

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    px-3

                    text-xs
                    font-medium

                    text-[var(--theme-text)]

                    hover:bg-[var(--theme-surface-soft)]
                "
            >

                <svg
                    class="
                        h-4
                        w-4
                    "

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.5"
                >

                    <path
                        d="
                            M20 11

                            a8 8
                            0 1 0
                            2 5.3
                        "
                    />


                    <path
                        d="
                            M20 4
                            v7
                            h-7
                        "
                    />

                </svg>


                <span>
                    Actualizar
                </span>

            </button>

        </div>

    </div>



    {{-- ============================================================
        DASHBOARD OPERATIVO
    ============================================================ --}}

    <livewire:dashboard-overview />

</div>

@endsection