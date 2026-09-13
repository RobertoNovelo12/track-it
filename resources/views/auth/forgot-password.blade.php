@extends('layouts.guest')

@section('title', 'Recuperar contraseña')
@section('page-label', 'RECUPERAR CONTRASEÑA')

@section('content')

<x-auth-card heading="Recuperar contraseña" icon="key">

    <p
        class="
            text-xs
            text-[var(--theme-text-muted)]
            text-center
            mb-5
            leading-relaxed
        "
    >
        Ingresa tu correo registrado y te enviaremos un enlace para restablecer tu contraseña.
    </p>


    @if (session('status'))

        <div
            class="
                mb-4

                px-3
                py-2

                rounded-md

                bg-emerald-500/10

                border
                border-emerald-500/25

                text-sm
                text-emerald-500
            "
        >
            {{ session('status') }}
        </div>

    @endif


    <form
        method="POST"
        action="{{ route('password.email') }}"
        class="space-y-4"
    >

        @csrf


        {{-- Correo --}}
        <div>

            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Correo electrónico"
                autofocus
                autocomplete="email"
                class="
                    w-full

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    px-4
                    py-2.5

                    text-sm
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    focus:outline-none
                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]
                    focus:border-[var(--theme-primary)]

                    transition-colors
                "
            >

            @error('email')

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-danger)]
                    "
                >
                    {{ $message }}
                </p>

            @enderror

        </div>


        {{-- Enviar enlace --}}
        <button
            type="submit"
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
            ENVIAR ENLACE

            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
        </button>


        {{-- Volver --}}
        <div class="text-center pt-1">

            <a
                href="{{ route('login') }}"
                class="
                    text-xs
                    text-[var(--theme-primary)]

                    hover:text-[var(--theme-primary-hover)]
                    hover:underline

                    transition-colors
                "
            >
                Volver a iniciar sesión
            </a>

        </div>

    </form>

</x-auth-card>

@endsection