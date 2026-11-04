{{-- ============================================================
    IMPORTAR EQUIPOS DESDE CSV (App\Livewire\EquiposImportar)
============================================================ --}}
@php
    $tarjeta = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    /*
     * Dos versiones (casilla «Actualizar…» apagada / encendida): se cambia
     * entre ellas en el navegador, sin esperar al servidor.
     */
    $textoBoton = function (int $nuevas, int $actualizar): string {
        $total = $nuevas + $actualizar;
        $texto = collect([$nuevas ? 'importar '.$nuevas : null, $actualizar ? 'actualizar '.$actualizar : null])->filter()->implode(' y ');

        return ucfirst($texto).($total === 1 ? ' equipo' : ' equipos');
    };
    $modos = [
        'sin' => ['listas' => $cuentas['nuevas'], 'errores' => $cuentas['sinActualizar']['errores'], 'boton' => $textoBoton($cuentas['nuevas'], 0)],
        'con' => [
            'listas' => $cuentas['nuevas'] + $cuentas['conActualizar']['actualizar'],
            'errores' => $cuentas['conActualizar']['errores'],
            'sinCambios' => $cuentas['conActualizar']['sinCambios'],
            'boton' => $textoBoton($cuentas['nuevas'], $cuentas['conActualizar']['actualizar']),
        ],
    ];
    $listas = $actualizarExistentes ? $modos['con']['listas'] : $modos['sin']['listas'];
@endphp

<div
    class="space-y-5"
    data-listas-sin="{{ $modos['sin']['listas'] }}"
    data-listas-con="{{ $modos['con']['listas'] }}"
    x-on:dragenter.window="if (enContenido($event)) { profundidad++; encima = true; }"
    x-on:dragleave.window="profundidad = Math.max(0, profundidad - 1); if (profundidad === 0) encima = false"
    x-on:dragover.window="if (Array.from($event.dataTransfer?.types ?? []).includes('Files')) { $event.preventDefault(); $event.dataTransfer.dropEffect = enContenido($event) ? 'copy' : 'none'; }"
    x-on:drop.window="if (Array.from($event.dataTransfer?.types ?? []).includes('Files')) { $event.preventDefault(); profundidad = 0; encima = false; if (enContenido($event)) subir($event.dataTransfer.files?.[0]); }"
    x-data="{
        actualizar: @js($actualizarExistentes),
        revisando: 0,
        revisadas: 0,

        /*
         * Tras crear catálogos o corregir valores, el servidor revisa las
         * filas afectadas de 25 en 25: los conteos bajan mientras tanto.
         */
        async revisarTras(accion) {
            if (this.revisando || this.corriendo) return null;
            this.revisando = 1;
            this.revisadas = 0;

            try {
                const r = await accion();
                const total = Number(r?.porRevisar || 0);
                let pendientes = total;
                this.revisando = Math.max(total, 1);

                while (pendientes > 0) {
                    const x = await $wire.revisarPendientes();
                    pendientes = Number(x?.pendientes || 0);
                    this.revisadas = total - pendientes;
                }

                return r;
            } catch (error) {
                window.trackitToast?.('error', window.trackitMensajeError(error, 'revisar las filas'), 'Error');

                return null;
            } finally {
                this.revisando = 0;
            }
        },
        avance: 0,
        contados: 0,
        totalImportar: 0,
        restante: '',
        corriendo: false,
        encima: false,
        profundidad: 0,
        subiendo: false,

        /*
         * Se puede soltar en cualquier parte del contenido; el menú
         * lateral, el encabezado y el menú inferior no cuentan.
         */
        enContenido(evento) {
            const archivo = Array.from(evento.dataTransfer?.types ?? []).includes('Files');
            const enMenu = evento.target instanceof Element
                && evento.target.closest('aside, header, nav, #mobileAppHeader, .mi-barra');

            return archivo && ! enMenu;
        },
        progreso: 0,

        // Archivo que llegó desde Inventario (arrastrado o elegido en «Añadir»).
        init() {
            const archivo = window.__trackitArchivoImportar;
            window.__trackitArchivoImportar = null;

            if (archivo) this.subir(archivo);
        },

        subir(archivo) {
            if (! archivo || this.subiendo || this.corriendo) return;

            if (! /\.(xlsx|csv)$/i.test(archivo.name)) {
                window.trackitToast?.('error', 'Solo se aceptan archivos Excel (.xlsx) o CSV.', 'Archivo no válido');
                return;
            }

            this.subiendo = true;
            this.progreso = 0;

            $wire.upload(
                'archivo',
                archivo,
                () => { this.subiendo = false; },
                () => { this.subiendo = false; window.trackitToast?.('error', 'No se pudo subir el archivo (máximo 4 MB).', 'Archivo no válido'); },
                (evento) => { this.progreso = evento.detail.progress; }
            );
        },

        async importar() {
            if (this.corriendo) return;
            this.corriendo = true;
            const total = Number((this.actualizar ? this.$root.dataset.listasCon : this.$root.dataset.listasSin) || 0);
            if (total === 0) { this.corriendo = false; return; }
            let aviso = window.trackitToast?.('loading', 'Guardando los equipos del archivo…', 'Importando') ?? null;

            try {
                let resultado = { pendientes: total };

                // Lotes pequeños (hasta 10): la barra avanza aunque sean pocos equipos.
                const porLote = Math.max(1, Math.min(10, Math.ceil(total / 10)));
                this.avance = 2;

                // Medición: tiempo total (navegador) y del servidor por lote.
                const inicio = performance.now();
                let msServidor = 0;
                let loteMasLento = 0;

                /*
                 * Contador de 1 en 1: entre lote y lote avanza al ritmo real
                 * medido (ms por equipo), sin pasar del lote que se está
                 * guardando; al volver cada lote se ajusta al número real.
                 */
                let confirmados = 0;
                let finLote = inicio;
                let msPorEquipo = 1200;
                this.contados = 0;
                this.totalImportar = total;

                const pintar = () => {
                    if (total <= 0) return;
                    const enCurso = Math.min(total, confirmados + porLote) - 1;
                    const estimado = confirmados + Math.floor((performance.now() - finLote) / msPorEquipo);
                    const nuevo = Math.max(this.contados, Math.min(Math.max(confirmados, enCurso), estimado), confirmados);

                    if (nuevo === this.contados && this.avance > 2) return;
                    this.contados = nuevo;
                    this.avance = Math.max(2, Math.round((nuevo / total) * 100));

                    // ETA: con el ritmo real (tras el primer lote confirmado).
                    if (confirmados > 0) {
                        const s = Math.max(1, Math.round(((total - nuevo) * msPorEquipo) / 1000));
                        this.restante = nuevo >= total ? '' : 'quedan ≈ ' + (s < 60 ? s + ' s' : Math.floor(s / 60) + ' min ' + String(s % 60).padStart(2, '0') + ' s');
                    } else {
                        this.restante = 'calculando tiempo…';
                    }

                    if (aviso) window.trackitToastActualizar?.(aviso, 'loading', nuevo + ' de ' + total + ' equipos guardados' + (this.restante ? ' · ' + this.restante : '…'), 'Importando');
                };

                const reloj = setInterval(pintar, 60);

                try {
                    while (resultado.pendientes > 0) {
                        resultado = await $wire.importarLote(porLote, this.actualizar);
                        msServidor += Number(resultado.ms || 0);
                        loteMasLento = Math.max(loteMasLento, Number(resultado.ms || 0));
                        confirmados = total - resultado.pendientes;
                        finLote = performance.now();
                        if (confirmados > 0) msPorEquipo = Math.max(30, (finLote - inicio) / confirmados);
                        pintar();
                    }
                } finally {
                    clearInterval(reloj);
                }

                const segundos = (performance.now() - inicio) / 1000;
                const hechos = Number(resultado.importadas || 0) + Number(resultado.actualizadas || 0);
                const porEquipo = hechos > 0 ? segundos / hechos : 0;
                const medicion = hechos + ' equipos en ' + segundos.toFixed(1) + ' s (' + porEquipo.toFixed(2) + ' s por equipo; servidor ' + (msServidor / 1000).toFixed(1) + ' s; lote más lento ' + (loteMasLento / 1000).toFixed(1) + ' s).';
                console.info('[Track-it] Importación: ' + medicion);

                if (aviso) window.trackitToastActualizar?.(aviso, 'success', medicion, 'Importación terminada');
            } catch (error) {
                if (aviso) window.trackitToastActualizar?.(aviso, 'error', window.trackitMensajeError(error, 'importar los equipos'), 'Error al importar');
            } finally {
                this.corriendo = false;
            }
        },
    }"
>
    {{-- ========================================================
        1. ARCHIVO (mismo diseño que las secciones de los formularios)
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Archivo</h2>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Plantilla de un tipo, plantilla universal (Excel por hojas o CSV) o un CSV exportado del Inventario.
                </p>
            </div>
        </div>

        <label
            for="importar-archivo"
            x-bind:class="encima ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]' : 'border-[var(--theme-border-strong)] hover:border-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft-subtle)]'"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition-colors"
        >
            <span x-show="! subiendo" class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M5 21h14"/></svg>
            </span>

            {{-- Subida (porcentaje real) y después revisión de filas en el servidor. --}}
            <div x-show="subiendo" x-cloak class="w-full max-w-xs">
                <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div
                        class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300"
                        x-bind:class="progreso >= 100 && 'animate-pulse'"
                        x-bind:style="{ width: Math.max(4, progreso) + '%' }"
                    ></div>
                </div>
            </div>

            <span class="text-sm font-medium text-[var(--theme-text-strong)]" x-text="subiendo ? (progreso < 100 ? 'Subiendo el archivo… ' + progreso + '%' : 'Revisando las filas del archivo…') : 'Suelta tu Excel o CSV aquí, o haz clic para elegirlo'">Suelta tu Excel o CSV aquí, o haz clic para elegirlo</span>
            <span class="text-xs text-[var(--theme-text-muted)]">.xlsx o .csv · hasta 4 MB y 500 equipos</span>

            <input
                id="importar-archivo"
                type="file"
                class="sr-only"
                accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                x-on:change="subir($event.target.files?.[0]); $event.target.value = ''"
                x-bind:disabled="corriendo || subiendo"
            >
        </label>

        @error('archivo') <p class="mt-2 text-center text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

        {{-- Cómo llenar el archivo --}}
        <div class="mt-4 grid grid-cols-1 gap-2 text-xs text-[var(--theme-text-muted)] sm:grid-cols-2">
            @foreach ([
                'No cambies los encabezados. Las columnas con * son obligatorias.',
                'Escribe las listas con su texto, como aparecen en el sistema (Estado «Disponible», Marca «Dell»…).',
                'Fechas en dd/mm/aaaa y Sí/No como «Sí» o «No». El código de inventario se asigna solo.',
                'Las filas con errores no se importan: corrígelas aquí mismo o en el archivo.',
            ] as $consejo)
                <p class="flex items-start gap-2 rounded-lg bg-[var(--theme-surface-soft)] px-3 py-2">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ $consejo }}</span>
                </p>
            @endforeach
        </div>
    </section>

    {{-- RESULTADO DE LA ÚLTIMA IMPORTACIÓN --}}
    @if (($ultimoResultado['importadas'] ?? 0) + ($ultimoResultado['actualizadas'] ?? 0) > 0)
        <div role="status" class="flex flex-col gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: inset 3px 0 var(--theme-success);">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-success-soft)] text-[var(--theme-success)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <p class="text-sm text-[var(--theme-text)]">
                    <strong class="font-semibold text-[var(--theme-text-strong)]">
                        {{ collect([
                            ($ultimoResultado['importadas'] ?? 0) ? $ultimoResultado['importadas'].(($ultimoResultado['importadas'] ?? 0) === 1 ? ' equipo importado' : ' equipos importados') : null,
                            ($ultimoResultado['actualizadas'] ?? 0) ? $ultimoResultado['actualizadas'].(($ultimoResultado['actualizadas'] ?? 0) === 1 ? ' equipo actualizado' : ' equipos actualizados') : null,
                        ])->filter()->implode(' y ') }}.
                    </strong>
                    @if (! empty($ultimoResultado['codigos']))
                        <span class="block text-xs text-[var(--theme-text-muted)]">{{ collect($ultimoResultado['codigos'])->take(6)->implode(', ') }}{{ count($ultimoResultado['codigos']) > 6 ? '…' : '' }}</span>
                    @endif
                    @if ($cuentas['total'] > 0)
                        <span class="block text-xs text-[var(--theme-text-muted)]">Abajo quedan las filas que no se guardaron; corrígelas aquí o en el archivo.</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('equipos.index') }}" wire:navigate.hover class="inline-flex h-9 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">Ver en Inventario</a>
        </div>
    @endif

    {{-- ========================================================
        2. VISTA PREVIA
        Barra con el archivo y las acciones, métricas y filas: el
        mismo orden que los apartados de Administración.
    ======================================================== --}}
    @if ($cuentas['total'] > 0)
        <x-admin.filter-bar>
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[var(--theme-text-strong)]">{{ $nombreArchivo }}</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">{{ number_format($cuentas['total']) }} {{ $cuentas['total'] === 1 ? 'fila' : 'filas' }} en el archivo</p>

                    {{-- Equipos que ya existen: actualizar con lo que cambió --}}
                    @if ($cuentas['existentes'] > 0)
                        <label class="mt-3 flex cursor-pointer items-start gap-2 text-sm text-[var(--theme-text)]">
                            <input
                                type="checkbox"
                                x-model="actualizar"
                                x-bind:disabled="corriendo"
                                class="mt-0.5 h-4 w-4 rounded border-[var(--theme-border-strong)] accent-[var(--theme-primary)]"
                            >
                            <span>
                                <span class="font-medium text-[var(--theme-text-strong)]">Actualizar los equipos que ya existen</span>
                                <span class="block text-xs text-[var(--theme-text-muted)]">
                                    Se reconocen por su código de inventario (o su número de serie). Solo se cambian las celdas con valor; una celda vacía conserva el dato actual.
                                    @if ($modos['con']['sinCambios'])
                                        <span x-show="actualizar" @unless ($actualizarExistentes) x-cloak @endunless>{{ $modos['con']['sinCambios'] }} {{ $modos['con']['sinCambios'] === 1 ? 'fila no tiene' : 'filas no tienen' }} cambios.</span>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button variant="secondary" wire:click="descartar" x-disabled="corriendo">
                    {{ $terminado ? 'Importar otro archivo' : 'Descartar' }}
                </x-ui.button>

                @if (! $terminado && $modos['sin']['listas'] + $modos['con']['listas'] > 0)
                    <x-ui.button
                        x-show="(actualizar ? {{ $modos['con']['listas'] }} : {{ $modos['sin']['listas'] }}) > 0"
                        x-on:click="importar()"
                        x-loading="corriendo"
                        x-disabled="corriendo || revisando > 0"
                        loading-text="Guardando…"
                    >
                        <span x-text="actualizar ? @js($modos['con']['boton']) : @js($modos['sin']['boton'])">{{ $actualizarExistentes ? $modos['con']['boton'] : $modos['sin']['boton'] }}</span>
                    </x-ui.button>
                @endif

                @if ($terminado)
                    <a href="{{ route('equipos.index') }}" wire:navigate.hover class="inline-flex h-10 items-center rounded-[var(--radius-md)] bg-[var(--theme-primary)] px-4 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)]">Ir a Inventario</a>
                @endif
            </x-slot:actions>
        </x-admin.filter-bar>

        {{-- Métricas: una versión por estado de la casilla «Actualizar…» --}}
        @foreach ($modos as $modo => $m)
            <div x-show="{{ $modo === 'con' ? 'actualizar' : '! actualizar' }}" @if ($modo === 'con' && ! $actualizarExistentes || $modo === 'sin' && $actualizarExistentes) x-cloak @endif>
                <x-admin.metrics :items="array_values(array_filter([
                    ['label' => $modo === 'con' ? 'Por guardar (nuevas y actualizaciones)' : 'Listas para importar', 'value' => $m['listas']],
                    ['label' => 'Con errores', 'value' => $m['errores']],
                    $cuentas['existentes'] ? ['label' => $modo === 'con' ? 'Ya existen · '.($m['sinCambios'] ?? 0).' sin cambios' : 'Ya existen en el inventario', 'value' => $cuentas['existentes']] : null,
                    ['label' => 'Filas en el archivo', 'value' => $cuentas['total']],
                ]))" />
            </div>
        @endforeach

        {{-- Avances, catálogos faltantes y filas (sin tarjeta: como las tablas del sistema) --}}
        <div>
            {{-- Revisión de filas tras crear catálogos o corregir valores --}}
            <div x-show="revisando" x-cloak class="mt-4" role="status">
                <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:style="{ width: (revisando > 1 ? Math.max(4, Math.round(revisadas / revisando * 100)) : 30) + '%' }"></div>
                </div>
                <p class="mt-1.5 text-[11px] tabular-nums text-[var(--theme-text-muted)]" x-text="revisando > 1 ? 'Revisando de nuevo las filas… ' + revisadas + ' de ' + revisando : 'Aplicando cambios…'"></p>
            </div>

            {{-- Barra de avance --}}
            <div x-show="corriendo" x-cloak class="mt-4">
                <div class="h-2 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:style="{ width: avance + '%' }"></div>
                </div>
                <p class="mt-1.5 flex justify-between text-[11px] tabular-nums text-[var(--theme-text-muted)]">
                    <span x-text="contados + ' de ' + totalImportar + ' equipos'"></span>
                    <span x-text="restante"></span>
                </p>
            </div>

            {{-- Marcas y modelos que no existen en el catálogo --}}
            @if ($faltantes !== [])
                @php
                    $filasAfectadas = collect($filas)->filter(fn ($f) => $f['estado'] === 'error' && ! empty($f['faltan']))->count();
                    $modelosFaltan = collect($faltantes)->where('clase', 'modelo')->count();
                    $marcasFaltan = collect($faltantes)->where('clase', 'marca')->count();
                    $opcionesFaltan = collect($faltantes)->where('clase', 'opcion')->count();
                    $resumenFaltan = collect([
                        $opcionesFaltan ? $opcionesFaltan.($opcionesFaltan === 1 ? ' opción de lista' : ' opciones de lista') : null,
                        $marcasFaltan ? $marcasFaltan.($marcasFaltan === 1 ? ' marca' : ' marcas') : null,
                        $modelosFaltan ? $modelosFaltan.($modelosFaltan === 1 ? ' modelo' : ' modelos') : null,
                    ])->filter()->implode(' y ');
                @endphp
                <div
                    wire:key="faltantes-{{ md5(collect($faltantes)->pluck('clave')->implode(',')) }}"
                    x-show="! corriendo"
                    x-data="{
                        seleccion: @js(collect($faltantes)->whereNull('sugerencia')->pluck('clave')->values()),
                        creando: false,
                        async crear() {
                            if (this.creando || this.seleccion.length === 0) return;
                            this.creando = true;
                            try {
                                const r = await this.revisarTras(() => $wire.crearCatalogos(this.seleccion));
                                if (! r) return;
                                const partes = [];
                                if (r.marcas) partes.push(r.marcas + (r.marcas === 1 ? ' marca' : ' marcas'));
                                if (r.modelos) partes.push(r.modelos + (r.modelos === 1 ? ' modelo' : ' modelos'));
                                if (r.opciones) partes.push(r.opciones + (r.opciones === 1 ? ' opción' : ' opciones'));
                                window.trackitToast?.('success', partes.length ? 'Se crearon ' + partes.join(' y ') + '. Las filas se revisaron de nuevo.' : 'Ya existían; las filas se revisaron de nuevo.', 'Catálogo actualizado');
                            } catch (error) {
                                window.trackitToast?.('error', window.trackitMensajeError(error, 'crear las marcas y modelos'), 'Error');
                            } finally {
                                this.creando = false;
                            }
                        },
                    }"
                    class="mt-4 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-warning-soft)] p-4"
                >
                    <p class="text-sm font-semibold text-[var(--theme-text-strong)]">
                        Faltan {{ $resumenFaltan }} en el catálogo
                        <span class="font-normal text-[var(--theme-text-muted)]">(afectan {{ $filasAfectadas }} {{ $filasAfectadas === 1 ? 'fila' : 'filas' }})</span>
                    </p>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        Marca los que quieras crear. Se crean solo con su nombre, marca y tipo de equipo; el resto de sus datos se completa después en Administración.
                    </p>

                    <ul class="mt-3 space-y-2">
                        @foreach ($faltantes as $falta)
                            <li wire:key="falta-{{ md5($falta['clave']) }}" class="flex flex-col gap-1.5 rounded-md bg-[var(--theme-surface)] px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                <label class="flex cursor-pointer items-start gap-2 text-sm text-[var(--theme-text)]">
                                    <input type="checkbox" value="{{ $falta['clave'] }}" x-model="seleccion" class="mt-0.5 h-4 w-4 rounded border-[var(--theme-border-strong)] accent-[var(--theme-primary)]">
                                    <span>
                                        @if ($falta['clase'] === 'opcion')
                                            <span class="font-medium text-[var(--theme-text-strong)]">Opción «{{ $falta['valor'] }}»</span>
                                            <span class="text-[var(--theme-text-muted)]">— {{ $falta['columna'] }} · {{ $falta['tipoNombre'] }}</span>
                                        @elseif ($falta['clase'] === 'marca')
                                            <span class="font-medium text-[var(--theme-text-strong)]">Marca «{{ $falta['marca'] }}»</span>
                                        @else
                                            <span class="font-medium text-[var(--theme-text-strong)]">Modelo «{{ $falta['modelo'] }}»</span>
                                            <span class="text-[var(--theme-text-muted)]">— {{ $falta['marca'] }} · {{ $falta['tipoNombre'] }}</span>
                                        @endif
                                        <span class="text-xs text-[var(--theme-text-muted)]">· {{ $falta['filas'] }} {{ $falta['filas'] === 1 ? 'fila' : 'filas' }}</span>
                                        @if ($falta['sugerencia'])
                                            <span class="block text-xs text-[var(--theme-text-muted)]">Márcalo solo si es distinto al que ya tienes; se creará como nuevo.</span>
                                        @endif
                                        @if ($falta['clase'] === 'modelo' && $falta['marcaNueva'])
                                            <span class="block text-xs text-[var(--theme-text-muted)]">Se crea solo si también se crea su marca.</span>
                                        @endif
                                    </span>
                                </label>

                                @if ($falta['sugerencia'])
                                    {{--
                                        Parecido a uno que ya existe: «Usar el existente» cambia el
                                        nombre en las filas (no crea nada); marcar la casilla lo crea
                                        como nuevo.
                                    --}}
                                    <div class="flex flex-wrap items-center gap-2 pl-6 text-xs sm:justify-end sm:pl-0">
                                        <span class="text-[var(--theme-text-muted)]">
                                            Ya tienes {{ $falta['clase'] === 'opcion' ? 'la opción' : ($falta['clase'] === 'marca' ? 'la marca' : 'el modelo') }}
                                            <span class="font-medium text-[var(--theme-text-strong)]">«{{ $falta['sugerencia'] }}»</span>
                                        </span>
                                        <button
                                            type="button"
                                            x-on:click="revisarTras(() => $wire.usarSugerencia(@js($falta['clave'])))"
                                            x-bind:disabled="revisando > 0 || corriendo"
                                            title="Cambia «{{ $falta['clase'] === 'opcion' ? $falta['valor'] : ($falta['clase'] === 'marca' ? $falta['marca'] : $falta['modelo']) }}» por «{{ $falta['sugerencia'] }}» en {{ $falta['filas'] === 1 ? 'la fila' : 'las '.$falta['filas'].' filas' }}, sin crear nada nuevo"
                                            class="h-8 shrink-0 rounded-md border border-[var(--theme-primary)] px-3 font-medium text-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft)] disabled:opacity-50"
                                        >Usar el existente</button>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <button
                            type="button"
                            x-on:click="crear()"
                            x-bind:disabled="creando || seleccion.length === 0"
                            class="h-10 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:opacity-60"
                            x-text="creando ? 'Creando y revisando…' : 'Crear ' + seleccion.length + ' y volver a revisar'"
                        >Crear y volver a revisar</button>
                        @if ($listas > 0)
                            <span class="text-xs text-[var(--theme-text-muted)]">O importa solo las {{ $listas }} listas con el botón de arriba.</span>
                        @endif
                    </div>
                </div>
            @endif

            {{--
                Filas: tabla en escritorio y tarjetas en móvil (mismo marcado;
                la rejilla solo se activa desde md).
            --}}
            @php
                $columnasFila = 'lg:grid-cols-[130px_130px_minmax(0,1fr)_160px_minmax(0,1.7fr)]';
            @endphp

            <div class="mt-4 lg:overflow-hidden lg:rounded-lg lg:border lg:border-[var(--theme-border)] lg:bg-[var(--theme-surface)]">
                <div class="hidden h-11 items-center border-b border-[var(--theme-border)] text-xs font-medium text-[var(--theme-text-muted)] lg:grid {{ $columnasFila }}">
                    <div class="px-4" title="Número de fila en tu archivo (la fila 1 son los encabezados)">Fila en el archivo</div>
                    <div class="px-4">Tipo</div>
                    <div class="px-4">Nombre</div>
                    <div class="px-4">Número de serie</div>
                    <div class="px-4">Resultado</div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:block lg:divide-y lg:divide-[var(--theme-border)]">
                    @foreach ($filas as $fila)
                        <div
                            wire:key="importar-fila-{{ $fila['fila'] }}"
                            class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 text-sm shadow-sm transition-colors lg:grid lg:items-start lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:text-xs lg:shadow-none lg:hover:bg-[var(--theme-primary-soft-subtle)] 2xl:text-sm {{ $columnasFila }}"
                        >
                            {{-- Móvil: «Fila 2 · Access point» en una línea --}}
                            <div class="flex items-center gap-1.5 text-xs text-[var(--theme-text-muted)] lg:contents">
                                <span class="lg:px-4 lg:py-3 lg:text-xs 2xl:text-sm">
                                    @if (! empty($fila['etiqueta']))
                                        {{-- Excel por hojas: «Laptop · fila 3» --}}
                                        {{ $fila['etiqueta'] }}
                                    @else
                                        <span class="lg:hidden">Fila </span>{{ $fila['fila'] }}
                                    @endif
                                </span>
                                <span class="lg:hidden" aria-hidden="true">·</span>
                                <span class="lg:px-4 lg:py-3 lg:text-xs 2xl:text-sm lg:text-[var(--theme-text)]">{{ $fila['tipo'] ?: '—' }}</span>
                            </div>

                            <div class="mt-1 break-words font-medium text-[var(--theme-text-strong)] lg:mt-0 lg:px-4 lg:py-3">{{ $fila['nombre'] ?: '—' }}</div>

                            <div class="break-words text-xs text-[var(--theme-text)] lg:px-4 lg:py-3 lg:text-xs 2xl:text-sm">
                                <span class="text-[var(--theme-text-muted)] lg:hidden">Serie: </span>{{ $fila['serie'] ?: '—' }}
                            </div>

                            <div class="mt-2 min-w-0 border-t border-[var(--theme-border)] pt-2 lg:mt-0 lg:border-t-0 lg:px-4 lg:py-3">
                                @if (isset($fila['actualizando'], $fila['sinActualizar']) && ! in_array($fila['estado'], ['importada', 'actualizada', 'fallo'], true))
                                    <div x-show="! actualizar" @if ($actualizarExistentes) x-cloak @endif>
                                        @include('livewire.partials.importar-resultado', ['r' => array_merge($fila, $fila['sinActualizar'])])
                                    </div>
                                    <div x-show="actualizar" @unless ($actualizarExistentes) x-cloak @endunless>
                                        @include('livewire.partials.importar-resultado', ['r' => array_merge($fila, $fila['actualizando'])])
                                    </div>
                                @else
                                    @include('livewire.partials.importar-resultado', ['r' => $fila])
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
