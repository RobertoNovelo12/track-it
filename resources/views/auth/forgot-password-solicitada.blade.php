@extends('layouts.guest')

@section('title', 'Solicitud enviada')
@section('page-label', 'RECUPERAR CONTRASEÑA')

@section('content')

<x-auth-card heading="Solicitud enviada" icon="key">

    <div class="space-y-4 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-b7aa433') }}"></use></svg>
        </div>

        <p class="text-sm leading-relaxed text-[var(--theme-text)]">
            Verificamos tu identidad y avisamos a los administradores.
        </p>

        <p class="text-xs leading-relaxed text-[var(--theme-text-muted)]">
            Un administrador te dará una <strong>contraseña temporal</strong>. Al iniciar sesión
            con ella, el sistema te pedirá elegir una nueva. La solicitud es válida por
            {{ \App\Services\RestablecimientoContrasenaService::VIGENCIA_HORAS }} horas.
        </p>

        <a
            href="{{ route('login') }}"
            class="inline-flex h-10 w-full items-center justify-center rounded-md bg-[var(--theme-primary)] text-sm font-medium tracking-wide text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
        >
            VOLVER A INICIAR SESIÓN
        </a>
    </div>

</x-auth-card>

@endsection
