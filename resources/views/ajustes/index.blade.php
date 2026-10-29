@extends('layouts.app')

@section('title', 'Ajustes')

@section('content')

<div>

    {{-- Administrador sin 2FA (App\Http\Middleware\ExigirDosFactoresAdmin) --}}
    @if (auth()->user()?->esAdministrador() && ! auth()->user()?->hasTwoFactorEnabled())
        <div
            role="alert"
            class="mb-5 rounded-md border border-[var(--theme-warning-border,#E7C98A)] bg-[var(--theme-warning-soft,#FDF6E7)] px-4 py-3 text-sm text-[var(--theme-text-strong)]"
        >
            <p class="font-semibold">Activa la verificación en dos pasos</p>
            <p class="mt-1 text-[var(--theme-text)]">
                Por seguridad, las cuentas de administrador deben usar verificación en dos pasos.
                Actívala aquí en <strong>Seguridad</strong> para volver a usar el resto del sistema.
            </p>
        </div>
    @endif

    {{-- ============================================================
        CONTENIDO
    ============================================================ --}}
    <livewire:ajustes-cuenta lazy />

</div>

@endsection