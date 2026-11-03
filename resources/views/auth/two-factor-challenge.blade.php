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
                for="codeBox0"
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


            {{-- Valor que se envía (lo llenan las 6 casillas) --}}
            <input
                id="code"
                type="hidden"
                name="code"
                value="{{ old('code') }}"
                @if ($initialMode === 'recovery') disabled @endif
            >

            {{--
                Una casilla por dígito. Al pegar o al autocompletar el código
                (SMS / gestor de contraseñas) se reparte en todas; al llenar
                la sexta se envía solo.
            --}}
            <div
                id="codeBoxes"
                class="flex justify-between gap-2 sm:gap-3"
                role="group"
                aria-label="Código de verificación de 6 dígitos"
            >
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="{{ $i === 0 ? 6 : 1 }}"
                        @if ($i === 0) id="codeBox0" autocomplete="one-time-code" @else autocomplete="off" @endif
                        @if ($i === 0 && $initialMode !== 'recovery') autofocus @endif
                        data-code-box
                        data-1p-ignore
                        data-lpignore="true"
                        data-bwignore
                        data-form-type="other"
                        aria-label="Dígito {{ $i + 1 }} de 6"
                        value="{{ substr((string) old('code'), $i, 1) }}"
                        class="
                            h-14
                            w-full
                            min-w-0
                            max-w-[52px]

                            rounded-md
                            border
                            border-[var(--theme-border-strong)]
                            bg-[var(--theme-surface)]

                            text-center
                            text-xl
                            font-semibold
                            tabular-nums
                            text-[var(--theme-text-strong)]

                            caret-[var(--theme-primary)]

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
                @endfor
            </div>


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
                ><use href="{{ \App\Support\Icono::url('i-ff7bd3b') }}"></use></svg>
            </span>


            {{-- Estado cargando --}}
            <span
                id="twoFactorLoadingState"

                class="
                    hidden
                    items-center
                    gap-2
                    justify-center
                "
            >
                <x-ui.spinner size="sm" />
                <span>Verificando…</span>
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
            | Casillas del código (una por dígito)
            |--------------------------------------------------------------------------
            */

            const cajas = Array.from(
                document.querySelectorAll('[data-code-box]')
            );

            let enviando = false;

            if (modeInput.value === 'recovery') {
                cajas.forEach((caja) => { caja.disabled = true; });
            }

            function sincronizarCodigo() {
                codeInput.value = cajas.map((caja) => caja.value).join('');

                if (/^\d{6}$/.test(codeInput.value) && modeInput.value === 'authenticator' && !enviando) {
                    enviando = true;
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            }

            // Reparte varios dígitos desde la casilla indicada.
            function repartir(desde, texto) {
                const digitos = String(texto).replace(/\D/g, '').slice(0, 6 - desde).split('');

                digitos.forEach((digito, n) => {
                    cajas[desde + n].value = digito;
                });

                const siguiente = Math.min(desde + digitos.length, cajas.length - 1);
                cajas[siguiente].focus();
                cajas[siguiente].select();

                sincronizarCodigo();
            }

            cajas.forEach((caja, indice) => {
                caja.addEventListener('focus', () => caja.select());

                caja.addEventListener('input', () => {
                    const limpio = caja.value.replace(/\D/g, '');

                    // Autocompletado (SMS o gestor) o varios dígitos a la vez.
                    if (limpio.length > 1) {
                        repartir(indice, limpio);

                        return;
                    }

                    caja.value = limpio;

                    if (limpio !== '' && indice < cajas.length - 1) {
                        cajas[indice + 1].focus();
                    }

                    sincronizarCodigo();
                });

                caja.addEventListener('paste', (evento) => {
                    evento.preventDefault();
                    repartir(indice, (evento.clipboardData || window.clipboardData).getData('text'));
                });

                caja.addEventListener('keydown', (evento) => {
                    if (evento.key === 'Backspace' && caja.value === '' && indice > 0) {
                        evento.preventDefault();
                        cajas[indice - 1].value = '';
                        cajas[indice - 1].focus();
                        sincronizarCodigo();
                    } else if (evento.key === 'ArrowLeft' && indice > 0) {
                        evento.preventDefault();
                        cajas[indice - 1].focus();
                    } else if (evento.key === 'ArrowRight' && indice < cajas.length - 1) {
                        evento.preventDefault();
                        cajas[indice + 1].focus();
                    }
                });
            });

            // Si hubo error, se borra el código para escribirlo de nuevo.
            @error('code')
                cajas.forEach((caja) => { caja.value = ''; });
                codeInput.value = '';
                cajas[0]?.focus();
            @enderror


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

                cajas.forEach((caja) => { caja.disabled = false; });

                cajas[0]?.focus();
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

                cajas.forEach((caja) => { caja.value = ''; caja.disabled = true; });


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