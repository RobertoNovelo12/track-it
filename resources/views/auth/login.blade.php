@extends('layouts.guest')

@section('title', 'Iniciar sesión')
@section('page-label', 'LOGIN')

@section('content')

<x-auth-card heading="Iniciar sesión" icon="user">

    {{-- Mensaje de sesión caducada / error general --}}
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
        id="loginForm"
        method="POST"
        action="{{ route('login') }}"
        class="space-y-4"
    >

        @csrf


        {{-- ============================================================
            USUARIO O CORREO
        ============================================================ --}}
        <div>

            <input
                id="loginInput"
                type="text"
                name="login"
                value="{{ old('login') }}"
                placeholder="Nombre de usuario o correo"
                autofocus
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

            @error('login')

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
                    autocomplete="current-password"
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
                    id="togglePasswordButton"
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

                        disabled:opacity-40
                        disabled:cursor-not-allowed

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


        {{-- ============================================================
            RECUPERAR CONTRASEÑA
        ============================================================ --}}
        <div class="text-right -mt-1">

            <a
                href="{{ route('password.request') }}"
                class="
                    text-xs
                    text-[var(--theme-primary)]

                    hover:text-[var(--theme-primary-hover)]
                    hover:underline

                    transition-colors
                "
            >
                Olvidé la contraseña
            </a>

        </div>


        {{-- ============================================================
            BOTÓN LOGIN
        ============================================================ --}}
        <button
            id="loginButton"
            type="submit"
            class="
                w-full
                min-h-[44px]

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

                disabled:opacity-80
                disabled:cursor-wait
            "
        >

            {{-- Estado normal --}}
            <span
                id="loginNormalState"
                class="
                    flex
                    items-center
                    justify-center
                    gap-2
                "
            >
                INGRESAR

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
            </span>


            {{-- Estado cargando --}}
            <span
                id="loginLoadingState"
                class="
                    hidden
                    items-center
                    justify-center
                "
            >
                <svg
                    class="
                        w-5
                        h-5
                        animate-spin
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-width="2"
                        opacity="0.25"
                    />

                    <path
                        d="M21 12a9 9 0 0 0-9-9"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>
            </span>

        </button>


        {{-- ============================================================
            REGISTRO
        ============================================================ --}}
        <div class="text-center pt-1">

            <a
                href="{{ route('register') }}"
                class="
                    text-xs
                    text-[var(--theme-primary)]

                    hover:text-[var(--theme-primary-hover)]
                    hover:underline

                    transition-colors
                "
            >
                No tengo una cuenta
            </a>

        </div>

    </form>

</x-auth-card>


<script>

    /*
    |--------------------------------------------------------------------------
    | Mostrar / ocultar contraseña
    |--------------------------------------------------------------------------
    */

    function togglePassword(fieldId) {

        const field =
            document.getElementById(fieldId);

        field.type =
            field.type === 'password'
                ? 'text'
                : 'password';

    }


    /*
    |--------------------------------------------------------------------------
    | Estado de carga del login
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const form =
            document.getElementById('loginForm');

        const button =
            document.getElementById('loginButton');

        const normalState =
            document.getElementById('loginNormalState');

        const loadingState =
            document.getElementById('loginLoadingState');

        const loginInput =
            document.getElementById('loginInput');

        const passwordInput =
            document.getElementById('password');

        const togglePasswordButton =
            document.getElementById('togglePasswordButton');


        if (!form) {
            return;
        }


        form.addEventListener('submit', function () {

            /*
             * Evita múltiples clics.
             */

            button.disabled = true;


            /*
             * Oculta:
             *
             * INGRESAR →
             */

            normalState.classList.add('hidden');


            /*
             * Muestra solamente el spinner.
             */

            loadingState.classList.remove('hidden');
            loadingState.classList.add('flex');


            /*
             * Bloqueamos edición mientras Laravel procesa.
             *
             * Usamos readonly y NO disabled para que los
             * datos sigan enviándose en el POST.
             */

            loginInput.readOnly = true;
            passwordInput.readOnly = true;

            togglePasswordButton.disabled = true;


            /*
             * Accesibilidad.
             */

            button.setAttribute(
                'aria-busy',
                'true'
            );

        });

    });

</script>

@endsection