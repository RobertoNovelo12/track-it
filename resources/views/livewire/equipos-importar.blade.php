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
    {{-- 1. ARCHIVO: arrastrar y soltar o elegir (Excel o CSV) --}}
    <section class="{{ $tarjeta }}">
        <label
            for="importar-archivo"
            x-bind:class="encima ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]' : 'border-[var(--theme-border-strong)] hover:border-[var(--theme-primary)]'"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-10 text-center transition-colors"
        >
            <svg x-show="! subiendo" class="h-8 w-8 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M5 21h14"/></svg>

            {{-- Subida (porcentaje real) y después revisión de filas en el servidor. --}}
            <div x-show="subiendo" x-cloak class="w-full max-w-xs">
                <div class="h-2 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div
                        class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300"
                        x-bind:class="progreso >= 100 && 'animate-pulse'"
                        x-bind:style="{ width: Math.max(4, progreso) + '%' }"
                    ></div>
                </div>
            </div>
            <span class="text-sm font-medium text-[var(--theme-text-strong)]" x-text="subiendo ? (progreso < 100 ? 'Subiendo el archivo… ' + progreso + '%' : 'Revisando las filas del archivo…') : 'Suelta tu Excel o CSV en cualquier parte, o haz clic para elegirlo'">Suelta tu Excel o CSV en cualquier parte, o haz clic para elegirlo</span>
            <span class="text-xs text-[var(--theme-text-muted)]">
                Usa la plantilla del tipo de equipo (Administración › Equipos › ⋮ › Descargar plantilla Excel), la plantilla universal para varios tipos a la vez o un CSV exportado del Inventario.
            </span>
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

        <ul class="mt-4 list-disc space-y-1 pl-5 text-xs text-[var(--theme-text-muted)]">
            <li>La primera fila son los encabezados; no cambies sus nombres. Las columnas con * son obligatorias.</li>
            <li>Escribe las listas con su texto, como aparecen en el sistema (Estado «Disponible», Marca «Dell», Hotel…).</li>
            <li>Fechas en dd/mm/aaaa. Sí/No como «Sí» o «No». El código de inventario se asigna solo.</li>
            <li>Hasta 500 equipos por archivo. Las filas con errores no se importan; corrígelas y vuelve a subir solo esas.</li>
        </ul>
    </section>

    {{-- RESULTADO DE LA ÚLTIMA IMPORTACIÓN --}}
    @if (($ultimoResultado['importadas'] ?? 0) + ($ultimoResultado['actualizadas'] ?? 0) > 0)
        <div role="status" class="flex flex-col gap-3 rounded-xl border border-[var(--theme-success-border,var(--theme-border))] bg-[var(--theme-success-soft)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-[var(--theme-text)]">
                <strong class="font-semibold text-[var(--theme-success)]">
                    {{ collect([
                        ($ultimoResultado['importadas'] ?? 0) ? $ultimoResultado['importadas'].(($ultimoResultado['importadas'] ?? 0) === 1 ? ' equipo importado' : ' equipos importados') : null,
                        ($ultimoResultado['actualizadas'] ?? 0) ? $ultimoResultado['actualizadas'].(($ultimoResultado['actualizadas'] ?? 0) === 1 ? ' equipo actualizado' : ' equipos actualizados') : null,
                    ])->filter()->implode(' y ') }}.
                </strong>
                @if (! empty($ultimoResultado['codigos']))
                    <span class="text-[var(--theme-text-muted)]">{{ collect($ultimoResultado['codigos'])->take(6)->implode(', ') }}{{ count($ultimoResultado['codigos']) > 6 ? '…' : '' }}</span>
                @endif
                @if ($cuentas['total'] > 0)
                    <span class="block text-xs text-[var(--theme-text-muted)]">Abajo quedan las filas que no se importaron; corrígelas y vuelve a subir el archivo.</span>
                @endif
            </p>
            <a href="{{ route('equipos.index') }}" wire:navigate class="inline-flex h-9 shrink-0 items-center justify-center rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)]">Ver en Inventario</a>
        </div>
    @endif

    {{-- 2. VISTA PREVIA --}}
    @if ($cuentas['total'] > 0)
        <section class="{{ $tarjeta }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">{{ $nombreArchivo }}</h2>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        {{ $cuentas['total'] }} filas ·
                        @foreach ($modos as $modo => $m)
                            <span x-show="{{ $modo === 'con' ? 'actualizar' : '! actualizar' }}" @if ($modo === 'con' && ! $actualizarExistentes || $modo === 'sin' && $actualizarExistentes) x-cloak @endif>
                                <span class="text-[var(--theme-success)]">{{ $m['listas'] + $cuentas['importadas'] }} {{ $modo === 'con' ? 'por aplicar' : 'sin errores' }}</span> ·
                                <span class="{{ $m['errores'] ? 'text-[var(--theme-danger)]' : '' }}">{{ $m['errores'] }} con errores</span>
                            </span>
                        @endforeach
                        @if ($cuentas['importadas']) · {{ $cuentas['importadas'] }} importadas @endif
                        @if ($cuentas['existentes']) · {{ $cuentas['existentes'] }} ya existen @endif
                    </p>

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

                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="descartar"
                        x-bind:disabled="corriendo"
                        class="h-10 rounded-md border border-[var(--theme-border-strong)] px-4 text-sm font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)] disabled:opacity-50"
                    >{{ $terminado ? 'Importar otro archivo' : 'Descartar' }}</button>

                    @if (! $terminado && $modos['sin']['listas'] + $modos['con']['listas'] > 0)
                        <button
                            x-show="(actualizar ? {{ $modos['con']['listas'] }} : {{ $modos['sin']['listas'] }}) > 0"
                            type="button"
                            x-on:click="importar()"
                            x-bind:disabled="corriendo || revisando"
                            class="h-10 rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)] disabled:opacity-70"
                            x-text="corriendo ? 'Guardando… ' + contados + ' de ' + totalImportar : (actualizar ? @js($modos['con']['boton']) : @js($modos['sin']['boton']))"
                        >Importar</button>
                    @endif

                    @if ($terminado)
                        <a href="{{ route('equipos.index') }}" wire:navigate class="inline-flex h-10 items-center rounded-md bg-[var(--theme-primary)] px-4 text-sm font-medium text-white hover:bg-[var(--theme-primary-hover)]">Ir a Inventario</a>
                    @endif
                </div>
            </div>

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
                                        @if ($falta['clase'] === 'modelo' && $falta['marcaNueva'])
                                            <span class="block text-xs text-[var(--theme-text-muted)]">Se crea solo si también se crea su marca.</span>
                                        @endif
                                    </span>
                                </label>

                                @if ($falta['sugerencia'])
                                    <div class="flex items-center gap-2 pl-6 text-xs sm:pl-0">
                                        <span class="text-[var(--theme-warning)]">¿Quisiste decir «{{ $falta['sugerencia'] }}»?</span>
                                        <button
                                            type="button"
                                            x-on:click="revisarTras(() => $wire.usarSugerencia(@js($falta['clave'])))"
                                            x-bind:disabled="revisando"
                                            wire:loading.attr="disabled"
                                            class="h-8 shrink-0 rounded-md border border-[var(--theme-border-strong)] px-3 font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)] disabled:opacity-50"
                                        >Usar «{{ $falta['sugerencia'] }}»</button>
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
                $columnasFila = 'md:grid-cols-[120px_130px_minmax(0,1fr)_150px_minmax(0,1.6fr)]';
            @endphp

            <div class="mt-4 md:overflow-hidden md:rounded-lg md:border md:border-[var(--theme-border)]">
                <div class="hidden bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)] md:grid {{ $columnasFila }}">
                    <div class="px-3 py-2 font-medium" title="Número de fila en tu archivo (la fila 1 son los encabezados)">Fila en el archivo</div>
                    <div class="px-3 py-2 font-medium">Tipo</div>
                    <div class="px-3 py-2 font-medium">Nombre</div>
                    <div class="px-3 py-2 font-medium">Número de serie</div>
                    <div class="px-3 py-2 font-medium">Resultado</div>
                </div>

                <div class="space-y-2 md:space-y-0 md:divide-y md:divide-[var(--theme-border)]">
                    @foreach ($filas as $fila)
                        <div
                            wire:key="importar-fila-{{ $fila['fila'] }}"
                            class="rounded-lg border border-[var(--theme-border)] bg-[var(--theme-surface)] p-3 text-sm md:grid md:items-start md:rounded-none md:border-0 md:bg-transparent md:p-0 {{ $columnasFila }}"
                        >
                            {{-- Móvil: «Fila 2 · Access point» en una línea --}}
                            <div class="flex items-center gap-1.5 text-xs text-[var(--theme-text-muted)] md:contents">
                                <span class="md:px-3 md:py-2 md:text-sm">
                                    @if (! empty($fila['etiqueta']))
                                        {{-- Excel por hojas: «Laptop · fila 3» --}}
                                        {{ $fila['etiqueta'] }}
                                    @else
                                        <span class="md:hidden">Fila </span>{{ $fila['fila'] }}
                                    @endif
                                </span>
                                <span class="md:hidden" aria-hidden="true">·</span>
                                <span class="md:px-3 md:py-2 md:text-sm md:text-[var(--theme-text)]">{{ $fila['tipo'] ?: '—' }}</span>
                            </div>

                            <div class="mt-1 break-words font-medium text-[var(--theme-text-strong)] md:mt-0 md:px-3 md:py-2">{{ $fila['nombre'] ?: '—' }}</div>

                            <div class="break-words text-xs text-[var(--theme-text)] md:px-3 md:py-2 md:text-sm">
                                <span class="text-[var(--theme-text-muted)] md:hidden">Serie: </span>{{ $fila['serie'] ?: '—' }}
                            </div>

                            <div class="mt-2 min-w-0 border-t border-[var(--theme-border)] pt-2 md:mt-0 md:border-t-0 md:px-3 md:py-2">
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
        </section>
    @endif
</div>
