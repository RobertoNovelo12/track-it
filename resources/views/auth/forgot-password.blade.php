@extends('layouts.guest')

@section('title', 'Recuperar contraseña')
@section('page-label', 'RECUPERAR CONTRASEÑA')

@php
    $campoClase = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
@endphp

@section('content')

{{-- Paso 1 de 2: identificar la cuenta (las preguntas vienen después). --}}
<x-auth-card heading="Recuperar contraseña" icon="key">

    <p class="mb-5 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Escribe tu nombre de usuario o tu correo. Después responderás dos preguntas
        de seguridad y un administrador te dará una contraseña temporal.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <input
                type="text"
                name="login"
                value="{{ old('login') }}"
                placeholder="Usuario o correo electrónico"
                autocomplete="username"
                required
                maxlength="255"
                class="{{ $campoClase }}"
            >

            @error('login')
                <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
            @enderror
        </div>

        @include('auth.partials.boton-envio', ['id' => 'forgotButton', 'texto' => 'CONTINUAR', 'cargando' => 'Buscando…'])

        <div class="pt-1 text-center">
            <a
                href="{{ route('login') }}"
                class="text-xs text-[var(--theme-primary)] transition-colors hover:text-[var(--theme-primary-hover)] hover:underline"
            >
                Volver a iniciar sesión
            </a>
        </div>
    </form>

</x-auth-card>

@endsection
