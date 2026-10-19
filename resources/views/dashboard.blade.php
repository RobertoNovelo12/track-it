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
                    wire:navigate.hover

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

            @if (\Illuminate\Support\Facades\Route::has('asignaciones.index') && \Illuminate\Support\Facades\Gate::allows('gestionar'))

                <a
                    href="{{ route('asignaciones.index') }}"
                    wire:navigate.hover

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

            {{--
                Se bloquea al presionarlo y el icono gira hasta que el
                componente de la Vista general responde con
                "dashboard-refreshed". Si la respuesta no llega (error
                de red, componente aún cargando), se libera a los 20 s.
            --}}
            <button
                type="button"

                x-data="{
                    actualizando: false,
                    limite: null,

                    actualizar() {
                        if (this.actualizando || ! window.Livewire) return;

                        this.actualizando = true;
                        this.limite = setTimeout(() => this.terminar(), 20000);

                        Livewire.dispatch('dashboard-refresh-requested');
                    },

                    terminar() {
                        clearTimeout(this.limite);
                        this.actualizando = false;
                    },
                }"

                x-on:click="actualizar()"
                x-on:dashboard-refreshed.window="terminar()"

                x-bind:disabled="actualizando"
                x-bind:aria-busy="actualizando"
                x-bind:title="actualizando ? 'Actualizando…' : 'Actualizar vista general'"

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

                    disabled:cursor-wait
                    disabled:opacity-70
                    disabled:hover:bg-[var(--theme-surface)]

                    transition-colors
                "
            >

                {{--
                    Icono centrado en el viewBox (círculo con centro en
                    12,12) para que al girar no se desplace.
                --}}
                <svg
                    class="
                        block
                        h-4
                        w-4
                        shrink-0
                        origin-center
                    "

                    x-bind:class="actualizando && 'animate-spin'"

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"

                    aria-hidden="true"
                >
                    <path d="M20 12a8 8 0 1 1-2.34-5.66L20 8.5"/>

                    <path d="M20 4v4.5h-4.5"/>
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