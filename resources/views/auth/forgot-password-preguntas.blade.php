@extends('layouts.guest')

@section('title', 'Preguntas de seguridad')
@section('page-label', 'RECUPERAR CONTRASEÑA')

@php
    $campoClase = 'w-full bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-md px-4 py-2.5 text-sm text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--theme-primary-soft)] focus:border-[var(--theme-primary)] transition-colors';
@endphp

@section('content')

{{-- Paso 2 de 2: dos preguntas con datos de la cuenta. --}}
<x-auth-card heading="Preguntas de seguridad" icon="key">

    <p class="mb-5 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Responde con los datos registrados en tu cuenta. Si son correctos,
        avisaremos a un administrador para que restablezca tu contraseña.
    </p>

    @error('respuestas')
        <div class="mb-4 rounded-md border border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] px-3 py-2 text-sm text-[var(--theme-danger)]">
            {{ $message }}
        </div>
    @enderror

    <form method="POST" action="{{ route('password.verificar') }}" class="space-y-4" autocomplete="off">
        @csrf

        @foreach ($preguntas as $clave => $pregunta)
            <div>
                <label for="respuesta-{{ $clave }}" class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">
                    {{ $pregunta['texto'] }}
                </label>

                @if ($pregunta['tipo'] === 'marca')
                    <select
                        id="respuesta-{{ $clave }}"
                        name="respuestas[{{ $clave }}]"
                        required
                        class="{{ $campoClase }}"
                    >
                        <option value="">Selecciona la marca…</option>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca['value'] }}" @selected(old('respuestas.'.$clave) === $marca['value'])>
                                {{ $marca['label'] }}
                            </option>
                        @endforeach
                    </select>
                @elseif ($pregunta['tipo'] === 'date')
                    <input
                        id="respuesta-{{ $clave }}"
                        type="date"
                        name="respuestas[{{ $clave }}]"
                        value="{{ old('respuestas.'.$clave) }}"
                        max="{{ now()->toDateString() }}"
                        required
                        class="{{ $campoClase }}"
                    >
                @else
                    <input
                        id="respuesta-{{ $clave }}"
                        type="text"
                        name="respuestas[{{ $clave }}]"
                        value="{{ old('respuestas.'.$clave) }}"
                        inputmode="numeric"
                        maxlength="255"
                        required
                        class="{{ $campoClase }}"
                    >
                @endif

                @error('respuestas.'.$clave)
                    <p class="mt-1 text-xs text-[var(--theme-danger)]">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        @include('auth.partials.boton-envio', ['id' => 'verifyButton', 'texto' => 'VERIFICAR', 'cargando' => 'Verificando…'])

        <div class="pt-1 text-center">
            <a
                href="{{ route('password.request') }}"
                class="text-xs text-[var(--theme-primary)] transition-colors hover:text-[var(--theme-primary-hover)] hover:underline"
            >
                Usar otra cuenta
            </a>
        </div>
    </form>

</x-auth-card>

@endsection
