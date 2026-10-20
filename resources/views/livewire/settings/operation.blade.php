{{-- ====================================================
    INFORMACIÓN ORGANIZACIONAL
    ------------------------------------------------------------
    Dos grupos de filas etiqueta / valor:
    - Organización: hotel, departamento, sede y puesto.
    - Cuenta: número de colaborador, rol, alta y último acceso.
==================================================== --}}
@php
    $etiquetasPlantilla = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();

    $gruposOrganizacion = [
        'Organización' => [
            [$etiquetasPlantilla['unidad_singular'], $usuario->unidad_nombre ?? null, 'Sin asignar'],
            ['Departamento', $usuario->departamento_nombre ?? null, 'Sin asignar'],
            ['Sede', $usuario->sede_nombre ?? null, 'Sin asignar'],
            ['Puesto', $usuario->puesto ?? null, 'Sin puesto asignado'],
        ],
        'Cuenta' => [
            ['Número de colaborador', $usuario->numero_colaborador ?? null, 'Sin especificar'],
            ['Rol', $usuario->rol_nombre ?? null, 'Sin rol asignado'],
            [
                'Fecha de alta',
                $usuario->fecha_registro
                    ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                    : null,
                '—',
            ],
            [
                'Último acceso',
                $usuario->ultimo_acceso
                    ? \App\Support\Fecha::conHora($usuario->ultimo_acceso)
                    : null,
                'Sin registro',
            ],
        ],
    ];
@endphp

<section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">

    {{-- ====================================================
        CABECERA
    ==================================================== --}}
    <div class="flex flex-col gap-4 px-4 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-5">

        <div class="min-w-0">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Información organizacional
            </h2>

            <p class="mt-1 flex items-start gap-1.5 text-xs text-[var(--theme-text-muted)]">
                <svg class="mt-px h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="11" width="14" height="9" rx="1.5"/>
                    <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                </svg>

                <span>
                    Estos datos los administra tu organización. Si alguno es incorrecto, solicita su actualización.
                </span>
            </p>
        </div>

        <button
            type="button"
            @click="$dispatch('abrir-solicitud-cambio-organizacion')"
            class="inline-flex h-9 shrink-0 items-center justify-center gap-2 self-start rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)]"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 7h11"/>
                <path d="M12 3l4 4-4 4"/>
                <path d="M20 17H9"/>
                <path d="M12 13l-4 4 4 4"/>
            </svg>

            Solicitar cambio
        </button>
    </div>


    {{-- ====================================================
        DATOS
    ==================================================== --}}
    <div class="border-t border-[var(--theme-border)] px-4 py-4 sm:px-5">
        <div class="grid grid-cols-1 gap-y-5 md:grid-cols-2 md:gap-x-10">
            @foreach ($gruposOrganizacion as $titulo => $filas)
                <div>
                    <h3 class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-[var(--theme-text-muted)]">
                        {{ $titulo }}
                    </h3>

                    <dl>
                        @foreach ($filas as [$etiqueta, $valor, $vacio])
                            <div @class([
                                'flex items-baseline gap-4 py-3 border-[var(--theme-border)]',
                                'border-b' => ! $loop->last,
                            ])>
                                <dt class="w-40 shrink-0 text-xs text-[var(--theme-text-muted)]">
                                    {{ $etiqueta }}
                                </dt>

                                <dd @class([
                                    'min-w-0 flex-1 truncate text-sm font-medium',
                                    'text-[var(--theme-text)]' => filled($valor),
                                    'text-[var(--theme-text-muted)]' => blank($valor),
                                ]) title="{{ filled($valor) ? $valor : '' }}">
                                    {{ filled($valor) ? $valor : $vacio }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </div>

</section>
