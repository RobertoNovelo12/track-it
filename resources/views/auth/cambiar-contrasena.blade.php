@extends('layouts.guest')

@section('title', 'Nueva contraseña')
@section('page-label', 'NUEVA CONTRASEÑA')

@php
    $campoClase = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
@endphp

@section('content')

{{-- Obligatorio tras recibir una contraseña temporal de un administrador. --}}
<x-auth-card heading="Elige una nueva contraseña" icon="key">

    <p class="mb-5 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Entraste con una contraseña temporal. Para continuar, elige una nueva:
        al menos 10 caracteres, con mayúscula, minúscula, número y carácter especial, sin espacios.
    </p>

    <form method="POST" action="{{ route('password.cambiar.guardar') }}" class="space-y-4">
        @csrf

        <div>
            <input
                type="password"
                name="password"
                placeholder="Nueva contraseña"
                autocomplete="new-password"
                required
                minlength="10"
                class="{{ $campoClase }}"
            >

            @error('password')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input
                type="password"
                name="password_confirmation"
                placeholder="Confirmar nueva contraseña"
                autocomplete="new-password"
                required
                minlength="10"
                class="{{ $campoClase }}"
            >
        </div>

        @include('auth.partials.boton-envio', ['id' => 'cambioButton', 'texto' => 'GUARDAR Y CONTINUAR', 'cargando' => 'Guardando…'])
    </form>

    <form method="POST" action="{{ route('logout') }}" class="pt-3 text-center">
        @csrf
        <button type="submit" class="text-xs text-[var(--theme-primary)] transition-colors hover:text-[var(--theme-primary-hover)] hover:underline">
            Cerrar sesión
        </button>
    </form>

</x-auth-card>

@endsection
