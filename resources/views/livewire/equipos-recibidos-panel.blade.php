{{-- ============================================================
    EQUIPOS RECIBIDOS (barra superior, escritorio)
    Lo que el usuario envió desde su teléfono con el escáner.
============================================================ --}}
{{--
    El estado «abierto» vive en un store global de Alpine
    ($store.equiposRecibidos), no en x-data: el refresco periódico
    (wire:poll) vuelve a evaluar el botón y el panel en una copia del
    HTML donde a veces no se encuentra el x-data del contenedor
    («abierto is not defined»). El store no depende de ese alcance.
--}}
<div wire:poll.15s.visible="refrescar">
<script>
    (function () {
        const registrar = () => {
            if (! window.Alpine.store('equiposRecibidos')) {
                window.Alpine.store('equiposRecibidos', { abierto: false });
            }
        };

        window.Alpine ? registrar() : document.addEventListener('alpine:init', registrar);
    })();
</script>
<div
    class="relative"
    x-data
    x-on:keydown.escape.window="$store.equiposRecibidos.abierto = false"
    x-on:click.outside="$store.equiposRecibidos.abierto = false"
>
    <button
        type="button"
        x-on:click="$store.equiposRecibidos.abierto = ! $store.equiposRecibidos.abierto"
        x-bind:aria-expanded="$store.equiposRecibidos?.abierto ? 'true' : 'false'"
        aria-haspopup="dialog"
        title="Equipos recibidos desde el teléfono"
        aria-label="Equipos recibidos desde el teléfono"
        class="relative flex h-9 w-9 items-center justify-center rounded-full text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-primary-soft)] hover:text-[var(--theme-primary)]"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="7" y="2.5" width="10" height="19" rx="2"/>
            <path d="M11 18.5h2"/>
            <path d="M3 9l2.5 2.5L3 14M21 9l-2.5 2.5L21 14"/>
        </svg>

        {{-- Contador (sobre el ícono, como el punto de notificaciones) --}}
        @if ($pendientes > 0)
            <span class="absolute -right-0.5 -top-0.5 min-w-[1.125rem] rounded-full bg-[var(--theme-primary)] px-1 py-0.5 text-center text-[10px] font-semibold leading-none text-white">
                {{ $pendientes > 99 ? '99+' : $pendientes }}
            </span>
        @endif
    </button>

    {{-- Panel --}}
    <div
        x-show="$store.equiposRecibidos?.abierto"
        x-cloak
        x-transition:enter="transition ease-out duration-[var(--motion-base)]"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-[var(--motion-fast)]"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute right-0 top-full z-40 mt-2 w-[26rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-[var(--radius-lg)] border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl"
        role="dialog"
        aria-label="Equipos recibidos"
    >
        <div class="flex items-center justify-between gap-3 border-b border-[var(--theme-border)] px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-[var(--theme-text-strong)]">Equipos recibidos</p>
                <p class="text-[11px] text-[var(--theme-text-muted)]">Enviados desde tu teléfono con el escáner.</p>
            </div>

            @if ($pendientes > 0)
                <button
                    type="button"
                    wire:click="atenderTodos"
                    wire:confirm="¿Marcar todos los equipos recibidos como atendidos?"
                    class="shrink-0 text-[11px] font-medium text-[var(--theme-text-muted)] hover:text-[var(--theme-primary)]"
                >
                    Marcar todos
                </button>
            @endif
        </div>

        @if ($equipos === [])
            <x-ui.empty-state
                compact
                title="Sin equipos pendientes"
                description="Escanea códigos de barras desde tu teléfono y elige «Enviar a la PC». Los códigos que no estén en el inventario llegan aquí para agregarlos."
            />
        @else
            <ul class="max-h-[60vh] divide-y divide-[var(--theme-border)] overflow-y-auto">
                @foreach ($equipos as $equipo)
                    <li wire:key="recibido-{{ $equipo['id'] }}" class="px-4 py-3">
                        @if ($equipo['nuevo'])
                            {{-- Código que no está en el inventario: agregarlo con ese número de serie --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-[var(--theme-text-strong)]">
                                        No está en el inventario
                                    </p>

                                    <p class="mt-0.5 break-all text-[11px] text-[var(--theme-text-muted)]">
                                        SN <span class="font-mono text-[var(--theme-text)]">{{ $equipo['codigo_leido'] }}</span>
                                        · {{ \App\Support\Fecha::hora($equipo['recibido_en']) }}
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full bg-[var(--theme-warning-soft)] px-2 py-0.5 text-[10px] font-medium text-[var(--theme-warning)]">
                                    Nuevo
                                </span>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @if ($puedeDarDeBaja)
                                    <a
                                        href="{{ route('equipos.create', ['numero_serie' => $equipo['codigo_leido']]) }}"
                                        wire:navigate
                                        x-on:click="$store.equiposRecibidos.abierto = false"
                                        class="inline-flex h-7 items-center rounded-[var(--radius-md)] bg-[var(--theme-primary)] px-2.5 text-[11px] font-medium text-white hover:bg-[var(--theme-primary-hover)]"
                                    >
                                        Agregar equipo
                                    </a>
                                @endif

                                <button
                                    type="button"
                                    wire:click="atender({{ $equipo['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="atender({{ $equipo['id'] }})"
                                    class="ml-auto inline-flex h-7 items-center rounded-[var(--radius-md)] px-2.5 text-[11px] font-medium text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)] disabled:opacity-60"
                                >
                                    Descartar
                                </button>
                            </div>
                        @else
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--theme-text-strong)]">
                                    {{ $equipo['nombre'] ?: $equipo['codigo'] }}
                                </p>

                                <p class="mt-0.5 text-[11px] text-[var(--theme-text-muted)]">
                                    {{ $equipo['codigo'] }}
                                    @if ($equipo['tipo']) · {{ $equipo['tipo'] }} @endif
                                    · {{ \App\Support\Fecha::hora($equipo['recibido_en']) }}
                                </p>
                            </div>

                            @if ($equipo['estado'])
                                <x-status-badge :status="$equipo['estado']" />
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <a
                                href="{{ route('equipos.show', $equipo['id_equipo']) }}"
                                wire:navigate
                                x-on:click="$store.equiposRecibidos.abierto = false"
                                class="inline-flex h-7 items-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] px-2.5 text-[11px] font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]"
                            >
                                Ver
                            </a>

                            @if ($puedeDarDeBaja && ! $equipo['dado_de_baja'])
                                <a
                                    href="{{ route('equipos.baja', $equipo['id_equipo']) }}"
                                    wire:navigate
                                    x-on:click="$store.equiposRecibidos.abierto = false"
                                    class="inline-flex h-7 items-center rounded-[var(--radius-md)] border border-transparent bg-[var(--theme-danger-soft)] px-2.5 text-[11px] font-medium text-[var(--theme-danger)] hover:opacity-90"
                                >
                                    Dar de baja
                                </a>
                            @endif

                            <button
                                type="button"
                                wire:click="atender({{ $equipo['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="atender({{ $equipo['id'] }})"
                                class="ml-auto inline-flex h-7 items-center rounded-[var(--radius-md)] px-2.5 text-[11px] font-medium text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)] disabled:opacity-60"
                            >
                                Atendido
                            </button>
                        </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
</div>
