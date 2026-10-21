{{-- ============================================================
    SEGURIDAD Y ROLES › NUEVO USUARIO
    Mismo diseño que los formularios de alta (Nueva Alta de equipo).
============================================================ --}}
@php
    $label = 'mb-1.5 block text-xs text-[var(--theme-text-muted)]';
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]';
    $seccion = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    $error = 'mt-1 text-xs text-[var(--theme-danger)]';

    $asterisco = fn (string $clave) => $obligatorio($clave) ? ' *' : '';

    $listaDominios = collect($dominios)->map(fn ($d) => '@' . $d)->implode(', ');
@endphp

<div>
    <form wire:submit="save" class="space-y-5">

        {{-- Cuenta para un colaborador sin cuenta (desde Seguridad y roles) --}}
        @if ($colaboradorNombre !== '')
            <div class="flex items-start gap-3 rounded-xl border border-[var(--theme-primary)] bg-[var(--theme-primary-soft)] px-4 py-3 text-sm text-[var(--theme-text-strong)]">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1.5 1.5"/>
                    <path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1.5-1.5"/>
                </svg>

                <p>
                    Cuenta para <span class="font-semibold">{{ $colaboradorNombre }}</span>.
                    Al guardarla, los equipos asignados a este colaborador quedarán vinculados a la nueva cuenta.
                    Revisa nombres y apellidos antes de guardar.
                </p>
            </div>
        @endif

        {{-- ====================================================
            SIN DOMINIOS CONFIGURADOS
        ==================================================== --}}
        @if ($dominios === [])
            <div class="rounded-xl border border-[var(--theme-warning-border,var(--theme-border))] bg-[var(--theme-warning-soft)] px-4 py-3 text-sm text-[var(--theme-warning)]">
                <p class="font-medium">
                    Falta configurar el dominio de correo
                </p>

                <p class="mt-1 text-xs">
                    Los usuarios deben tener un correo de la organización. Agrega al menos un dominio en
                    <a
                        href="{{ route('catalogos.index', ['section' => 'organizacion']) }}"
                        wire:navigate.hover
                        class="font-medium underline underline-offset-2"
                    >Administración › Organización</a>
                    para poder crear cuentas.
                </p>
            </div>
        @endif


        {{-- ====================================================
            DATOS PERSONALES
        ==================================================== --}}
        <section class="{{ $seccion }}">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                Datos personales
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="user-names" class="{{ $label }}">Nombre(s) *</label>
                    <input id="user-names" type="text" wire:model="nombres" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('nombres') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-last-name" class="{{ $label }}">Apellido paterno *</label>
                    <input id="user-last-name" type="text" wire:model="apellidoPaterno" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('apellidoPaterno') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-second-last-name" class="{{ $label }}">Apellido materno{{ $asterisco('apellido_materno') }}</label>
                    <input id="user-second-last-name" type="text" wire:model="apellidoMaterno" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('apellidoMaterno') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-email" class="{{ $label }}">Correo electrónico *</label>
                    <input
                        id="user-email"
                        type="email"
                        wire:model="correo"
                        maxlength="255"
                        autocomplete="off"
                        placeholder="{{ $dominios !== [] ? 'nombre@' . $dominios[0] : '' }}"
                        class="{{ $campo }}"
                    >

                    @error('correo')
                        <p class="{{ $error }}">{{ $message }}</p>
                    @else
                        @if ($dominios !== [])
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                Debe terminar en {{ $listaDominios }}
                            </p>
                        @endif
                    @enderror
                </div>

                <div>
                    <label for="user-phone" class="{{ $label }}">Teléfono{{ $asterisco('telefono') }}</label>
                    <input id="user-phone" type="tel" wire:model="telefono" maxlength="30" autocomplete="off" class="{{ $campo }}">
                    @error('telefono') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>


        {{-- ====================================================
            ORGANIZACIÓN
        ==================================================== --}}
        <section class="{{ $seccion }}">
            <h2 class="mb-5 text-sm font-semibold text-[var(--theme-text-strong)]">
                Organización
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <x-searchable-select
                        wire-model="idUnidad"
                        :label="$etiquetas['unidad_singular'] . ' *'"
                        :placeholder="$etiquetas['buscar_unidad']"
                        :options="$unidades"
                        :show-clear="false"
                    />
                    @error('idUnidad') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div wire:key="user-department-{{ $idUnidad ?? 'none' }}">
                    <x-searchable-select
                        wire-model="idDepartamento"
                        label="Departamento *"
                        :placeholder="filled($idUnidad) ? 'Buscar departamento...' : 'Selecciona un ' . mb_strtolower($etiquetas['unidad_singular']) . ' primero'"
                        :options="$departamentos"
                        :disabled="blank($idUnidad)"
                        :show-clear="false"
                    />
                    @error('idDepartamento') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-position" class="{{ $label }}">Puesto{{ $asterisco('puesto') }}</label>
                    <input id="user-position" type="text" wire:model="puesto" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('puesto') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-employee-number" class="{{ $label }}">Número de colaborador{{ $asterisco('numero_colaborador') }}</label>
                    <input id="user-employee-number" type="text" wire:model="numeroColaborador" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('numeroColaborador') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>


        {{-- ====================================================
            ACCESO AL SISTEMA
        ==================================================== --}}
        <section
            class="{{ $seccion }}"
            x-data="{ verPassword: false }"
            x-on:usuario-password-generada.window="verPassword = true"
        >
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Acceso al sistema
            </h2>

            <p class="mt-1 mb-5 text-xs text-[var(--theme-text-muted)]">
                La cuenta se crea activa. Comparte la contraseña con el usuario para su primer inicio de sesión.
            </p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="user-username" class="{{ $label }}">Nombre de usuario *</label>
                    <input id="user-username" type="text" wire:model="username" maxlength="255" autocomplete="off" class="{{ $campo }}">
                    @error('username') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="user-password" class="{{ $label }}">Contraseña *</label>

                    <div class="relative">
                        <input
                            id="user-password"
                            x-bind:type="verPassword ? 'text' : 'password'"
                            type="password"
                            wire:model="password"
                            autocomplete="new-password"
                            class="{{ $campo }} pr-10"
                        >

                        <button
                            type="button"
                            x-on:click="verPassword = ! verPassword"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]"
                            x-bind:aria-label="verPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>

                    @error('password')
                        <p class="{{ $error }}">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Mínimo 8 caracteres.</p>
                    @enderror
                </div>

                <div>
                    <label for="user-password-confirmation" class="{{ $label }}">Confirmar contraseña *</label>

                    <div class="flex gap-2">
                        <input
                            id="user-password-confirmation"
                            x-bind:type="verPassword ? 'text' : 'password'"
                            type="password"
                            wire:model="passwordConfirmation"
                            autocomplete="new-password"
                            class="{{ $campo }}"
                        >

                        <button
                            type="button"
                            wire:click="generarPassword"
                            wire:loading.attr="disabled"
                            wire:target="generarPassword"
                            title="Generar contraseña segura"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 2l-2 2m-7.6 7.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                            </svg>
                            Generar
                        </button>
                    </div>

                    @error('passwordConfirmation') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>


        {{-- ====================================================
            ROL
        ==================================================== --}}
        <section class="{{ $seccion }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Rol *
            </h2>

            <p class="mt-1 mb-5 text-xs text-[var(--theme-text-muted)]">
                Define qué podrá ver y hacer el usuario dentro del sistema.
            </p>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($roles as $rol)
                    <label
                        wire:key="user-role-{{ $rol->id_rol }}"
                        @class([
                            'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors',
                            'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]' => (string) $idRol === (string) $rol->id_rol,
                            'border-[var(--theme-border)] hover:border-[var(--theme-primary-border)]' => (string) $idRol !== (string) $rol->id_rol,
                        ])
                    >
                        <input
                            type="radio"
                            wire:model.live="idRol"
                            value="{{ $rol->id_rol }}"
                            class="mt-0.5 border-[var(--theme-border-strong)] text-[var(--theme-primary)]"
                        >

                        <span class="min-w-0">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-medium text-[var(--theme-text-strong)]">
                                    {{ $rol->nombre }}
                                </span>

                                @php
                                    $nivelRol = match ($rol->nivel ?? null) {
                                        'admin' => 'Acceso total',
                                        'estandar' => 'Operación',
                                        'lectura' => 'Solo consulta',
                                        default => 'Solo consulta',
                                    };
                                @endphp

                                <span class="rounded-full bg-[var(--theme-surface-soft)] px-2 py-0.5 text-[10px] font-medium text-[var(--theme-text-muted)]">
                                    {{ $nivelRol }}
                                </span>
                            </span>

                            @if (filled($rol->descripcion))
                                <span class="mt-0.5 block text-xs text-[var(--theme-text-muted)]">
                                    {{ $rol->descripcion }}
                                </span>
                            @endif
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-[var(--theme-text-muted)]">
                        No hay roles activos configurados.
                    </p>
                @endforelse
            </div>

            @error('idRol') <p class="{{ $error }} mt-3">{{ $message }}</p> @enderror
        </section>


        {{-- ====================================================
            ACCIONES
        ==================================================== --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ route('usuarios.index') }}"
                wire:navigate.hover
                class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
            >
                Cancelar
            </a>

            <x-ui.button
                type="submit"
                target="save"
                size="lg"
                :disabled="$dominios === []"
                loading-text="Creando…"
                class="w-full sm:w-auto"
            >
                Crear usuario
            </x-ui.button>
        </div>

    </form>
</div>
