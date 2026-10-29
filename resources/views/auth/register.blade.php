@extends('layouts.guest')
@section('title', 'Crear cuenta')
@section('page-label', 'REGISTRO')
@section('content')
@php
    \Livewire\Livewire::forceAssetInjection();
    $visible = fn (string $campo): bool => ($campos[$campo] ?? 'opcional') !== 'oculto';
    $obligatorio = fn (string $campo): bool => ($campos[$campo] ?? 'opcional') === 'obligatorio';
    $sufijo = fn (string $campo): string => $obligatorio($campo) ? '' : ' (opcional)';
    $unidadSingular = mb_strtolower($etiquetas['unidad_singular']);
    $campoClase = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
    $errorClase = 'mt-1 text-xs text-[var(--theme-danger)]';
@endphp

{{-- Esta envoltura centra el registro sin modificar el layout del login. --}}
<div class="w-full" style="width:100%;display:flex;justify-content:center;">
    <x-auth-card heading="Crear cuenta" icon="user-plus" width="2xl">
        <form id="registerForm" method="POST" action="{{ route('register') }}" class="space-y-4"
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
                get unidadesDeSede() {
                    return this.unidades.filter(u => u.sede === String(this.sede ?? ''));
                },
                get departamentosDeUnidad() {
                    const unidad = String(this.unidad ?? '');
                    if (!unidad) return [];
                    return this.departamentos.filter(d => d.global || d.unidades.includes(unidad));
                },
                init() {
                    this.$watch('sede', () => {
                        if (!this.unidadesDeSede.some(u => u.value === String(this.unidad ?? ''))) this.unidad = '';
                    });
                    this.$watch('unidad', () => {
                        if (!this.departamentosDeUnidad.some(d => d.value === String(this.departamento ?? ''))) this.departamento = '';
                    });
                },
            }">
            @csrf

            {{-- INFORMACIÓN PERSONAL --}}
            <div class="pt-1 text-center">
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                    <span class="text-[10px] font-semibold tracking-wider text-[var(--theme-text-muted)] uppercase">Información personal</span>
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                </div>
                <p class="mt-2 text-[11px] text-[var(--theme-text-muted)]">Datos básicos de tu cuenta.</p>
            </div>

            <div>
                <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombre(s)"
                    autocomplete="given-name" autofocus required maxlength="255" data-solo-letras class="{{ $campoClase }}">
                @error('nombres') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}"
                        placeholder="Apellido paterno" autocomplete="family-name" maxlength="255" required
                        data-solo-letras class="{{ $campoClase }}">
                    @error('apellido_paterno') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
                @if ($visible('apellido_materno'))
                    <div>
                        <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                            placeholder="Apellido materno{{ $sufijo('apellido_materno') }}" autocomplete="additional-name"
                            maxlength="255" data-solo-letras @required($obligatorio('apellido_materno')) class="{{ $campoClase }}">
                        @error('apellido_materno') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
            @if ($visible('telefono'))
                <div>
                    <input type="tel" name="telefono" value="{{ old('telefono') }}"
                        placeholder="Teléfono{{ $sufijo('telefono') }}" autocomplete="tel" maxlength="30"
                        @required($obligatorio('telefono')) class="{{ $campoClase }}">
                    @error('telefono') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
            @endif
            {{-- Pregunta de seguridad si algún día olvida su contraseña --}}
            <div>
                <label for="fecha_nacimiento" class="mb-1 block text-[11px] text-[var(--theme-text-muted)]">
                    Fecha de nacimiento
                </label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                    autocomplete="bday" required min="1900-01-01" max="{{ now()->subDay()->toDateString() }}" class="{{ $campoClase }}">
                <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                    Se usa para verificar tu identidad si olvidas tu contraseña.
                </p>
                @error('fecha_nacimiento') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <input type="email" name="correo" value="{{ old('correo') }}" autocomplete="email" required
                        placeholder="{{ $dominios !== [] ? 'Correo corporativo' : 'Correo electrónico' }}" class="{{ $campoClase }}">
                    @if ($dominios !== [])
                        <p class="mt-1 text-[11px] text-[var(--theme-text-muted)]">
                            Dominios corporativos autorizados:
                            {{ collect($dominios)->map(fn ($dominio) => '@'.$dominio)->implode(', ') }}
                        </p>
                    @else
                        <p class="{{ $errorClase }}">No hay dominios corporativos configurados. Contacta al administrador.</p>
                    @endif
                    @error('correo') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Nombre de usuario"
                        autocomplete="username" required class="{{ $campoClase }}">
                    @error('username') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- INFORMACIÓN ORGANIZACIONAL --}}
            <div class="pt-2 text-center">
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                    <span class="text-[10px] font-semibold tracking-wider text-[var(--theme-text-muted)] uppercase">Información organizacional</span>
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                </div>
                <p class="mt-2 text-[11px] text-[var(--theme-text-muted)]">
                    Información relacionada con tu operación dentro de la organización.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if ($visible('numero_colaborador'))
                    <div>
                        <input type="text" name="numero_colaborador" value="{{ old('numero_colaborador') }}"
                            placeholder="Número de colaborador{{ $sufijo('numero_colaborador') }}" inputmode="numeric"
                            pattern="[0-9]+" maxlength="255" autocomplete="off" data-solo-numeros
                            @required($obligatorio('numero_colaborador')) class="{{ $campoClase }}">
                        @error('numero_colaborador') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                    </div>
                @endif
                @if ($visible('puesto'))
                    <div>
                        <input type="text" name="puesto" value="{{ old('puesto') }}"
                            placeholder="Puesto{{ $sufijo('puesto') }}" autocomplete="organization-title"
                            maxlength="255" data-solo-letras @required($obligatorio('puesto')) class="{{ $campoClase }}">
                        @error('puesto') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <x-searchable-select name="id_sede" x-model="sede"
                        :options="$sedes->map(fn ($sede) => ['value' => (string) $sede->id_sede, 'label' => $sede->nombre])->values()->all()"
                        placeholder="Selecciona tu sede" :show-clear="false" :empty-as-null="false" />
                    @error('id_sede') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <x-searchable-select name="id_unidad" x-model="unidad" x-options="unidadesDeSede" x-disabled="! sede"
                        :placeholder="'Selecciona tu '.$unidadSingular" :show-clear="false" :empty-as-null="false" />
                    @error('id_unidad') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <x-searchable-select name="id_departamento" x-model="departamento" x-options="departamentosDeUnidad"
                        x-disabled="! unidad" placeholder="Selecciona tu departamento" :show-clear="false" :empty-as-null="false" />
                    @error('id_departamento') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
                @if ($visible('id_rol'))
                    <div>
                        <x-searchable-select name="id_rol" x-model="rol"
                            :options="$roles->map(fn ($rol) => ['value' => (string) $rol->id_rol, 'label' => $rol->nombre])->values()->all()"
                            :placeholder="'Selecciona tu rol'.$sufijo('id_rol')" :show-clear="! $obligatorio('id_rol')"
                            clear-label="Sin rol" :empty-as-null="false" />
                        @error('id_rol') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            {{-- SEGURIDAD --}}
            <div class="pt-2 text-center">
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                    <span class="text-[10px] font-semibold tracking-wider text-[var(--theme-text-muted)] uppercase">Seguridad</span>
                    <div class="h-px flex-1 bg-[var(--theme-border)]"></div>
                </div>
                <p class="mt-2 text-[11px] text-[var(--theme-text-muted)]">
                    Mínimo 10 caracteres, incluyendo mayúscula, minúscula, número y un símbolo especial; sin espacios.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <div class="relative">
                        <input id="password" type="password" name="password" placeholder="Contraseña"
                            autocomplete="new-password" required minlength="10" class="{{ $campoClase }} pr-10">
                        <button type="button" data-password-toggle onclick="togglePassword('password', this)" aria-pressed="false"
                            class="absolute inset-y-0 right-3 flex items-center text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] transition-colors"
                            aria-label="Mostrar u ocultar contraseña">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="2.5"/><line class="ojo-tachado" pathLength="1" x1="3" y1="21" x2="21" y2="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            placeholder="Confirmar contraseña" autocomplete="new-password" required minlength="10"
                            class="{{ $campoClase }} pr-10">
                        <button type="button" data-password-toggle onclick="togglePassword('password_confirmation', this)" aria-pressed="false"
                            class="absolute inset-y-0 right-3 flex items-center text-[var(--theme-text-muted)] hover:text-[var(--theme-text)] transition-colors"
                            aria-label="Mostrar u ocultar confirmación de contraseña">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="2.5"/><line class="ojo-tachado" pathLength="1" x1="3" y1="21" x2="21" y2="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation') <p class="{{ $errorClase }}">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-xs text-[var(--theme-text-muted)] leading-relaxed">
                Tras crear tu cuenta, un administrador deberá aprobarla antes de que puedas iniciar sesión.
            </p>
            <button id="registerButton" type="submit"
                class="relative w-full min-h-[44px] flex items-center justify-center bg-[var(--theme-primary)] hover:bg-[var(--theme-primary-hover)] text-white font-medium tracking-wide text-sm rounded-md py-3 transition-colors disabled:opacity-80 disabled:cursor-wait">
                <span id="registerNormalState" class="flex items-center justify-center gap-2">
                    CREAR CUENTA
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="{{ \App\Support\Icono::url('i-ff7bd3b') }}"></use></svg>
                </span>
                <span id="registerLoadingState" class="hidden absolute inset-0 items-center gap-2 justify-center">
                    <x-ui.spinner size="sm" /><span>Creando cuenta…</span>
                </span>
            </button>
            <div class="text-center pt-1">
                <a href="{{ route('login') }}" class="text-xs text-[var(--theme-primary)] hover:text-[var(--theme-primary-hover)] hover:underline transition-colors">
                    Ya tengo una cuenta
                </a>
            </div>
        </form>
    </x-auth-card>
</div>
<script>
    function togglePassword(fieldId, boton = null) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const visible = field.type === 'password';
        field.type = visible ? 'text' : 'password';

        /* La línea del ojo se borra o se dibuja (ui-styles: .ojo-tachado). */
        if (boton) {
            boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
            boton.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('registerForm');
        if (!form) return;
        const limpiarLetras = (valor) => valor.replace(/[^\p{L}\p{M} ]/gu, '').replace(/ {2,}/g, ' ');
        const limpiarNumeros = (valor) => valor.replace(/[^0-9]/g, '');
        const limitarCampo = (field, limpiar) => {
            field.addEventListener('input', function () {
                const original = field.value;
                const cursor = field.selectionStart ?? original.length;
                const normalizado = limpiar(original);
                if (normalizado !== original) {
                    const nuevoCursor = limpiar(original.slice(0, cursor)).length;
                    field.value = normalizado;
                    field.setSelectionRange(nuevoCursor, nuevoCursor);
                }
            });
        };
        form.querySelectorAll('[data-solo-letras]').forEach((field) => {
            limitarCampo(field, limpiarLetras);
            field.addEventListener('blur', () => { field.value = field.value.trim().replace(/ {2,}/g, ' '); });
        });
        form.querySelectorAll('[data-solo-numeros]').forEach((field) => limitarCampo(field, limpiarNumeros));

        const dominios = @js($dominios);
        const correo = form.querySelector('[name="correo"]');
        if (correo) {
            const validarCorreo = () => {
                if (!correo.value.trim()) { correo.setCustomValidity(''); return; }
                const formato = /^[A-Za-z0-9]+(?:[._%+\-][A-Za-z0-9]+)*@[A-Za-z0-9](?:[A-Za-z0-9.\-]*[A-Za-z0-9])?\.[A-Za-z]{2,63}$/;
                const partes = correo.value.trim().split('@');
                const dominio = partes.length === 2 ? partes[1].toLowerCase() : '';
                if (!dominios.length) {
                    correo.setCustomValidity('No hay dominios corporativos configurados. Contacta al administrador.');
                } else if (!formato.test(correo.value.trim())) {
                    correo.setCustomValidity('Escribe un correo válido, sin emojis ni espacios. Se permiten puntos en el nombre.');
                } else if (!dominios.includes(dominio)) {
                    correo.setCustomValidity('El dominio no está autorizado. Utiliza: ' + dominios.map(d => '@' + d).join(', '));
                } else {
                    correo.setCustomValidity('');
                }
            };
            correo.addEventListener('input', validarCorreo);
            correo.addEventListener('blur', validarCorreo);
            validarCorreo();
        }
        const password = form.querySelector('[name="password"]');
        const confirmacion = form.querySelector('[name="password_confirmation"]');
        const formatoContrasena = /^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*[0-9])(?=.*[^\p{L}\p{N}\s])\S{10,}$/u;
        const validarPassword = () => {
            if (password) password.setCustomValidity(
                password.value && !formatoContrasena.test(password.value)
                    ? 'Usa al menos 10 caracteres, con mayúscula, minúscula, número y un símbolo especial, sin espacios.' : ''
            );
            if (confirmacion) confirmacion.setCustomValidity(
                confirmacion.value && confirmacion.value !== (password?.value ?? '') ? 'Las contraseñas no coinciden.' : ''
            );
        };
        password?.addEventListener('input', validarPassword);
        confirmacion?.addEventListener('input', validarPassword);

        const button = document.getElementById('registerButton');
        const normalState = document.getElementById('registerNormalState');
        const loadingState = document.getElementById('registerLoadingState');
        if (button && normalState && loadingState) {
            form.addEventListener('submit', function () {
                button.disabled = true;
                normalState.classList.add('invisible');
                loadingState.classList.remove('hidden');
                loadingState.classList.add('flex');
                form.querySelectorAll('input, select').forEach(function (field) {
                    if (field.type !== 'hidden' && field.type !== 'submit') field.style.pointerEvents = 'none';
                });
                form.querySelectorAll('[data-password-toggle]').forEach(toggle => { toggle.disabled = true; });
                button.setAttribute('aria-busy', 'true');
            });
        }
    });
</script>
@endsection
