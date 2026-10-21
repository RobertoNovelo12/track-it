{{-- ============================================================
    SOLICITUDES DE RESTABLECIMIENTO DE CONTRASEÑA
    ------------------------------------------------------------
    Usuarios que respondieron sus preguntas de seguridad en
    «¿Olvidaste tu contraseña?» y esperan una contraseña temporal.
    Solo aparece cuando hay solicitudes vigentes.
    Datos: App\Services\RestablecimientoContrasenaService::pendientes()
============================================================ --}}
@php
    $solicitudesRestablecimiento = app(\App\Services\RestablecimientoContrasenaService::class)->pendientes();
@endphp

@if ($solicitudesRestablecimiento !== [])
    <section class="rounded-lg border border-[var(--theme-warning)] bg-[var(--theme-surface)]">
        <div class="flex flex-col gap-1 border-b border-[var(--theme-border)] px-4 py-4 sm:px-5">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                    Restablecimiento de contraseña
                </h2>

                <span class="rounded-full bg-[var(--theme-warning-soft)] px-2 py-0.5 text-[11px] font-medium text-[var(--theme-warning)]">
                    {{ count($solicitudesRestablecimiento) }}
                </span>
            </div>

            <p class="text-xs text-[var(--theme-text-muted)]">
                Respondieron correctamente sus preguntas de seguridad. Genera una contraseña temporal y entrégasela en persona.
            </p>
        </div>

        <ul class="divide-y divide-[var(--theme-border)]">
            @foreach ($solicitudesRestablecimiento as $solicitud)
                <li
                    wire:key="restablecer-{{ $solicitud['id'] }}"
                    class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-[var(--theme-text-strong)]">
                            {{ $solicitud['nombre'] }}
                        </p>
                        <p class="truncate text-xs text-[var(--theme-text-muted)]">
                            {{ '@'.$solicitud['username'] }} · solicitado {{ \Illuminate\Support\Carbon::parse($solicitud['fecha'])->diffForHumans() }}
                        </p>
                    </div>

                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('abrir-restablecer-contrasena', @js(['id' => $solicitud['id'], 'nombre' => $solicitud['nombre'], 'username' => $solicitud['username']]))"
                        class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-md bg-[var(--theme-primary)] px-3 text-xs font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="8" cy="15" r="4"/>
                            <path d="M10.85 12.15L19 4M18 5l2 2M15 8l2 2"/>
                        </svg>
                        Restablecer contraseña
                    </button>
                </li>
            @endforeach
        </ul>
    </section>
@endif
