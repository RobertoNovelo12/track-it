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
            flex-wrap
            items-center
            justify-between
            gap-2
        "
    >

        {{-- «Volver» a la izquierda; se oculta si no hay pantalla anterior. --}}
        @include('layouts.partials.boton-volver')





        {{-- ========================================================
            ACCIONES RÁPIDAS
        ======================================================== --}}

        {{-- Móvil: las tres acciones en una sola fila del mismo ancho. --}}
        <div
            class="
                ml-auto
                grid
                w-full
                grid-cols-3
                items-center
                gap-2

                sm:flex
                sm:w-auto
                sm:flex-wrap
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
                        min-w-0
                        gap-1.5
                        sm:gap-2

                        rounded-lg

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-2

                        sm:px-3

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
                        min-w-0
                        gap-1.5
                        sm:gap-2

                        rounded-lg

                        border
                        border-[var(--theme-border)]

                        bg-[var(--theme-surface)]

                        px-2

                        sm:px-3

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
                    min-w-0
                    gap-1.5
                    sm:gap-2

                    rounded-lg

                    border
                    border-[var(--theme-border)]

                    bg-[var(--theme-surface)]

                    px-2

                    sm:px-3

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


                {{-- En pantallas muy angostas (<360px) solo queda el icono. --}}
                <span class="max-[359px]:sr-only">
                    Actualizar
                </span>

            </button>

        </div>

    </div>



    {{-- ============================================================
        DASHBOARD OPERATIVO
    ============================================================ --}}

    {{--
        Carga por secciones.

        Rendimiento (LCP / CLS): si las tarjetas ya están en caché (lo
        normal), el componente se pinta en esta misma respuesta, sin
        «lazy». Así no hay segunda petición antes de ver los números ni
        salto de diseño al cambiar el skeleton por el contenido (era el
        CLS de 0.62). Las gráficas siguen cargándose después con
        wire:init cuando no están en caché.

        Solo con la caché fría (primera visita tras «Actualizar» o tras
        un cambio en los datos) se usa lazy, para no bloquear la página
        con las consultas de las tarjetas.
    --}}
    @php
        $kpisEnCache = \Illuminate\Support\Facades\Cache::has(
            app(\App\Services\SystemCacheService::class)->key('dashboard', 'kpis')
        );
    @endphp

    @if ($kpisEnCache)
        <livewire:dashboard-overview />
    @else
        <livewire:dashboard-overview lazy />
    @endif

</div>

@endsection