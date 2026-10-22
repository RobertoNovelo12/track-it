{{-- ============================================================
    AJUSTES › PREFERENCIAS
    ------------------------------------------------------------
    Cada fila: nombre + descripción a la izquierda, control a la
    derecha. En móvil el control baja debajo del texto.
============================================================ --}}
@php
    /*
     * Sin overflow-hidden: las listas desplegables deben poder salir
     * de la tarjeta.
     */
    $seccion = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]';
    $cabecera = 'border-b border-[var(--theme-border)] px-4 py-4 sm:px-5';
    $fila = 'flex flex-col gap-3 px-4 py-4 sm:px-5 md:flex-row md:items-center md:justify-between md:gap-8';
    $control = 'w-full shrink-0 md:w-72';
    $error = 'mt-1 text-xs text-[var(--theme-danger)]';

    /*
     * Opciones tipo tarjeta (tema, densidad, formato de hora).
     */
    $claseTarjetaActiva = 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)] text-[var(--theme-primary)]';
    $claseTarjetaInactiva = 'border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-[var(--theme-text)] hover:border-[var(--theme-primary-border)]';

    $tarjeta = fn (bool $activa) => $activa ? $claseTarjetaActiva : $claseTarjetaInactiva;

    /*
     * Tema y densidad cambian en el navegador al instante (Alpine);
     * el valor viaja al servidor al pulsar «Guardar preferencias»
     * (entangle sin .live). Antes cada clic esperaba al servidor.
     * Las clases del servidor evitan parpadeo; Alpine las alterna.
     */
    $tarjetaAlpine = fn (string $modelo, string $valor) => '{ ['
        . \Illuminate\Support\Js::from($claseTarjetaActiva) . ']: ' . $modelo . ' === ' . \Illuminate\Support\Js::from($valor)
        . ', [' . \Illuminate\Support\Js::from($claseTarjetaInactiva) . ']: ' . $modelo . ' !== ' . \Illuminate\Support\Js::from($valor)
        . ' }';

    $unidadSingular = mb_strtolower($etiquetas['unidad_singular']);
@endphp

<div class="space-y-5">

    {{-- ====================================================
        APARIENCIA
    ==================================================== --}}
    <section
        class="{{ $seccion }}"
        x-data="{
            tema: $wire.entangle('tema'),
            densidad: $wire.entangle('densidad'),
        }"
    >
        <div class="{{ $cabecera }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Apariencia</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Cómo se ve el sistema en tus dispositivos.</p>
        </div>

        <div class="divide-y divide-[var(--theme-border)]">

            {{-- Tema --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Tema</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        «Según el sistema» cambia solo entre claro y oscuro con tu equipo.
                    </p>
                </div>

                <div class="grid w-full shrink-0 grid-cols-3 gap-2 md:w-auto">
                    @foreach (\App\Services\PreferenciasUsuarioService::TEMAS as $valor => $nombre)
                        <label
                            wire:key="pref-tema-{{ $valor }}"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-colors {{ $tarjeta($tema === $valor) }}"
                            x-bind:class="{{ $tarjetaAlpine('tema', $valor) }}"
                        >
                            <input type="radio" x-model="tema" value="{{ $valor }}" class="sr-only">

                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @if ($valor === 'light')
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
                                @elseif ($valor === 'dark')
                                    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
                                @else
                                    <rect x="3" y="4" width="18" height="12" rx="1.5"/>
                                    <path d="M8 20h8M12 16v4"/>
                                @endif
                            </svg>

                            <span class="whitespace-nowrap">{{ $valor === 'system' ? 'Sistema' : $nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Densidad --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Densidad de tablas</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        «Compacta» reduce el alto de las filas para ver más registros a la vez.
                    </p>
                </div>

                <div class="grid w-full shrink-0 grid-cols-2 gap-2 md:w-auto">
                    @foreach (\App\Services\PreferenciasUsuarioService::DENSIDADES as $valor => $nombre)
                        <label
                            wire:key="pref-densidad-{{ $valor }}"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-colors {{ $tarjeta($densidad === $valor) }}"
                            x-bind:class="{{ $tarjetaAlpine('densidad', $valor) }}"
                        >
                            <input type="radio" x-model="densidad" value="{{ $valor }}" class="sr-only">

                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                @if ($valor === 'compacta')
                                    <path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                @else
                                    <path d="M4 6h16M4 12h16M4 18h16"/>
                                @endif
                            </svg>

                            {{ $nombre }}
                        </label>
                    @endforeach
                </div>
            </div>


            {{-- Vista previa de densidad (mismos tamaños que las tablas) --}}
            <div class="px-4 pb-4 sm:px-5">
                <p class="mb-2 text-[11px] font-medium uppercase tracking-wide text-[var(--theme-text-muted)]">
                    Vista previa
                </p>

                <div class="overflow-x-auto rounded-lg border border-[var(--theme-border)]">
                    <table class="w-full min-w-[420px]" data-vista-previa-densidad>
                        <thead class="bg-[var(--theme-surface-soft)] text-left text-xs text-[var(--theme-text-muted)]">
                            <tr>
                                @foreach (['Código', 'Equipo', 'Estado'] as $columna)
                                    <th @class(['px-4 font-medium', 'py-1.5' => $densidad === 'compacta', 'py-3' => $densidad !== 'compacta']) x-bind:class="{ 'py-1.5': densidad === 'compacta', 'py-3': densidad !== 'compacta' }">{{ $columna }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[var(--theme-border)] text-[var(--theme-text)]">
                            @foreach ([['ACT-000101', 'Laptop Dell Latitude', 'Asignado'], ['ACT-000102', 'Impresora HP LaserJet', 'Disponible'], ['ACT-000103', 'Switch Cisco 24 puertos', 'En mantenimiento']] as $filaEjemplo)
                                <tr>
                                    @foreach ($filaEjemplo as $dato)
                                        <td @class(['px-4', 'py-1 text-xs' => $densidad === 'compacta', 'py-2.5 text-sm' => $densidad !== 'compacta']) x-bind:class="{ 'py-1 text-xs': densidad === 'compacta', 'py-2.5 text-sm': densidad !== 'compacta' }">{{ $dato }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>


    {{-- ====================================================
        ACCESIBILIDAD
        ----------------------------------------------------
        Cada cambio se aplica al instante en esta página como
        vista previa (atributos de <html>). Se conserva al pulsar
        «Guardar preferencias»; si sales sin guardar, se descarta.
    ==================================================== --}}
    @php
        $opcionesAccesibilidad = [
            [
                'modelo' => 'reducirMovimiento',
                'atributo' => 'data-movimiento',
                'activo' => 'reducido',
                'titulo' => 'Reducir animaciones',
                'descripcion' => 'Quita transiciones y efectos de movimiento. Útil si las animaciones te distraen o marean.',
            ],
            [
                'modelo' => 'altoContraste',
                'atributo' => 'data-contraste',
                'activo' => 'alto',
                'titulo' => 'Alto contraste',
                'descripcion' => 'Textos secundarios y bordes más marcados para leer con más facilidad.',
            ],
            [
                'modelo' => 'resaltarFoco',
                'atributo' => 'data-foco',
                'activo' => 'resaltado',
                'titulo' => 'Resaltar el foco del teclado',
                'descripcion' => 'Muestra un contorno grueso en el elemento seleccionado al navegar con la tecla Tab.',
            ],
        ];

        $tamanosTexto = [
            'normal' => ['muestra' => 'text-sm', 'escala' => '100%'],
            'grande' => ['muestra' => 'text-base', 'escala' => '112%'],
            'muy_grande' => ['muestra' => 'text-lg', 'escala' => '125%'],
        ];
    @endphp

    <section
        class="{{ $seccion }}"
        x-data="{
            tamanoTexto: $wire.entangle('tamanoTexto'),
            reducirMovimiento: $wire.entangle('reducirMovimiento'),
            altoContraste: $wire.entangle('altoContraste'),
            resaltarFoco: $wire.entangle('resaltarFoco'),

            /* Vista previa inmediata en toda la página. */
            aplicar() {
                const html = document.documentElement;

                html.setAttribute('data-texto', this.tamanoTexto);
                html.setAttribute('data-movimiento', this.reducirMovimiento ? 'reducido' : 'normal');
                html.setAttribute('data-contraste', this.altoContraste ? 'alto' : 'normal');
                html.setAttribute('data-foco', this.resaltarFoco ? 'resaltado' : 'normal');
            },
        }"
        x-effect="aplicar()"
    >
        <div class="{{ $cabecera }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Accesibilidad</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Ajusta el sistema para leerlo y usarlo con más comodidad. Los cambios se ven al instante.</p>
        </div>

        @unless ($accesibilidadDisponible)
            <p class="border-b border-[var(--theme-border)] bg-[var(--theme-warning-soft)] px-4 py-2.5 text-xs text-[var(--theme-text)] sm:px-5">
                Falta ejecutar la actualización de la base de datos (<code class="font-mono">php artisan migrate</code>) para guardar estas opciones.
            </p>
        @endunless

        <div class="divide-y divide-[var(--theme-border)]">

            {{-- Tamaño del texto --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Tamaño del texto</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        Agranda textos, botones y espacios en todo el sistema.
                    </p>
                </div>

                <div class="grid w-full shrink-0 grid-cols-3 gap-2 md:w-auto" role="radiogroup" aria-label="Tamaño del texto">
                    @foreach (\App\Services\PreferenciasUsuarioService::TAMANOS_TEXTO as $valor => $nombre)
                        <label
                            wire:key="pref-texto-{{ $valor }}"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-colors {{ $tarjeta($tamanoTexto === $valor) }}"
                            x-bind:class="{{ $tarjetaAlpine('tamanoTexto', $valor) }}"
                        >
                            <input type="radio" x-model="tamanoTexto" value="{{ $valor }}" class="sr-only">
                            <span class="{{ $tamanosTexto[$valor]['muestra'] }} font-semibold leading-none" aria-hidden="true">Aa</span>
                            <span class="whitespace-nowrap">{{ $nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Interruptores --}}
            @foreach ($opcionesAccesibilidad as $opcion)
                <div class="{{ $fila }}">
                    <div class="min-w-0">
                        <p id="pref-{{ $opcion['modelo'] }}-titulo" class="text-sm font-medium text-[var(--theme-text)]">{{ $opcion['titulo'] }}</p>
                        <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">{{ $opcion['descripcion'] }}</p>
                    </div>

                    <div class="flex shrink-0 justify-start md:justify-end">
                        <button
                            type="button"
                            role="switch"
                            aria-labelledby="pref-{{ $opcion['modelo'] }}-titulo"
                            aria-checked="{{ $this->{$opcion['modelo']} ? 'true' : 'false' }}"
                            x-bind:aria-checked="{{ $opcion['modelo'] }} ? 'true' : 'false'"
                            x-on:click="{{ $opcion['modelo'] }} = ! {{ $opcion['modelo'] }}"
                            class="inline-flex items-center gap-2.5 rounded-full"
                        >
                            <span
                                class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-150 {{ $this->{$opcion['modelo']} ? 'bg-[var(--theme-primary)]' : 'bg-[var(--theme-border-strong)]' }}"
                                x-bind:class="{ 'bg-[var(--theme-primary)]': {{ $opcion['modelo'] }}, 'bg-[var(--theme-border-strong)]': ! {{ $opcion['modelo'] }} }"
                            >
                                <span
                                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-150 {{ $this->{$opcion['modelo']} ? 'translate-x-5' : 'translate-x-0' }}"
                                    x-bind:class="{ 'translate-x-5': {{ $opcion['modelo'] }}, 'translate-x-0': ! {{ $opcion['modelo'] }} }"
                                ></span>
                            </span>

                            <span
                                class="w-[5.5rem] text-left text-xs text-[var(--theme-text-muted)] md:order-first md:text-right"
                                x-text="{{ $opcion['modelo'] }} ? 'Activado' : 'Desactivado'"
                            >{{ $this->{$opcion['modelo']} ? 'Activado' : 'Desactivado' }}</span>
                        </button>
                    </div>
                </div>
            @endforeach

        </div>
    </section>


    {{-- ====================================================
        INICIO Y LISTADOS
    ==================================================== --}}
    <section class="{{ $seccion }}">
        <div class="{{ $cabecera }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Inicio y listados</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Dónde empiezas y cómo se muestran las tablas.</p>
        </div>

        <div class="divide-y divide-[var(--theme-border)]">

            {{-- Pantalla de inicio --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Pantalla al iniciar sesión</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        La primera pantalla que verás al entrar al sistema.
                    </p>
                </div>

                <div class="{{ $control }}">
                    <x-searchable-select
                        wire-model="pantallaInicio"
                        :options="collect($pantallas)->map(fn ($p, $k) => ['value' => $k, 'label' => $p['label']])->values()->all()"
                        placeholder="Selecciona..."
                        :show-clear="false"
                    />
                    @error('pantallaInicio') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Registros por página --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Registros por página</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        Cantidad inicial de filas en las tablas. Puedes cambiarla en cada tabla cuando quieras.
                    </p>
                </div>

                <div class="{{ $control }}">
                    <x-searchable-select
                        wire-model="registrosPorPagina"
                        :options="collect(\App\Services\PreferenciasUsuarioService::REGISTROS_POR_PAGINA)->map(fn ($n) => ['value' => (string) $n, 'label' => $n . ' registros'])->all()"
                        placeholder="10 registros"
                        :show-clear="false"
                    />
                    @error('registrosPorPagina') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Hotel predeterminado --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">{{ $etiquetas['unidad_singular'] }} predeterminado</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        Inventario se abrirá filtrado por este {{ $unidadSingular }}. Deja «Todos» para ver todos los equipos.
                    </p>
                </div>

                <div class="{{ $control }}">
                    <x-searchable-select
                        wire-model="idUnidadPredeterminada"
                        :options="$unidades"
                        :placeholder="'Todos los ' . mb_strtolower($etiquetas['unidad_plural'])"
                        :show-clear="true"
                        :clear-label="'Todos los ' . mb_strtolower($etiquetas['unidad_plural'])"
                    />
                    @error('idUnidadPredeterminada') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

        </div>
    </section>


    {{-- ====================================================
        FORMATO REGIONAL
    ==================================================== --}}
    <section class="{{ $seccion }}">
        <div class="{{ $cabecera }}">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Formato regional</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Cómo se muestran las fechas y horas registradas.</p>
        </div>

        <div class="divide-y divide-[var(--theme-border)]">

            {{-- Zona horaria --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Zona horaria</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        Hora actual en esta zona:
                        <span class="font-medium text-[var(--theme-text)]">
                            {{ now()->setTimezone($zonaHoraria)->format($formatoHora === '12' ? 'h:i a' : 'H:i') }}
                        </span>
                    </p>
                </div>

                <div class="{{ $control }}">
                    <x-searchable-select
                        wire-model="zonaHoraria"
                        :options="collect(\App\Services\PreferenciasUsuarioService::ZONAS_HORARIAS)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->all()"
                        placeholder="Buscar zona..."
                        :show-clear="false"
                    />
                    @error('zonaHoraria') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Formato de hora --}}
            <div class="{{ $fila }}">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text)]">Formato de hora</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        Se aplica en bitácora, último acceso, historial y reportes.
                    </p>
                </div>

                <div class="grid w-full shrink-0 grid-cols-2 gap-2 md:w-auto">
                    @foreach (\App\Services\PreferenciasUsuarioService::FORMATOS_HORA as $valor => $nombre)
                        <label wire:key="pref-hora-{{ $valor }}" class="flex cursor-pointer items-center justify-center rounded-lg border px-3 py-2 text-xs font-medium whitespace-nowrap transition-colors {{ $tarjeta($formatoHora === (string) $valor) }}">
                            <input type="radio" wire:model.live="formatoHora" value="{{ $valor }}" class="sr-only">
                            {{ $nombre }}
                        </label>
                    @endforeach
                </div>
            </div>

        </div>
    </section>


    {{-- ====================================================
        GUARDAR
    ==================================================== --}}
    <div class="flex justify-end">
        <x-ui.button
            wire:click="save"
            target="save"
            loading-text="Guardando…"
            class="w-full sm:w-auto"
        >
            Guardar preferencias
        </x-ui.button>
    </div>

</div>
