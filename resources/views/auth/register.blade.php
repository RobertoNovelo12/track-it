@extends('layouts.guest')

@section('title', 'Crear cuenta')
@section('page-label', 'REGISTRO')

@section('content')

<x-auth-card
    heading="Crear cuenta"
    icon="user-plus"
    width="2xl"
>

    <form
        id="registerForm"
        method="POST"
        action="{{ route('register') }}"
        class="space-y-4"
    >

        @csrf


        {{-- ============================================================
            INFORMACIÓN PERSONAL
        ============================================================ --}}
        <div class="pt-1">

            <div class="flex items-center gap-3">

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

                <span
                    class="
                        text-[10px]
                        font-semibold
                        tracking-wider
                        text-[var(--theme-text-muted)]
                        uppercase
                    "
                >
                    Información personal
                </span>

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

            </div>


            <p
                class="
                    mt-2
                    text-center
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Datos básicos de tu cuenta.
            </p>

        </div>


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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

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
            CORREO / USUARIO
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Correo --}}
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


            {{-- Nombre de usuario --}}
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

        </div>


        {{-- ============================================================
            INFORMACIÓN ORGANIZACIONAL
        ============================================================ --}}
        <div class="pt-2">

            <div class="flex items-center gap-3">

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

                <span
                    class="
                        text-[10px]
                        font-semibold
                        tracking-wider
                        text-[var(--theme-text-muted)]
                        uppercase
                    "
                >
                    Información organizacional
                </span>

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

            </div>


            <p
                class="
                    mt-2
                    text-center
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Información relacionada con tu operación dentro de la organización.
            </p>

        </div>


        {{-- ============================================================
            NÚMERO COLABORADOR / PUESTO
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Número de colaborador --}}
            <div>

                <input
                    type="text"
                    name="numero_colaborador"
                    value="{{ old('numero_colaborador') }}"
                    placeholder="Número de colaborador"
                    autocomplete="off"
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

                @error('numero_colaborador')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Puesto --}}
            <div>

                <input
                    type="text"
                    name="puesto"
                    value="{{ old('puesto') }}"
                    placeholder="Puesto"
                    autocomplete="organization-title"
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

                @error('puesto')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            SEDE / ÁREA
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Sede --}}
            <div>

                <select
                    id="registerSede"
                    name="id_sede"
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

                        focus:outline-none
                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]
                        focus:border-[var(--theme-primary)]

                        transition-colors
                    "
                >
                    <option value="">
                        Selecciona tu sede
                    </option>

                    @foreach ($sedes as $sede)

                        <option
                            value="{{ $sede->id_sede }}"
                            @selected(old('id_sede') == $sede->id_sede)
                        >
                            {{ $sede->nombre }}
                        </option>

                    @endforeach

                </select>

                @error('id_sede')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Área --}}
            <div>

                <select
                    id="registerArea"
                    name="id_area"
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

                        focus:outline-none
                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]
                        focus:border-[var(--theme-primary)]

                        disabled:opacity-50
                        disabled:cursor-not-allowed
                        disabled:bg-[var(--theme-surface-soft)]

                        transition-colors
                    "
                >
                    <option value="">
                        Selecciona tu área
                    </option>

                    @foreach ($areas as $area)

                        <option
                            value="{{ $area->id_area }}"
                            data-sede="{{ $area->id_sede }}"
                            @selected(old('id_area') == $area->id_area)
                        >
                            {{ $area->nombre }}
                        </option>

                    @endforeach

                </select>

                @error('id_area')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            DEPARTAMENTO / ROL
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Departamento --}}
            <div>

                <select
                    id="registerDepartamento"
                    name="id_departamento"
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

                        focus:outline-none
                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]
                        focus:border-[var(--theme-primary)]

                        disabled:opacity-50
                        disabled:cursor-not-allowed
                        disabled:bg-[var(--theme-surface-soft)]

                        transition-colors
                    "
                >
                    <option value="">
                        Selecciona tu departamento
                    </option>

                    @foreach ($departamentos as $departamento)

                        <option
                            value="{{ $departamento->id_departamento }}"
                            data-area="{{ $departamento->id_area }}"
                            @selected(old('id_departamento') == $departamento->id_departamento)
                        >
                            {{ $departamento->nombre }}
                        </option>

                    @endforeach

                </select>

                @error('id_departamento')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Rol --}}
            <div>

                <select
                    name="id_rol"
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

                        focus:outline-none
                        focus:ring-2
                        focus:ring-[var(--theme-primary-soft)]
                        focus:border-[var(--theme-primary)]

                        transition-colors
                    "
                >
                    <option value="">
                        Selecciona tu rol
                    </option>

                    @foreach ($roles as $rol)

                        <option
                            value="{{ $rol->id_rol }}"
                            @selected(old('id_rol') == $rol->id_rol)
                        >
                            {{ $rol->nombre }}
                        </option>

                    @endforeach

                </select>

                @error('id_rol')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>

        </div>


        {{-- ============================================================
            SEGURIDAD
        ============================================================ --}}
        <div class="pt-2">

            <div class="flex items-center gap-3">

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

                <span
                    class="
                        text-[10px]
                        font-semibold
                        tracking-wider
                        text-[var(--theme-text-muted)]
                        uppercase
                    "
                >
                    Seguridad
                </span>

                <div class="h-px flex-1 bg-[var(--theme-border)]"></div>

            </div>


            <p
                class="
                    mt-2
                    text-center
                    text-[11px]
                    text-[var(--theme-text-muted)]
                "
            >
                Define la contraseña con la que accederás a tu cuenta.
            </p>

        </div>


        {{-- ============================================================
            CONTRASEÑA / CONFIRMAR CONTRASEÑA
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Contraseña --}}
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
                        data-password-toggle
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
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Confirmar contraseña --}}
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
                        data-password-toggle
                        onclick="togglePassword('password_confirmation')"
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
            id="registerButton"
            type="submit"
            class="
                relative

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
                id="registerNormalState"
                class="
                    flex
                    items-center
                    justify-center
                    gap-2
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
            </span>


            {{-- Estado cargando --}}
            <span
                id="registerLoadingState"
                class="
                    hidden
                    absolute
                    inset-0

                    items-center
                    justify-center
                "
            >
                <svg
                    class="w-5 h-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
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

                <span class="sr-only">
                    Creando cuenta...
                </span>
            </span>

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

    /*
    |--------------------------------------------------------------------------
    | Mostrar / ocultar contraseña
    |--------------------------------------------------------------------------
    */

    function togglePassword(fieldId) {

        const field =
            document.getElementById(fieldId);

        if (!field) {
            return;
        }

        field.type =
            field.type === 'password'
                ? 'text'
                : 'password';

    }


    /*
    |--------------------------------------------------------------------------
    | Sede -> Área -> Departamento
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const sede =
            document.getElementById('registerSede');

        const area =
            document.getElementById('registerArea');

        const departamento =
            document.getElementById('registerDepartamento');


        if (!sede || !area || !departamento) {
            return;
        }


        function actualizarAreas() {

            const sedeSeleccionada =
                sede.value;

            const areaSeleccionada =
                area.value;


            Array.from(area.options).forEach(function (option, index) {

                if (index === 0) {
                    return;
                }

                const corresponde =
                    option.dataset.sede === sedeSeleccionada;

                option.hidden =
                    !corresponde;

                option.disabled =
                    !corresponde;

            });


            const opcionActual =
                area.options[area.selectedIndex];


            if (
                opcionActual &&
                opcionActual.value !== '' &&
                opcionActual.disabled
            ) {
                area.value = '';
            }


            area.disabled =
                sedeSeleccionada === '';


            if (
                areaSeleccionada &&
                Array.from(area.options).some(
                    option =>
                        option.value === areaSeleccionada &&
                        !option.disabled
                )
            ) {
                area.value =
                    areaSeleccionada;
            }


            actualizarDepartamentos();

        }


        function actualizarDepartamentos() {

            const areaSeleccionada =
                area.value;

            const departamentoSeleccionado =
                departamento.value;


            Array.from(departamento.options).forEach(
                function (option, index) {

                    if (index === 0) {
                        return;
                    }

                    const corresponde =
                        option.dataset.area === areaSeleccionada;

                    option.hidden =
                        !corresponde;

                    option.disabled =
                        !corresponde;

                }
            );


            const opcionActual =
                departamento.options[
                    departamento.selectedIndex
                ];


            if (
                opcionActual &&
                opcionActual.value !== '' &&
                opcionActual.disabled
            ) {
                departamento.value = '';
            }


            departamento.disabled =
                areaSeleccionada === '';


            if (
                departamentoSeleccionado &&
                Array.from(departamento.options).some(
                    option =>
                        option.value === departamentoSeleccionado &&
                        !option.disabled
                )
            ) {
                departamento.value =
                    departamentoSeleccionado;
            }

        }


        sede.addEventListener(
            'change',
            actualizarAreas
        );


        area.addEventListener(
            'change',
            actualizarDepartamentos
        );


        actualizarAreas();

    });


    /*
    |--------------------------------------------------------------------------
    | Estado de carga del registro
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const form =
            document.getElementById('registerForm');

        const button =
            document.getElementById('registerButton');

        const normalState =
            document.getElementById('registerNormalState');

        const loadingState =
            document.getElementById('registerLoadingState');


        if (
            !form ||
            !button ||
            !normalState ||
            !loadingState
        ) {
            return;
        }


        form.addEventListener('submit', function () {

            button.disabled = true;

            normalState.classList.add('invisible');

            loadingState.classList.remove('hidden');
            loadingState.classList.add('flex');


            /*
             * Evitamos cambios mientras Laravel procesa.
             *
             * Los inputs no se deshabilitan para que sus valores
             * continúen formando parte del POST.
             */

            form
                .querySelectorAll('input, select')
                .forEach(function (field) {

                    if (
                        field.type !== 'hidden' &&
                        field.type !== 'submit'
                    ) {
                        field.style.pointerEvents = 'none';
                    }

                });


            form
                .querySelectorAll('[data-password-toggle]')
                .forEach(function (toggle) {
                    toggle.disabled = true;
                });


            button.setAttribute(
                'aria-busy',
                'true'
            );

        });

    });

</script>

@endsection