@extends('layouts.guest')

@section('title', 'Crear cuenta')
@section('page-label', 'REGISTRO')

@section('content')

{{--
    Las listas usan <x-searchable-select> (Alpine). Esta página no tiene
    componentes Livewire, por eso se fuerza la carga de sus scripts.
--}}
@php
    \Livewire\Livewire::forceAssetInjection();
@endphp

@php
    /*
     * Campos configurables en Administración › Organización:
     * obligatorio | opcional | oculto
     */
    $visible = fn (string $campo): bool => ($campos[$campo] ?? 'opcional') !== 'oculto';
    $obligatorio = fn (string $campo): bool => ($campos[$campo] ?? 'opcional') === 'obligatorio';
    $sufijo = fn (string $campo): string => $obligatorio($campo) ? '' : ' (opcional)';

    $unidadSingular = mb_strtolower($etiquetas['unidad_singular']);
@endphp

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
        x-data="{
            sede: @js((string) old('id_sede', '')),
            unidad: @js((string) old('id_unidad', '')),
            departamento: @js((string) old('id_departamento', '')),
            rol: @js((string) old('id_rol', '')),

            unidades: @js($unidades->map(fn ($u) => [
                'value' => (string) $u->id_unidad,
                'label' => $u->nombre,
                'sede' => (string) $u->id_sede,
            ])->values()),

            departamentos: @js($departamentos->map(fn ($d) => [
                'value' => (string) $d->id_departamento,
                'label' => $d->nombre,
                'global' => (bool) $d->global,
                'unidades' => array_map('strval', $d->unidades),
            ])->values()),

            /* Sede -> Hotel -> Departamento (cobertura del departamento) */
            get unidadesDeSede() {
                return this.unidades.filter(u => u.sede === String(this.sede ?? ''));
            },

            get departamentosDeUnidad() {
                const unidad = String(this.unidad ?? '');

                if (unidad === '') {
                    return [];
                }

                return this.departamentos.filter(d => d.global || d.unidades.includes(unidad));
            },

            init() {
                this.$watch('sede', () => {
                    if (! this.unidadesDeSede.some(u => u.value === String(this.unidad ?? ''))) {
                        this.unidad = '';
                    }
                });

                this.$watch('unidad', () => {
                    if (! this.departamentosDeUnidad.some(d => d.value === String(this.departamento ?? ''))) {
                        this.departamento = '';
                    }
                });
            },
        }"
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


            @if ($visible('apellido_materno'))
            {{-- Apellido materno --}}
            <div>

                <input
                    type="text"
                    name="apellido_materno"
                    value="{{ old('apellido_materno') }}"
                    placeholder="Apellido materno{{ $sufijo('apellido_materno') }}"
                    @required($obligatorio('apellido_materno'))
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
            @endif

        </div>


        {{-- ============================================================
            TELÉFONO
        ============================================================ --}}
        @if ($visible('telefono'))
            <div>

                <input
                    type="tel"
                    name="telefono"
                    value="{{ old('telefono') }}"
                    placeholder="Teléfono{{ $sufijo('telefono') }}"
                    autocomplete="tel"
                    maxlength="30"
                    @required($obligatorio('telefono'))
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

                @error('telefono')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>
        @endif


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
                    placeholder="{{ $dominios !== [] ? 'Correo corporativo' : 'Correo electrónico' }}"
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

                @if ($dominios !== [])
                    <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                        Utiliza tu correo
                        {{ collect($dominios)->map(fn ($dominio) => '@'.$dominio)->implode(', ') }}
                    </p>
                @endif

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

            @if ($visible('numero_colaborador'))
            {{-- Número de colaborador --}}
            <div>

                <input
                    type="text"
                    name="numero_colaborador"
                    value="{{ old('numero_colaborador') }}"
                    placeholder="Número de colaborador{{ $sufijo('numero_colaborador') }}"
                    @required($obligatorio('numero_colaborador'))
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
            @endif


            @if ($visible('puesto'))
            {{-- Puesto --}}
            <div>

                <input
                    type="text"
                    name="puesto"
                    value="{{ old('puesto') }}"
                    placeholder="Puesto{{ $sufijo('puesto') }}"
                    @required($obligatorio('puesto'))
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
            @endif

        </div>


        {{-- ============================================================
            SEDE / HOTEL
        ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

            {{-- Sede --}}
            <div>

                <x-searchable-select
                    name="id_sede"
                    x-model="sede"
                    :options="$sedes->map(fn ($sede) => ['value' => (string) $sede->id_sede, 'label' => $sede->nombre])->values()->all()"
                    placeholder="Selecciona tu sede"
                    :show-clear="false"
                    :empty-as-null="false"
                />

                @error('id_sede')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Hotel (unidad organizativa) --}}
            <div>

                <x-searchable-select
                    name="id_unidad"
                    x-model="unidad"
                    x-options="unidadesDeSede"
                    x-disabled="! sede"
                    :placeholder="'Selecciona tu '.$unidadSingular"
                    :show-clear="false"
                    :empty-as-null="false"
                />

                @error('id_unidad')
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

                <x-searchable-select
                    name="id_departamento"
                    x-model="departamento"
                    x-options="departamentosDeUnidad"
                    x-disabled="! unidad"
                    placeholder="Selecciona tu departamento"
                    :show-clear="false"
                    :empty-as-null="false"
                />

                @error('id_departamento')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            @if ($visible('id_rol'))
            {{-- Rol --}}
            <div>

                <x-searchable-select
                    name="id_rol"
                    x-model="rol"
                    :options="$roles->map(fn ($rol) => ['value' => (string) $rol->id_rol, 'label' => $rol->nombre])->values()->all()"
                    :placeholder="'Selecciona tu rol'.$sufijo('id_rol')"
                    :show-clear="! $obligatorio('id_rol')"
                    clear-label="Sin rol"
                    :empty-as-null="false"
                />

                @error('id_rol')
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">
                        {{ $message }}
                    </p>
                @enderror

            </div>
            @endif

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