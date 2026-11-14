@extends('layouts.guest')

@section('title', 'Configurar instancia')
@section('page-label', 'CONFIGURACIÓN INICIAL')

@php
    $campoClase = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
    $titulo = 'text-xs font-semibold uppercase tracking-wide text-[var(--theme-text-strong)]';
@endphp

@section('content')

{{-- Usuario temporal tras restablecer (App\Http\Controllers\InstanciaController). --}}
<x-auth-card heading="Configura la instancia" icon="user-plus" width="xl">

    <p class="mb-6 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Crea el primer @plantilla('unidad') con un departamento y tu cuenta de administrador.
        Después podrás agregar más desde Administración.
    </p>

    <form
        method="POST"
        action="{{ route('instancia.configurar.guardar') }}"
        class="space-y-6"
        x-data="{ sede: @js((string) old('id_sede', $sedes->first()->id_sede ?? '')) }"
    >
        @csrf

        {{-- 1. Edificio --}}
        <fieldset class="space-y-3">
            <legend class="{{ $titulo }}">1. @plantilla('unidad_singular')</legend>

            <div>
                <label for="id_sede" class="mb-1 block text-xs text-[var(--theme-text-muted)]">Sede</label>
                <select id="id_sede" name="id_sede" x-model="sede" class="{{ $campoClase }}">
                    @foreach ($sedes as $s)
                        <option value="{{ $s->id_sede }}">{{ $s->nombre }}</option>
                    @endforeach
                    <option value="">Nueva sede…</option>
                </select>
                @error('id_sede') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>

            <div x-show="sede === ''" x-cloak>
                <input type="text" name="sede_nueva" value="{{ old('sede_nueva') }}" placeholder="Nombre de la nueva sede" maxlength="120" class="{{ $campoClase }}">
                @error('sede_nueva') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>

            <div>
                <input type="text" name="unidad" value="{{ old('unidad') }}" placeholder="Nombre del @plantilla('unidad')" maxlength="150" required class="{{ $campoClase }}">
                @error('unidad') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>
        </fieldset>

        {{-- 2. Departamento --}}
        <fieldset class="space-y-3">
            <legend class="{{ $titulo }}">2. Departamento</legend>

            <div>
                <input type="text" name="departamento" value="{{ old('departamento') }}" placeholder="Nombre del departamento (p. ej. Sistemas)" maxlength="150" required class="{{ $campoClase }}">
                @error('departamento') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>
        </fieldset>

        {{-- 3. Cuenta definitiva --}}
        <fieldset class="space-y-3">
            <legend class="{{ $titulo }}">3. Tu cuenta de administrador</legend>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombre(s)" maxlength="100" required autocomplete="given-name" class="{{ $campoClase }}">
                    @error('nombres') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" placeholder="Apellido paterno" maxlength="100" required class="{{ $campoClase }}">
                    @error('apellido_paterno') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>
            </div>

            <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" placeholder="Apellido materno (opcional)" maxlength="100" class="{{ $campoClase }}">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="Usuario" maxlength="50" required autocomplete="username" class="{{ $campoClase }}">
                    @error('username') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input type="email" name="correo" value="{{ old('correo') }}" placeholder="Correo" maxlength="150" required autocomplete="email" class="{{ $campoClase }}">
                    @error('correo') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <input type="password" name="password" placeholder="Contraseña" required minlength="10" autocomplete="new-password" class="{{ $campoClase }}">
                @error('password') <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror
            </div>

            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" required minlength="10" autocomplete="new-password" class="{{ $campoClase }}">

            <p class="text-xs text-[var(--theme-text-muted)]">
                Al menos 10 caracteres, con mayúscula, minúscula, número y carácter especial, sin espacios.
                Al entrar se te pedirá activar la verificación en dos pasos.
            </p>
        </fieldset>

        @include('auth.partials.boton-envio', ['id' => 'configurarButton', 'texto' => 'GUARDAR Y TERMINAR', 'cargando' => 'Guardando.'])
    </form>

    <form method="POST" action="{{ route('logout') }}" class="pt-3 text-center">
        @csrf
        <button type="submit" class="text-xs text-[var(--theme-primary)] transition-colors hover:text-[var(--theme-primary-hover)] hover:underline">
            Cerrar sesión
        </button>
    </form>

</x-auth-card>

@endsection
