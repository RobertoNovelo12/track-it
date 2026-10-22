{{-- ============================================================
    AVISO FIJO: CUENTA INCOMPLETA
    ------------------------------------------------------------
    Se muestra mientras falten los datos para recuperar la
    contraseña con preguntas de seguridad (fecha de nacimiento y,
    si se puede, número de colaborador). Lleva a Ajustes › Cuenta
    con «Editar perfil» abierto. Desaparece al guardar el perfil.

    Si el formulario de perfil ya está en la página, «Completar ahora»
    solo abre la ventana (sin recargar).

    Solo se muestra en Ajustes (para no estorbar en el resto); en las
    demás pantallas el recordatorio queda fijo en Notificaciones.
============================================================ --}}
@php
    $usuarioAviso = auth()->user();
    $atributosAviso = $usuarioAviso?->getAttributes() ?? [];

    // Solo cuando ya existe la columna (migración aplicada).
    $faltaFecha = array_key_exists('fecha_nacimiento', $atributosAviso) && blank($atributosAviso['fecha_nacimiento']);
    $faltaNumero = blank($atributosAviso['numero_colaborador'] ?? null);
@endphp

@include('layouts.partials.completar-perfil-script')

@if ($usuarioAviso && $faltaFecha && request()->routeIs('ajustes.index'))
    <div
        x-data="{ visible: true }"
        x-show="visible"
        x-on:perfil-actualizado.window="visible = false"
        role="status"
        class="mb-5 flex flex-col gap-3 rounded-lg border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex min-w-0 items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-[var(--theme-warning)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21a8 8 0 0 1 16 0"/>
                <path d="M19 3v4M19 10h.01"/>
            </svg>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    Aún necesitamos información de tu cuenta
                </p>

                <p class="mt-0.5 text-xs leading-relaxed text-[var(--theme-text)]">
                    Agrega tu <strong>fecha de nacimiento</strong> para poder recuperar tu contraseña si algún día la olvidas.
                    @if ($faltaNumero)
                        También te recomendamos solicitar tu <strong>número de colaborador</strong> en «Solicitar cambio».
                    @endif
                </p>
            </div>
        </div>

        <a
            href="{{ route('ajustes.index', ['section' => 'cuenta', 'completar' => 'perfil']) }}"
            x-on:click.prevent="window.trackitCompletarPerfil($el.href)"
            class="inline-flex h-9 shrink-0 items-center justify-center rounded-md bg-[var(--theme-primary)] px-4 text-xs font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
        >
            Completar ahora
        </a>
    </div>
@endif
