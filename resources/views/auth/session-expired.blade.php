@extends('layouts.guest')

@section('title', 'Sesión caducada')
@section('page-label', 'SESIÓN CADUCADA')

@section('content')

<div
    class="
        w-full
        max-w-md

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border-strong)]

        rounded-lg

        shadow-lg
        shadow-[var(--theme-shadow)]

        px-6
        sm:px-10

        py-9
        sm:py-10
    "
>

    <div
        class="
            flex
            flex-col
            items-center
            text-center
        "
    >

        {{-- ============================================================
            ICONO
        ============================================================ --}}
        <div
            class="
                relative

                w-24
                h-24

                mb-5

                flex
                items-center
                justify-center
            "
        >

            {{-- Fondo --}}
            <div
                class="
                    absolute
                    inset-1

                    rounded-full

                    bg-[var(--theme-primary-soft-subtle)]
                "
            ></div>


            {{-- Reloj --}}
            <svg
                class="
                    relative

                    w-16
                    h-16

                    text-[var(--theme-text)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.3"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="8"
                />

                <path d="M12 7v5"/>
                <path d="M12 12l3 1.5"/>
            </svg>


            {{-- Advertencia --}}
            <div
                class="
                    absolute
                    right-1
                    bottom-2

                    w-9
                    h-9

                    rounded-full

                    bg-[var(--theme-primary)]

                    border-[3px]
                    border-[var(--theme-surface)]

                    flex
                    items-center
                    justify-center

                    text-white
                "
            >
                <span
                    class="
                        text-lg
                        font-semibold
                        leading-none
                    "
                >
                    !
                </span>
            </div>

        </div>


        {{-- ============================================================
            TÍTULO
        ============================================================ --}}
        <h1
            class="
                text-base
                font-semibold

                text-[var(--theme-text-strong)]

                mb-2
            "
        >
            Tu sesión ha caducado
        </h1>


        {{-- ============================================================
            DESCRIPCIÓN
        ============================================================ --}}
        <p
            class="
                max-w-sm

                text-xs
                text-[var(--theme-text-muted)]

                leading-relaxed

                mb-6
            "
        >
            Por motivos de seguridad, tu sesión ha expirado debido
            a un periodo prolongado de inactividad.
        </p>


        {{-- ============================================================
            AVISO
        ============================================================ --}}
        <div
            class="
                w-full

                flex
                items-start
                gap-3

                bg-[var(--theme-primary-soft)]

                border
                border-[var(--theme-primary-border)]

                rounded-md

                px-4
                py-3

                mb-6

                text-left
            "
        >

            <svg
                class="
                    w-5
                    h-5

                    shrink-0

                    mt-0.5

                    text-[var(--theme-primary)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                />

                <path d="M12 11v5"/>

                <circle
                    cx="12"
                    cy="7.5"
                    r=".7"
                    fill="currentColor"
                    stroke="none"
                />
            </svg>


            <p
                class="
                    text-xs
                    text-[var(--theme-text)]

                    leading-relaxed
                "
            >
                Para proteger tu información, cerramos tu sesión
                automáticamente cuando detectamos inactividad.
            </p>

        </div>


        {{-- ============================================================
            LOGIN
        ============================================================ --}}
        <a
            href="{{ route('login') }}"
            class="
                w-full

                flex
                items-center
                justify-center
                gap-2

                bg-[var(--theme-primary)]
                hover:bg-[var(--theme-primary-hover)]

                text-white

                font-medium
                tracking-wide
                text-sm

                rounded-md

                py-3

                transition-colors
            "
        >
            INICIAR SESIÓN

            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path d="M5 12h14"/>
                <path d="M15 8l4 4-4 4"/>
            </svg>
        </a>

    </div>

</div>

@endsection