@extends('layouts.guest')

@section('title', 'Crear cuenta')
@section('page-label', 'REGISTRO')

@section('content')

<x-auth-card heading="Crear cuenta" icon="user-plus">

    <form
        method="POST"
        action="{{ route('register') }}"
        class="space-y-4"
    >

        @csrf


        {{-- ============================================================
            NOMBRES
        ============================================================ --}}
        <div>

            <input
                type="text"
                name="nombres"
                value="{{ old('nombres') }}"
                placeholder="Nombre(s)"
                autofocus
                autocomplete="given-name"
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

            @error('nombres')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- ============================================================
            APELLIDOS
        ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            {{-- Apellido paterno --}}
            <div>

                <input
                    type="text"
                    name="apellido_paterno"
                    value="{{ old('apellido_paterno') }}"
                    placeholder="Apellido paterno"
                    autocomplete="family-name"
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

                @error('apellido_paterno')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Apellido materno --}}
            <div>

                <input
                    type="text"
                    name="apellido_materno"
                    value="{{ old('apellido_materno') }}"
                    placeholder="Apellido materno"
                    autocomplete="additional-name"
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

                @error('apellido_materno')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            CORREO
        ============================================================ --}}
        <div>

            <input
                type="email"
                name="correo"
                value="{{ old('correo') }}"
                placeholder="Correo electrónico"
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

            @error('correo')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- ============================================================
            NOMBRE DE USUARIO
        ============================================================ --}}
        <div>

            <input
                type="text"
                name="username"
                value="{{ old('username') }}"
                placeholder="Nombre de usuario"
                autocomplete="username"
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

            @error('username')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- ============================================================
            CONTRASEÑA
        ============================================================ --}}
        <div>

            <div class="relative">

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Contraseña"
                    autocomplete="new-password"
                    class="
                        w-full

                        bg-[var(--theme-surface)]

                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-4
                        py-2.5
                        pr-10

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


                <button
                    type="button"
                    onclick="togglePassword('password')"
                    class="
                        absolute
                        inset-y-0
                        right-3

                        flex
                        items-center

                        text-[var(--theme-text-muted)]

                        hover:text-[var(--theme-text)]

                        transition-colors
                    "
                    aria-label="Mostrar u ocultar contraseña"
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                        <line x1="3" y1="21" x2="21" y2="3"/>
                    </svg>
                </button>

            </div>


            @error('password')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- ============================================================
            CONFIRMAR CONTRASEÑA
        ============================================================ --}}
        <div>

            <div class="relative">

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirmar contraseña"
                    autocomplete="new-password"
                    class="
                        w-full

                        bg-[var(--theme-surface)]

                        border
                        border-[var(--theme-border-strong)]

                        rounded-md

                        px-4
                        py-2.5
                        pr-10

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


                <button
                    type="button"
                    onclick="togglePassword('password_confirmation')"
                    class="
                        absolute
                        inset-y-0
                        right-3

                        flex
                        items-center

                        text-[var(--theme-text-muted)]

                        hover:text-[var(--theme-text)]

                        transition-colors
                    "
                    aria-label="Mostrar u ocultar confirmación de contraseña"
                >
                    <svg
                        class="w-5 h-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                        <line x1="3" y1="21" x2="21" y2="3"/>
                    </svg>
                </button>

            </div>

        </div>


        {{-- ============================================================
            AVISO
        ============================================================ --}}
        <p
            class="
                text-xs
                text-[var(--theme-text-muted)]
                leading-relaxed
            "
        >
            Tras crear tu cuenta, un administrador deberá aprobarla antes de que puedas iniciar sesión.
        </p>


        {{-- ============================================================
            CREAR CUENTA
        ============================================================ --}}
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
            CREAR CUENTA

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


        {{-- ============================================================
            LOGIN
        ============================================================ --}}
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
                Ya tengo una cuenta
            </a>

        </div>

    </form>

</x-auth-card>


<script>

    function togglePassword(fieldId) {

        const field =
            document.getElementById(fieldId);

        field.type =
            field.type === 'password'
                ? 'text'
                : 'password';

    }

</script>

@endsection