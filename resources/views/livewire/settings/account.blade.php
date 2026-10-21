{{-- ============================================================
    AJUSTES › CUENTA
    ------------------------------------------------------------
    1. Información personal: encabezado con avatar y estado,
       datos en dos columnas de filas etiqueta / valor.
    2. Información organizacional (settings/operation).
============================================================ --}}
@php
    $estadoClave = strtoupper($usuario->estado_clave ?? '');

    $estadoClasses = match ($estadoClave) {
        'ACTIVO' =>
            'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

        'PENDIENTE' =>
            'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

        'INACTIVO', 'BAJA' =>
            'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

        default =>
            'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]',
    };

    $iniciales = mb_strtoupper(
        mb_substr($usuario->nombres ?? '?', 0, 1)
        . mb_substr($usuario->apellido_paterno ?? '', 0, 1)
    );

    /*
     * Columnas de datos: [etiqueta, valor, clases extra del valor].
     */
    $datosPersonales = [
        [
            ['Nombre completo', $nombreCompleto, ''],
            ['Nombre de usuario', $usuario->username, ''],
        ],
        [
            ['Correo electrónico', $usuario->correo, 'break-all'],
            ['Teléfono', $usuario->telefono ?: null, ''],
            ['Fecha de nacimiento', $usuario->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($usuario->fecha_nacimiento)->format('d/m/Y') : null, ''],
        ],
    ];
@endphp

<div class="space-y-5">

    {{-- ====================================================
        INFORMACIÓN PERSONAL
    ==================================================== --}}
    <section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">

        {{-- Encabezado: identidad + acción --}}
        <div class="flex flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-5">

            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[var(--theme-primary-soft)] text-lg font-semibold text-[var(--theme-primary)]">
                    {{ $iniciales }}
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-base font-semibold text-[var(--theme-text-strong)]">
                            {{ $nombreCompleto }}
                        </h2>

                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {{ $estadoClasses }}">
                            {{ $usuario->estado_nombre ?? 'Sin estado' }}
                        </span>
                    </div>

                    <p class="mt-0.5 truncate text-xs text-[var(--theme-text-muted)]">
                        {{ '@' . $usuario->username }}
                    </p>
                </div>
            </div>

            <button
                x-data="{}"
                type="button"
                @click="$dispatch('abrir-edicion-perfil')"
                class="inline-flex h-9 shrink-0 items-center justify-center gap-2 self-start rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:border-[var(--theme-primary-border)] hover:bg-[var(--theme-surface-soft)] sm:self-auto"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>
                </svg>

                Editar perfil
            </button>
        </div>

        {{-- Datos --}}
        <div class="border-t border-[var(--theme-border)] px-4 py-2 sm:px-5">
            <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-10">
                @foreach ($datosPersonales as $columna)
                    <dl>
                        @foreach ($columna as [$etiqueta, $valor, $extra])
                            <div @class([
                                'flex items-baseline gap-4 py-3 border-[var(--theme-border)]',
                                'border-b' => ! ($loop->parent->last && $loop->last),
                                'md:border-b-0' => $loop->last,
                            ])>
                                <dt class="w-40 shrink-0 text-xs text-[var(--theme-text-muted)]">
                                    {{ $etiqueta }}
                                </dt>

                                <dd class="min-w-0 flex-1 text-sm font-medium {{ filled($valor) ? 'text-[var(--theme-text)]' : 'text-[var(--theme-text-muted)]' }} {{ $extra }}">
                                    {{ filled($valor) ? $valor : 'Sin especificar' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endforeach
            </div>
        </div>

    </section>


    {{-- ====================================================
        INFORMACIÓN ORGANIZACIONAL
    ==================================================== --}}
    @include('livewire.settings.operation')

</div>
