{{-- ============================================================
    IMPORTAR MARCAS Y MODELOS (App\Livewire\Catalogos\MarcasModelosImportar)
    Mismo diseño que Inventario › Importar.

    La vista previa se arma en el navegador con las filas que revisó el
    servidor ($wire.filas): las correcciones («usar…», «Usar el
    existente», «Dejar vacío») y la casilla «Actualizar…» son
    instantáneas. Se envían al servidor al pulsar el botón de guardar.
============================================================ --}}
@php
    $tarjeta = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    $columnasFila = 'lg:grid-cols-[150px_110px_minmax(0,1fr)_minmax(0,1.6fr)]';
@endphp

<div
    class="space-y-5"
    x-on:dragenter.window="if (esArchivo($event) && ! enMenu($event)) { profundidad++; encima = true; }"
    x-on:dragleave.window="if (esArchivo($event)) { profundidad = Math.max(0, profundidad - 1); if (profundidad === 0) encima = false; }"
    x-on:dragover.window="if (esArchivo($event)) { $event.preventDefault(); $event.dataTransfer.dropEffect = enMenu($event) ? 'none' : 'copy'; }"
    x-on:drop.window="if (esArchivo($event)) { $event.preventDefault(); profundidad = 0; encima = false; if (! enMenu($event)) subir($event.dataTransfer.files?.[0]); }"
    x-data="{
        actualizar: @js($actualizarExistentes),
        correcciones: {},
        corriendo: false,
        subiendo: false,
        progreso: 0,
        encima: false,
        profundidad: 0,
        hechos: 0,
        totalImportar: 0,

        insignias: {
            nueva: ['Se creará', 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'],
            actualizar: ['Se actualizará', 'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]'],
            sin_cambios: ['Sin cambios', 'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]'],
            existe: ['Ya existe', 'bg-[var(--theme-surface-soft)] text-[var(--theme-text)]'],
            creada: ['Creada', 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'],
            actualizada: ['Actualizada', 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'],
            fallo: ['No se guardó', 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'],
            error: ['Con errores', 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'],
        },

        // Archivo que llegó desde Marcas/Modelos (arrastrado o elegido en «Añadir»).
        init() {
            const archivo = window.__trackitArchivoMarcasModelos;
            window.__trackitArchivoMarcasModelos = null;

            if (archivo) this.subir(archivo);
        },

        esArchivo(evento) { return Array.from(evento.dataTransfer?.types ?? []).includes('Files'); },
        enMenu(evento) { return evento.target instanceof Element && !! evento.target.closest('aside, header, nav, #mobileAppHeader, .mi-barra'); },
        normal(t) { return String(t ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/\s+/g, ' ').trim(); },

        /* ---------- Vista previa calculada en el navegador ---------- */

        get filas() { return $wire.filas || []; },

        corregida(clave) { return clave && Object.prototype.hasOwnProperty.call(this.correcciones, clave); },

        // Fila de marca sin errores pendientes (sus modelos dependen de ella).
        filaOk(numero) {
            const f = this.filas.find((x) => x.numero === numero);

            return !! f && f.estado !== 'fallo' && (f.errores || []).every((e) => this.corregida(e.clave));
        },

        resuelto(e) {
            return this.corregida(e.clave) || (e.depende !== null && e.depende !== undefined && this.filaOk(e.depende));
        },

        calcular(f) {
            if (['creada', 'actualizada', 'fallo'].includes(f.estado)) {
                return { estado: f.estado, mensajes: f.mensajes || [], cambios: f.estado === 'actualizada' ? (f.cambios || []) : [], corregidos: [] };
            }

            const pendientes = (f.errores || []).filter((e) => ! this.resuelto(e));
            const corregidos = (f.correcciones || [])
                .filter((c) => this.corregida(c.clave))
                .map((c) => ({ etiqueta: c.etiqueta, antes: c.valor, despues: this.correcciones[c.clave] === '__quitar__' ? '' : this.correcciones[c.clave] }));

            if (pendientes.length) {
                return { estado: 'error', mensajes: pendientes.map((e) => e.texto), cambios: [], corregidos };
            }

            if (! f.existe) return { estado: 'nueva', mensajes: [], cambios: [], corregidos };

            if (! this.actualizar) {
                return { estado: 'existe', mensajes: ['Activa «Actualizar los que ya existen» para aplicar sus cambios.'], cambios: [], corregidos: [] };
            }

            const extra = (f.correcciones || [])
                .filter((c) => this.corregida(c.clave) && this.correcciones[c.clave] !== '__quitar__' && this.normal(this.correcciones[c.clave]) !== this.normal(c.actual))
                .map((c) => ({ etiqueta: c.etiqueta, antes: c.actual || '', despues: this.correcciones[c.clave] }));
            const cambios = [...(f.cambios || []), ...extra];

            return { estado: cambios.length ? 'actualizar' : 'sin_cambios', mensajes: [], cambios, corregidos: [] };
        },

        get vista() { return this.filas.map((f) => ({ f, r: this.calcular(f) })); },

        get cuentas() {
            const c = { nuevas: 0, actualizar: 0, errores: 0, sinCambios: 0, existen: 0 };

            this.vista.forEach(({ f, r }) => {
                if (r.estado === 'nueva') c.nuevas++;
                if (r.estado === 'actualizar') c.actualizar++;
                if (r.estado === 'error' || r.estado === 'fallo') c.errores++;
                if (r.estado === 'sin_cambios') c.sinCambios++;
                if (f.existe) c.existen++;
            });

            return c;
        },

        get textoBoton() {
            const c = this.cuentas;
            const total = c.nuevas + c.actualizar;
            const partes = [];

            if (c.nuevas) partes.push('crear ' + c.nuevas);
            if (c.actualizar) partes.push('actualizar ' + c.actualizar);

            const texto = partes.join(' y ');

            return texto.charAt(0).toUpperCase() + texto.slice(1) + (total === 1 ? ' registro' : ' registros');
        },

        corregir(clave, valor) {
            if (valor === '' || valor === null) {
                delete this.correcciones[clave];
            } else {
                this.correcciones[clave] = valor;
            }

            this.correcciones = { ...this.correcciones };
        },

        /* ---------- Archivo e importación ---------- */

        subir(archivo) {
            if (! archivo || this.subiendo || this.corriendo) return;

            if (! /\.(xlsx|csv)$/i.test(archivo.name)) {
                window.trackitToast?.('error', 'Solo se aceptan archivos Excel (.xlsx) o CSV.', 'Archivo no válido');
                return;
            }

            this.subiendo = true;
            this.progreso = 0;
            this.correcciones = {};

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

            const total = this.cuentas.nuevas + this.cuentas.actualizar;
            if (total === 0) return;

            this.corriendo = true;
            this.hechos = 0;
            this.totalImportar = total;
            let aviso = window.trackitToast?.('loading', 'Guardando marcas y modelos…', 'Importando') ?? null;

            try {
                // 1) Las correcciones se aplican una sola vez; 2) se guarda de 10 en 10.
                const preparado = await $wire.prepararImportacion({ ...this.correcciones }, this.actualizar);
                this.correcciones = {};
                this.totalImportar = Number(preparado.pendientes || 0);

                let r = { pendientes: this.totalImportar, creadas: 0, actualizadas: 0 };

                /*
                 * Contador de 1 en 1 (como en Inventario): entre lote y lote
                 * avanza al ritmo real medido, sin pasar del lote en curso; al
                 * volver cada lote se ajusta al número real.
                 */
                const totalLote = this.totalImportar;
                const inicio = performance.now();
                let confirmados = 0;
                let finLote = inicio;
                let msPorRegistro = 600;

                const pintar = () => {
                    if (totalLote <= 0) return;
                    const tope = Math.min(totalLote, confirmados + 10) - 1;
                    const estimado = confirmados + Math.floor((performance.now() - finLote) / msPorRegistro);
                    const nuevo = Math.max(this.hechos, confirmados, Math.min(Math.max(confirmados, tope), estimado));

                    if (nuevo === this.hechos) return;
                    this.hechos = nuevo;

                    const s = Math.max(1, Math.round(((totalLote - nuevo) * msPorRegistro) / 1000));
                    const resta = confirmados > 0 && nuevo < totalLote ? ' · quedan ≈ ' + (s < 60 ? s + ' s' : Math.floor(s / 60) + ' min ' + String(s % 60).padStart(2, '0') + ' s') : '';
                    if (aviso) window.trackitToastActualizar?.(aviso, 'loading', nuevo + ' de ' + totalLote + ' guardados' + resta, 'Importando');
                };

                const reloj = setInterval(pintar, 60);

                try {
                    while (r.pendientes > 0) {
                        r = await $wire.importarLote(10, this.actualizar);
                        confirmados = Math.max(0, totalLote - r.pendientes);
                        finLote = performance.now();
                        if (confirmados > 0) msPorRegistro = Math.max(30, (finLote - inicio) / confirmados);
                        this.hechos = Math.max(this.hechos, confirmados);
                        pintar();
                    }
                } finally {
                    clearInterval(reloj);
                }

                const partes = [];
                if (r.creadas) partes.push(r.creadas + (r.creadas === 1 ? ' creado' : ' creados'));
                if (r.actualizadas) partes.push(r.actualizadas + (r.actualizadas === 1 ? ' actualizado' : ' actualizados'));
                if (aviso) window.trackitToastActualizar?.(aviso, 'success', (partes.join(' y ') || 'Sin cambios') + '.', 'Importación terminada');
            } catch (error) {
                if (aviso) window.trackitToastActualizar?.(aviso, 'error', window.trackitMensajeError(error, 'importar'), 'Error al importar');
            } finally {
                this.corriendo = false;
            }
        },
    }"
>
    {{-- ========================================================
        1. ARCHIVO
    ======================================================== --}}
    <section class="{{ $tarjeta }}">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">Archivo</h2>
            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Plantilla de marcas y modelos (Excel con dos hojas o CSV) o un archivo exportado desde esta misma pantalla.
            </p>
        </div>

        <label
            for="mm-importar-archivo"
            x-bind:class="encima ? 'border-[var(--theme-primary)] bg-[var(--theme-primary-soft-subtle)]' : 'border-[var(--theme-border-strong)] hover:border-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft-subtle)]'"
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition-colors"
        >
            <span x-show="! subiendo" class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M5 21h14"/></svg>
            </span>

            <div x-show="subiendo" x-cloak class="w-full max-w-xs">
                <div class="h-1.5 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:class="progreso >= 100 && 'animate-pulse'" x-bind:style="{ width: Math.max(4, progreso) + '%' }"></div>
                </div>
            </div>

            <span class="text-sm font-medium text-[var(--theme-text-strong)]" x-text="subiendo ? (progreso < 100 ? 'Subiendo el archivo… ' + progreso + '%' : 'Revisando las filas del archivo…') : 'Suelta tu Excel o CSV aquí, o haz clic para elegirlo'">Suelta tu Excel o CSV aquí, o haz clic para elegirlo</span>
            <span class="text-xs text-[var(--theme-text-muted)]">.xlsx o .csv · hasta 4 MB y 1,000 filas</span>

            <input
                id="mm-importar-archivo"
                type="file"
                class="sr-only"
                accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                x-on:change="subir($event.target.files?.[0]); $event.target.value = ''"
                x-bind:disabled="corriendo || subiendo"
            >
        </label>

        @error('archivo') <p class="mt-2 text-center text-xs text-[var(--theme-danger)]">{{ $message }}</p> @enderror

        <div class="mt-4 grid grid-cols-1 gap-2 text-xs text-[var(--theme-text-muted)] sm:grid-cols-2">
            @foreach ([
                'En Excel usa las hojas «Marcas» y «Modelos» (puedes llenar solo una). En CSV, una tabla de marcas o una de modelos.',
                'Las marcas se guardan primero: un modelo puede usar una marca nueva de la hoja «Marcas».',
                'Con «Código» (MAR-… / MOD-…) se actualiza ese registro; sin código se reconoce por su nombre.',
                'Al actualizar, una celda vacía conserva el dato actual. Estado: «Activo» o «Inactivo».',
            ] as $consejo)
                <p class="flex items-start gap-2 rounded-lg bg-[var(--theme-surface-soft)] px-3 py-2">
                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[var(--theme-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>{{ $consejo }}</span>
                </p>
            @endforeach
        </div>
    </section>

    {{-- RESULTADO DE LA ÚLTIMA IMPORTACIÓN --}}
    @if (($ultimoResultado['creadas'] ?? 0) + ($ultimoResultado['actualizadas'] ?? 0) > 0)
        <div role="status" class="flex flex-col gap-3 rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 sm:flex-row sm:items-center sm:justify-between" style="box-shadow: inset 3px 0 var(--theme-success);">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-success-soft)] text-[var(--theme-success)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <p class="text-sm text-[var(--theme-text)]">
                    <strong class="font-semibold text-[var(--theme-text-strong)]">
                        {{ collect([
                            $ultimoResultado['creadas'] ? $ultimoResultado['creadas'].($ultimoResultado['creadas'] === 1 ? ' registro creado' : ' registros creados') : null,
                            $ultimoResultado['actualizadas'] ? $ultimoResultado['actualizadas'].($ultimoResultado['actualizadas'] === 1 ? ' actualizado' : ' actualizados') : null,
                        ])->filter()->implode(' y ') }}.
                    </strong>
                    <span class="block text-xs text-[var(--theme-text-muted)]">
                        {{ $ultimoResultado['marcas'] }} {{ $ultimoResultado['marcas'] === 1 ? 'marca' : 'marcas' }} · {{ $ultimoResultado['modelos'] }} {{ $ultimoResultado['modelos'] === 1 ? 'modelo' : 'modelos' }}
                        @if (($ultimoResultado['omitidas'] ?? 0) > 0) · {{ $ultimoResultado['omitidas'] }} ya {{ $ultimoResultado['omitidas'] === 1 ? 'existía y no se tocó' : 'existían y no se tocaron' }} @endif
                        @if ($cuentas['total'] > 0) · abajo quedan las filas con errores para corregirlas. @endif
                    </span>
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('catalogos.index', ['section' => 'marcas']) }}" wire:navigate.hover class="inline-flex h-9 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">Ver marcas</a>
                <a href="{{ route('catalogos.index', ['section' => 'modelos']) }}" wire:navigate.hover class="inline-flex h-9 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]">Ver modelos</a>
            </div>
        </div>
    @endif

    {{-- ========================================================
        2. VISTA PREVIA (se calcula en el navegador)
    ======================================================== --}}
    @if ($cuentas['total'] > 0)
        <x-admin.filter-bar>
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[var(--theme-text-strong)]">{{ $nombreArchivo }}</p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        {{ $cuentas['marcas'] }} {{ $cuentas['marcas'] === 1 ? 'marca' : 'marcas' }} · {{ $cuentas['modelos'] }} {{ $cuentas['modelos'] === 1 ? 'modelo' : 'modelos' }}
                        <span x-show="Object.keys(correcciones).length" x-cloak x-text="' · ' + Object.keys(correcciones).length + (Object.keys(correcciones).length === 1 ? ' corrección pendiente de guardar' : ' correcciones pendientes de guardar')"></span>
                    </p>

                    @if ($cuentas['existentes'] > 0)
                        <label class="mt-3 flex cursor-pointer items-start gap-2 text-sm text-[var(--theme-text)]">
                            <input type="checkbox" x-model="actualizar" x-bind:disabled="corriendo" class="mt-0.5 h-4 w-4 rounded border-[var(--theme-border-strong)] accent-[var(--theme-primary)]">
                            <span>
                                <span class="font-medium text-[var(--theme-text-strong)]">Actualizar los que ya existen</span>
                                <span class="block text-xs text-[var(--theme-text-muted)]">
                                    Se reconocen por su código o su nombre. Solo cambian las celdas con valor; una celda vacía conserva el dato actual.
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

                <x-ui.button
                    x-show="cuentas.nuevas + cuentas.actualizar > 0"
                    x-on:click="importar()"
                    x-loading="corriendo"
                    loading-text="Guardando…"
                >
                    <span x-text="textoBoton">Guardar</span>
                </x-ui.button>
            </x-slot:actions>
        </x-admin.filter-bar>

        {{-- Métricas (cambian al instante con las correcciones y la casilla) --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['cuentas.nuevas + cuentas.actualizar', "actualizar ? 'Por guardar (nuevos y actualizaciones)' : 'Listos para crear'", true],
                ['cuentas.errores', "'Con errores'", false],
                ['cuentas.existen', "actualizar ? 'Ya existen · ' + cuentas.sinCambios + ' sin cambios' : 'Ya existen'", false],
                ['filas.length', "'Filas en el archivo'", false],
            ] as [$valor, $etiqueta, $destacada])
                <div @class([
                    'rounded-xl border bg-[var(--theme-surface)] px-4 py-3.5',
                    'border-[var(--theme-primary)]' => $destacada,
                    'border-[var(--theme-border)]' => ! $destacada,
                ])>
                    <p @class([
                        'text-2xl font-semibold leading-tight tabular-nums',
                        'text-[var(--theme-primary)]' => $destacada,
                        'text-[var(--theme-text-strong)]' => ! $destacada,
                    ]) x-text="({{ $valor }}).toLocaleString('es-MX')">0</p>
                    <p class="mt-0.5 text-[11px] text-[var(--theme-text-muted)]" x-text="{{ $etiqueta }}"></p>
                </div>
            @endforeach
        </div>

        <div>
            {{-- Avance --}}
            <div x-show="corriendo" x-cloak class="mb-4" role="status">
                <div class="h-2 overflow-hidden rounded-full bg-[var(--theme-surface-soft)]">
                    <div class="h-full rounded-full bg-[var(--theme-primary)] transition-[width] duration-300" x-bind:style="{ width: (totalImportar ? Math.max(3, Math.round(hechos / totalImportar * 100)) : 3) + '%' }"></div>
                </div>
                <p class="mt-1.5 text-[11px] tabular-nums text-[var(--theme-text-muted)]" x-text="hechos + ' de ' + totalImportar + ' guardados'"></p>
            </div>

            {{-- Filas: tabla en escritorio y tarjetas en móvil --}}
            <div class="lg:overflow-hidden lg:rounded-lg lg:border lg:border-[var(--theme-border)] lg:bg-[var(--theme-surface)]">
                <div class="hidden h-11 items-center border-b border-[var(--theme-border)] text-xs font-medium text-[var(--theme-text-muted)] lg:grid {{ $columnasFila }}">
                    <div class="px-4">Fila en el archivo</div>
                    <div class="px-4">Tipo</div>
                    <div class="px-4">Nombre</div>
                    <div class="px-4">Resultado</div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:block lg:divide-y lg:divide-[var(--theme-border)]">
                    <template x-for="{ f, r } in vista" :key="f.numero + '-' + f.etiqueta">
                        <div class="rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 text-sm shadow-sm lg:grid lg:items-start lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0 lg:text-xs lg:shadow-none lg:hover:bg-[var(--theme-primary-soft-subtle)] 2xl:text-sm {{ $columnasFila }}">
                            <div class="flex items-center gap-1.5 text-xs text-[var(--theme-text-muted)] lg:contents">
                                <span class="lg:px-4 lg:py-3 lg:text-xs 2xl:text-sm" x-text="f.etiqueta"></span>
                                <span class="lg:hidden" aria-hidden="true">·</span>
                                <span class="lg:px-4 lg:py-3 lg:text-xs lg:text-[var(--theme-text)] 2xl:text-sm" x-text="f.clase === 'marca' ? 'Marca' : 'Modelo'"></span>
                            </div>

                            <div class="mt-1 min-w-0 lg:mt-0 lg:px-4 lg:py-3">
                                <p class="break-words font-medium text-[var(--theme-text-strong)]" x-text="f.nombre || '—'"></p>
                                <p x-show="f.detalle" class="break-words text-xs text-[var(--theme-text-muted)]" x-text="f.detalle"></p>
                            </div>

                            <div class="mt-2 min-w-0 border-t border-[var(--theme-border)] pt-2 lg:mt-0 lg:border-t-0 lg:px-4 lg:py-3">
                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium" x-bind:class="insignias[r.estado][1]">
                                    <span x-text="insignias[r.estado][0] + (f.codigo ? ' · ' + f.codigo : '') + (['actualizar', 'actualizada'].includes(r.estado) ? ' · ' + r.cambios.length + (r.cambios.length === 1 ? ' cambio' : ' cambios') : '')"></span>
                                </span>

                                {{-- Lo que cambia (al actualizar) --}}
                                <ul x-show="r.cambios.length" class="mt-1 space-y-0.5 text-xs text-[var(--theme-text)]">
                                    <template x-for="(cambio, i) in r.cambios" :key="i">
                                        <li>
                                            <span class="text-[var(--theme-text-muted)]" x-text="cambio.etiqueta + ':'"></span>
                                            <span class="text-[var(--theme-text-muted)]" x-bind:class="cambio.antes && 'line-through'" x-text="cambio.antes || '(vacío)'"></span> →
                                            <span class="font-medium text-[var(--theme-text-strong)]" x-text="cambio.despues"></span>
                                        </li>
                                    </template>
                                </ul>

                                {{-- Correcciones hechas en esta fila (se pueden deshacer) --}}
                                <template x-for="(c, i) in r.corregidos" :key="'c' + i">
                                    <p class="mt-1 text-xs text-[var(--theme-text)]">
                                        <span class="text-[var(--theme-text-muted)]" x-text="c.etiqueta + ':'"></span>
                                        <span class="text-[var(--theme-text-muted)] line-through" x-text="c.antes"></span> →
                                        <span class="font-medium text-[var(--theme-text-strong)]" x-text="c.despues || '(vacío)'"></span>
                                    </p>
                                </template>

                                {{-- Avisos y errores pendientes --}}
                                <ul x-show="r.mensajes.length" class="mt-1 space-y-0.5 text-xs" x-bind:class="r.estado === 'existe' ? 'text-[var(--theme-text-muted)]' : 'text-[var(--theme-danger)]'">
                                    <template x-for="(m, i) in r.mensajes" :key="'m' + i">
                                        <li x-text="m"></li>
                                    </template>
                                </ul>

                                {{-- Corregir aquí: elegir una opción válida, usar el parecido o quitar el valor --}}
                                <template x-for="c in (f.correcciones || [])" :key="c.clave">
                                    <div x-show="r.estado === 'error' || corregida(c.clave)" class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="text-[var(--theme-text-muted)]" x-text="c.etiqueta + ': usar'"></span>
                                        <select
                                            x-on:change="corregir(c.clave, $event.target.value)"
                                            x-bind:value="correcciones[c.clave] ?? ''"
                                            x-bind:disabled="corriendo"
                                            class="h-8 max-w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-2 text-xs text-[var(--theme-text)] focus:border-[var(--theme-primary)] focus:outline-none"
                                            x-bind:aria-label="'Corregir «' + c.valor + '» en ' + c.etiqueta"
                                        >
                                            <option value="" x-text="'«' + c.valor + '» (sin corregir)'"></option>
                                            <template x-for="o in c.opciones" :key="o">
                                                <option x-bind:value="o" x-text="o" x-bind:selected="correcciones[c.clave] === o"></option>
                                            </template>
                                            <template x-if="! (['id_marca', 'id_tipo_equipo'].includes(c.columna) && ! f.existe)">
                                                <option value="__quitar__" x-text="f.existe ? 'Dejar sin cambiar' : 'Dejar vacío'" x-bind:selected="correcciones[c.clave] === '__quitar__'"></option>
                                            </template>
                                        </select>

                                        <template x-if="c.sugerencia && ! corregida(c.clave)">
                                            <span class="flex flex-wrap items-center gap-2">
                                                <span class="text-[var(--theme-text-muted)]">Ya tienes <span class="font-medium text-[var(--theme-text-strong)]" x-text="'«' + c.sugerencia + '»'"></span></span>
                                                <button
                                                    type="button"
                                                    x-on:click="corregir(c.clave, c.sugerencia)"
                                                    x-bind:title="'Cambia «' + c.valor + '» por «' + c.sugerencia + '» en todas las filas que lo traen'"
                                                    class="h-8 shrink-0 rounded-md border border-[var(--theme-primary)] px-3 font-medium text-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft)]"
                                                >Usar el existente</button>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif
</div>
