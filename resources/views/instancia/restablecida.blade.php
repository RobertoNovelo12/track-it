@extends('layouts.guest')

@section('title', 'Instancia restablecida')
@section('page-label', 'INSTANCIA RESTABLECIDA')

@section('content')

{{-- Se muestra una sola vez, justo después de restablecer (App\Livewire\RestablecerInstancia). --}}
<x-auth-card heading="Instancia restablecida" icon="key">

    <p class="mb-5 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Se creó un usuario temporal para configurar el sistema.
        <strong class="text-[var(--theme-text-strong)]">Copia la contraseña ahora:</strong>
        no se volverá a mostrar.
    </p>

    <div class="space-y-3">
        <div class="rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-4 py-3">
            <p class="text-xs text-[var(--theme-text-muted)]">Usuario</p>
            <p class="font-mono text-sm text-[var(--theme-text-strong)]">{{ $credenciales['usuario'] }}</p>
        </div>

        <div class="rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-[var(--theme-text-muted)]">Contraseña temporal</p>
                    <p class="break-all font-mono text-sm text-[var(--theme-text-strong)]">{{ $credenciales['contrasena'] }}</p>
                </div>
                {{-- Esta pantalla usa el layout de invitado (sin Alpine): JavaScript simple. --}}
                <button
                    type="button"
                    data-copiar="{{ $credenciales['contrasena'] }}"
                    onclick="navigator.clipboard?.writeText(this.dataset.copiar).then(() => { this.textContent = 'Copiada'; setTimeout(() => this.textContent = 'Copiar', 2000); })"
                    class="shrink-0 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-1.5 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                >Copiar</button>
            </div>
        </div>
    </div>

    <p class="mt-5 text-center text-xs leading-relaxed text-[var(--theme-text-muted)]">
        Al entrar, el sistema te pedirá elegir la plantilla (hoteles o instituciones), crear la
        primera sede con su departamento y tu cuenta de administrador. El usuario temporal se elimina al terminar.
    </p>

    <a
        href="{{ route('login') }}"
        class="mt-5 flex w-full items-center justify-center rounded-md bg-[var(--theme-primary)] py-3 text-sm font-medium tracking-wide text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
    >
        IR A INICIAR SESIÓN
    </a>

</x-auth-card>

@endsection
