{{-- ============================================================
    COLABORADORES SIN CUENTA
    ------------------------------------------------------------
    Equipos asignados (vigentes) a personas que se escribieron a
    mano al asignar y que aún no tienen cuenta. Por cada una:
    - Vincular: ya tiene cuenta con otro nombre → sus asignaciones
      pasan a esa cuenta (toman su nombre y número).
    - Crear cuenta: formulario normal de nuevo usuario con los datos
      precargados; al guardarlo se vinculan sus equipos.
    Datos: App\Services\ColaboradoresService::pendientes()
============================================================ --}}
@php
    $totalEquiposSinCuenta = collect($colaboradoresSinCuenta)->sum(fn ($grupo) => count($grupo['equipos']));
@endphp

<section class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)]">

    {{-- Cabecera --}}
    <div class="flex flex-col gap-1 border-b border-[var(--theme-border)] px-4 py-4 sm:px-5">
        <div class="flex items-center gap-2">
            <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                Colaboradores sin cuenta
            </h2>

            @if ($colaboradoresSinCuenta !== [])
                <span class="rounded-full bg-[var(--theme-warning-soft)] px-2 py-0.5 text-[11px] font-medium text-[var(--theme-warning)]">
                    {{ count($colaboradoresSinCuenta) }}
                </span>
            @endif
        </div>

        <p class="text-xs text-[var(--theme-text-muted)]">
            @if ($colaboradoresSinCuenta === [])
                Todos los equipos asignados pertenecen a una cuenta.
            @else
                {{ $totalEquiposSinCuenta }} {{ $totalEquiposSinCuenta === 1 ? 'equipo asignado' : 'equipos asignados' }}
                a personas sin cuenta. Vincúlalas con su cuenta si ya existe con otro nombre, o crea la cuenta.
            @endif
        </p>
    </div>

    @if ($colaboradoresSinCuenta === [])
        <x-ui.empty-state
            compact
            title="Sin pendientes"
            description="Cuando se asigne un equipo a alguien sin cuenta, aparecerá aquí."
        />
    @else
        <ul class="divide-y divide-[var(--theme-border)]">
            @foreach ($colaboradoresSinCuenta as $grupo)
                <li
                    wire:key="colaborador-sin-cuenta-{{ md5($grupo['clave']) }}"
                    x-data="{ vinculando: false, cuenta: '', enviando: false }"
                    class="px-4 py-4 sm:px-5"
                >
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">

                        {{-- Colaborador y sus equipos --}}
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[var(--theme-text-strong)]">
                                {{ $grupo['nombre'] }}

                                @if ($grupo['numero'])
                                    <span class="ml-1 text-xs font-normal text-[var(--theme-text-muted)]">· {{ $grupo['numero'] }}</span>
                                @endif
                            </p>

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($grupo['equipos'] as $equipo)
                                    <a
                                        href="{{ route('equipos.show', $equipo['id_equipo']) }}"
                                        wire:navigate.hover
                                        title="{{ $equipo['ubicacion'] }}"
                                        class="inline-flex max-w-full items-center gap-1 rounded-md border border-[var(--theme-border)] px-2 py-0.5 text-[11px] text-[var(--theme-text)] transition-colors hover:border-[var(--theme-primary-border)] hover:text-[var(--theme-primary)]"
                                    >
                                        <span class="font-medium">{{ $equipo['codigo'] }}</span>
                                        <span class="truncate text-[var(--theme-text-muted)]">{{ $equipo['nombre'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex shrink-0 flex-wrap items-center gap-2" x-show="! vinculando">
                            <x-ui.button
                                variant="secondary"
                                size="sm"
                                x-on:click="vinculando = true"
                            >
                                Vincular con cuenta
                            </x-ui.button>

                            <a
                                href="{{ route('usuarios.create', array_filter(['colaborador' => $grupo['nombre'], 'numero' => $grupo['numero']])) }}"
                                wire:navigate
                                class="inline-flex h-8 items-center justify-center rounded-[var(--radius-md)] border border-transparent bg-[var(--theme-primary)] px-3 text-xs font-medium text-white transition-colors duration-[var(--motion-fast)] hover:bg-[var(--theme-primary-hover)]"
                            >
                                Crear cuenta
                            </a>
                        </div>
                    </div>

                    {{-- Vincular: elegir la cuenta existente --}}
                    <div
                        x-show="vinculando"
                        x-cloak
                        class="mt-3 flex flex-col gap-2 rounded-md border border-[var(--theme-border)] bg-[var(--theme-surface-soft)] p-3 sm:flex-row sm:items-end"
                    >
                        <div class="min-w-0 flex-1">
                            <x-searchable-select
                                label="Cuenta de este colaborador"
                                x-model="cuenta"
                                :options="$opcionesCuentas"
                                :show-clear="false"
                                placeholder="Buscar por nombre, número o correo..."
                                compact
                            />
                        </div>

                        <div class="flex shrink-0 gap-2">
                            <x-ui.button
                                variant="ghost"
                                size="sm"
                                x-on:click="vinculando = false; cuenta = ''"
                                x-disabled="enviando"
                            >
                                Cancelar
                            </x-ui.button>

                            <x-ui.button
                                size="sm"
                                x-loading="enviando"
                                loading-text="Vinculando…"
                                x-disabled="enviando || cuenta === ''"
                                x-on:click="
                                    enviando = true;
                                    $wire.vincularColaborador(@js($grupo['clave']), cuenta)
                                        .finally(() => { enviando = false; });
                                "
                            >
                                Vincular
                            </x-ui.button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
