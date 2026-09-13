@extends('layouts.guest')

@section('title', 'Registro enviado')
@section('page-label', 'ESPERA A ACEPTACIÓN')

@section('content')

<div
    class="
        w-full
        max-w-md

        bg-[var(--theme-surface)]

        border
        border-[var(--theme-border-strong)]

        rounded-lg

        shadow-sm
        shadow-[var(--theme-shadow)]

        px-10
        py-10
    "
>

    <div class="flex flex-col items-center text-center">

        {{-- Ilustración --}}
        <div
            class="
                w-20
                h-20

                flex
                items-center
                justify-center

                mb-5
            "
        >
            <img
                src="{{ asset('images/pendiente.png') }}"
                alt="Registro pendiente de aprobación"
                class="
                    w-full
                    h-full
                    object-contain
                "
            >
        </div>


        <h1
            class="
                text-base
                font-semibold
                tracking-wide
                uppercase

                text-[var(--theme-text-strong)]

                mb-2
            "
        >
            Registro enviado
        </h1>


        <p
            class="
                text-sm
                text-[var(--theme-text-muted)]
                leading-relaxed

                mb-5
            "
        >
            Tu información ha sido enviada correctamente. Un administrador revisará tus datos y te notificará cuando tu cuenta sea activada.
        </p>


        {{-- Aviso --}}
        <div
            class="
                w-full

                flex
                gap-3
                items-start

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

                    flex-shrink-0

                    mt-0.5

                    text-[var(--theme-primary)]
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <circle cx="12" cy="12" r="9"/>
                <line x1="12" y1="10.5" x2="12" y2="16"/>
                <circle
                    cx="12"
                    cy="7.5"
                    r="0.9"
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
                Este proceso puede tardar hasta 24–48 horas hábiles.
            </p>

        </div>


        {{-- Notificación por correo --}}
        <div
            class="
                w-full

                border-t
                border-[var(--theme-border)]

                pt-6
                mb-6
            "
        >

            <div class="flex flex-col items-center gap-2">

                <svg
                    class="
                        w-6
                        h-6

                        text-[var(--theme-primary)]
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.4"
                >
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M3 6.5l9 6 9-6"/>
                </svg>


                <p
                    class="
                        text-xs
                        text-[var(--theme-text-muted)]
                        leading-relaxed
                    "
                >
                    Te enviaremos un correo electrónico cuando tu cuenta esté lista.
                </p>

            </div>

        </div>


        <a
            href="{{ route('login') }}"
            class="
                w-full

                flex
                items-center
                justify-center

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
            ENTENDIDO
        </a>

    </div>

</div>

@endsection