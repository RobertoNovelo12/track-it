@extends('layouts.guest')

@section('title', 'Verificación en dos pasos')
@section('page-label', '2FA')

@section('content')

@php
    $initialMode = old('mode', 'authenticator');
@endphp


<x-auth-card
    heading="Verificación en dos pasos"
    icon="user"
>
    {{-- ============================================================
        DESCRIPCIÓN
    ============================================================ --}}
    <div class="mb-5">

        <p
            id="authenticatorDescription"
            class="
                text-sm
                leading-relaxed
                text-[var(--theme-text-muted)]

                {{ $initialMode === 'recovery' ? 'hidden' : '' }}
            "
        >
            Abre tu aplicación autenticadora e ingresa el código
            de 6 dígitos para continuar.
        </p>


        <p
            id="recoveryDescription"
            class="
                text-sm
                leading-relaxed
                text-[var(--theme-text-muted)]

                {{ $initialMode === 'recovery' ? '' : 'hidden' }}
            "
        >
            Ingresa uno de los códigos de recuperación que guardaste
            al activar la autenticación en dos pasos.
        </p>

    </div>


    {{-- ============================================================
        FORMULARIO
    ============================================================ --}}
    <form
        id="twoFactorForm"
        method="POST"
        action="{{ route('two-factor.verify') }}"
        class="space-y-4"
    >
        @csrf


        <input
            id="twoFactorMode"
            type="hidden"
            name="mode"
            value="{{ $initialMode }}"
        >


        {{-- ========================================================
            MÉTODO: APLICACIÓN AUTENTICADORA
        ======================================================== --}}
        <div
            id="authenticatorSection"
            class="{{ $initialMode === 'recovery' ? 'hidden' : '' }}"
        >
            <label
                for="code"
                class="
                    block
                    mb-1.5

                    text-xs
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                Código de verificación
            </label>


            <input
                id="code"
                type="text"
                name="code"

                value="{{ old('code') }}"

                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"

                autocomplete="one-time-code"

                placeholder="000000"

                @if ($initialMode === 'recovery')
                    disabled
                @else
                    autofocus
                @endif

                class="
                    w-full

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    px-4
                    py-3

                    text-center
                    text-lg
                    font-medium
                    tracking-[0.35em]
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    focus:outline-none
                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]
                    focus:border-[var(--theme-primary)]

                    transition-colors

                    @error('code')
                        border-[var(--theme-danger)]
                    @enderror
                "
            >


            @error('code')

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


        {{-- ========================================================
            MÉTODO: CÓDIGO DE RECUPERACIÓN
        ======================================================== --}}
        <div
            id="recoverySection"
            class="{{ $initialMode === 'recovery' ? '' : 'hidden' }}"
        >
            <label
                for="recovery_code"
                class="
                    block
                    mb-1.5

                    text-xs
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                Código de recuperación
            </label>


            <input
                id="recovery_code"
                type="text"
                name="recovery_code"

                value="{{ old('recovery_code') }}"

                autocomplete="off"

                placeholder="XXXXX-XXXXX"

                @if ($initialMode !== 'recovery')
                    disabled
                @endif

                class="
                    w-full

                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border-strong)]

                    rounded-md

                    px-4
                    py-3

                    text-center
                    text-sm
                    font-mono
                    font-medium
                    tracking-wider
                    uppercase
                    text-[var(--theme-text)]

                    placeholder:text-[var(--theme-text-muted)]

                    focus:outline-none
                    focus:ring-2
                    focus:ring-[var(--theme-primary-soft)]
                    focus:border-[var(--theme-primary)]

                    transition-colors

                    @error('recovery_code')
                        border-[var(--theme-danger)]
                    @enderror
                "
            >


            @error('recovery_code')

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


        {{-- ========================================================
            BOTÓN VERIFICAR
        ======================================================== --}}
        <button
            id="twoFactorButton"
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
                id="twoFactorNormalState"

                class="
                    flex
                    items-center
                    justify-center
                    gap-2
                "
            >
                VERIFICAR

                <svg
                    class="w-4 h-4"

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <line
                        x1="5"
                        y1="12"
                        x2="19"
                        y2="12"
                    />

                    <polyline
                        points="12 5 19 12 12 19"
                    />
                </svg>
            </span>


            {{-- Estado cargando --}}
            <span
                id="twoFactorLoadingState"

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


        {{-- ========================================================
            CAMBIAR MÉTODO
        ======================================================== --}}
        <div
            class="
                pt-1

                text-center
            "
        >
            <button
                id="useRecoveryButton"
                type="button"

                class="
                    text-xs
                    text-[var(--theme-primary)]

                    hover:text-[var(--theme-primary-hover)]
                    hover:underline

                    transition-colors

                    {{ $initialMode === 'recovery' ? 'hidden' : '' }}
                "
            >
                ¿No tienes acceso a tu aplicación?
                Usar un código de recuperación
            </button>


            <button
                id="useAuthenticatorButton"
                type="button"

                class="
                    text-xs
                    text-[var(--theme-primary)]

                    hover:text-[var(--theme-primary-hover)]
                    hover:underline

                    transition-colors

                    {{ $initialMode === 'recovery' ? '' : 'hidden' }}
                "
            >
                Volver a usar mi aplicación autenticadora
            </button>
        </div>


        {{-- ========================================================
            VOLVER AL LOGIN
        ======================================================== --}}
        <div
            class="
                text-center
                pt-2
            "
        >
            <a
                href="{{ route('login') }}"

                class="
                    text-xs
                    text-[var(--theme-text-muted)]

                    hover:text-[var(--theme-text)]
                    hover:underline

                    transition-colors
                "
            >
                Volver al inicio de sesión
            </a>
        </div>

    </form>
</x-auth-card>


<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |--------------------------------------------------------------------------
            | Elementos
            |--------------------------------------------------------------------------
            */

            const form =
                document.getElementById(
                    'twoFactorForm'
                );

            const modeInput =
                document.getElementById(
                    'twoFactorMode'
                );

            const authenticatorDescription =
                document.getElementById(
                    'authenticatorDescription'
                );

            const recoveryDescription =
                document.getElementById(
                    'recoveryDescription'
                );

            const authenticatorSection =
                document.getElementById(
                    'authenticatorSection'
                );

            const recoverySection =
                document.getElementById(
                    'recoverySection'
                );

            const codeInput =
                document.getElementById(
                    'code'
                );

            const recoveryInput =
                document.getElementById(
                    'recovery_code'
                );

            const useRecoveryButton =
                document.getElementById(
                    'useRecoveryButton'
                );

            const useAuthenticatorButton =
                document.getElementById(
                    'useAuthenticatorButton'
                );

            const submitButton =
                document.getElementById(
                    'twoFactorButton'
                );

            const normalState =
                document.getElementById(
                    'twoFactorNormalState'
                );

            const loadingState =
                document.getElementById(
                    'twoFactorLoadingState'
                );


            if (!form) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Aplicación autenticadora
            |--------------------------------------------------------------------------
            */

            function showAuthenticator() {

                modeInput.value =
                    'authenticator';


                authenticatorDescription
                    .classList
                    .remove('hidden');

                recoveryDescription
                    .classList
                    .add('hidden');


                authenticatorSection
                    .classList
                    .remove('hidden');

                recoverySection
                    .classList
                    .add('hidden');


                useRecoveryButton
                    .classList
                    .remove('hidden');

                useAuthenticatorButton
                    .classList
                    .add('hidden');


                codeInput.disabled = false;

                recoveryInput.disabled = true;


                recoveryInput.value = '';


                codeInput.focus();
            }


            /*
            |--------------------------------------------------------------------------
            | Código de recuperación
            |--------------------------------------------------------------------------
            */

            function showRecovery() {

                modeInput.value =
                    'recovery';


                authenticatorDescription
                    .classList
                    .add('hidden');

                recoveryDescription
                    .classList
                    .remove('hidden');


                authenticatorSection
                    .classList
                    .add('hidden');

                recoverySection
                    .classList
                    .remove('hidden');


                useRecoveryButton
                    .classList
                    .add('hidden');

                useAuthenticatorButton
                    .classList
                    .remove('hidden');


                codeInput.disabled = true;

                recoveryInput.disabled = false;


                codeInput.value = '';


                recoveryInput.focus();
            }


            /*
            |--------------------------------------------------------------------------
            | Eventos
            |--------------------------------------------------------------------------
            */

            useRecoveryButton
                .addEventListener(
                    'click',
                    showRecovery
                );


            useAuthenticatorButton
                .addEventListener(
                    'click',
                    showAuthenticator
                );


            /*
            |--------------------------------------------------------------------------
            | Estado de carga
            |--------------------------------------------------------------------------
            */

            form.addEventListener(
                'submit',
                function () {

                    submitButton.disabled = true;


                    normalState
                        .classList
                        .add('hidden');


                    loadingState
                        .classList
                        .remove('hidden');

                    loadingState
                        .classList
                        .add('flex');


                    submitButton.setAttribute(
                        'aria-busy',
                        'true'
                    );
                }
            );

        }
    );
</script>

@endsection