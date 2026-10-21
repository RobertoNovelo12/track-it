<div

    x-data="{

        activo: @js($tiposReporte[0] ?? 'altas'),

        vistaLocalVacia: false,

        buscandoAutomaticamente: false,

        temporizadorBusqueda: null,

        versionBusqueda: 0,

        mensajeVisible: false,

        mensajeActual: '',

        mensajeTipo: 'success',

        temporizadorMensaje: null,

        generando: null,

        sugerenciasAbiertas: false,
        nombreEquipoVisible: null,
        sugerenciasLista: [],
        sugerenciasHayMasLocal: false,
        sugerenciasPaginaLocal: 0,
        sugerenciasTerminoLocal: '',
        consultandoSugerencias: false,
        temporizadorSugerencias: null,
        consultaSugerenciasVersion: 0,
        precargadas: @js($listasIniciales),
        precargasEnCurso: {},

        async precargarListas() {
            // Priorizar lo que el usuario está viendo; no esperar otras categorías.
            const tipos = ['altas', 'bajas', 'asignaciones', 'reasignaciones', 'mantenimientos'];
            const pendientes = [this.activo, ...tipos.filter(tipo => tipo !== this.activo)];
            const cargar = async (tipo) => {
                if (this.precargadas[tipo]) return this.precargadas[tipo];
                if (this.precargasEnCurso[tipo]) return this.precargasEnCurso[tipo];
                const promesa = $wire.obtenerSugerenciasRapidas(tipo, '', 1);
                this.precargasEnCurso[tipo] = promesa;
                try {
                    const datos = await promesa;
                    this.precargadas[tipo] = datos;
                    if (this.sugerenciasAbiertas && this.activo === tipo && this.sugerenciasTerminoLocal === '') {
                        this.mostrarLote(datos, true);
                    }
                    return datos;
                } catch (error) {
                    console.error('No fue posible precargar equipos:', tipo, error);
                    return null;
                } finally {
                    delete this.precargasEnCurso[tipo];
                }
            };
            // Lanza la categoría actual primero, después prepara las restantes.
            void cargar(pendientes[0]);
            // El resto va en lotes pequeños para no saturar la conexión.
            for (let i = 1; i < pendientes.length; i += 2) {
                await Promise.all(pendientes.slice(i, i + 2).map(cargar));
            }
        },

        mostrarLote(datos, reiniciar = true) {
            if (reiniciar) {
                this.sugerenciasLista = [];
                this.sugerenciasPaginaLocal = 0;
            }
            const nuevos = Array.isArray(datos?.registros) ? datos.registros : [];
            this.sugerenciasLista = reiniciar ? nuevos : [...this.sugerenciasLista, ...nuevos];
            this.sugerenciasPaginaLocal += 1;
            this.sugerenciasHayMasLocal = !!datos?.hayMas;
        },

        async solicitarSugerencias(texto = '', tipo = this.activo, reiniciar = true) {
            const version = ++this.consultaSugerenciasVersion;
            this.consultandoSugerencias = true;
            this.sugerenciasTerminoLocal = texto;
            try {
                const datos = texto === '' && this.precargadas[tipo]
                    ? this.precargadas[tipo]
                    : texto === '' && this.precargasEnCurso[tipo]
                        ? await this.precargasEnCurso[tipo]
                        : await $wire.obtenerSugerenciasRapidas(tipo, texto, 1);
                if (version !== this.consultaSugerenciasVersion || tipo !== this.activo) return;
                this.mostrarLote(datos, reiniciar);
            } catch (error) {
                console.error('Error al consultar equipos:', error);
            } finally {
                if (version === this.consultaSugerenciasVersion) this.consultandoSugerencias = false;
            }
        },

        mostrarSugerencias() {
            this.sugerenciasAbiertas = true;
            this.sugerenciasTerminoLocal = '';
            // Reutilizar el lote precargado si ya está listo.
            if (this.precargadas[this.activo]) {
                this.consultaSugerenciasVersion++;
                this.mostrarLote(this.precargadas[this.activo]);
                this.consultandoSugerencias = false;
                return;
            }
            this.solicitarSugerencias('', this.activo);
        },

        programarSugerencias(evento) {
            this.nombreEquipoVisible = null;
            clearTimeout(this.temporizadorSugerencias);
            const texto = evento.target.value.trim();
            const tipo = this.activo;
            this.sugerenciasAbiertas = true;
            this.sugerenciasLista = [];
            this.sugerenciasHayMasLocal = false;
            const version = ++this.consultaSugerenciasVersion;
            this.temporizadorSugerencias = setTimeout(() => {
                if (version !== this.consultaSugerenciasVersion) return;
                this.solicitarSugerencias(texto, tipo);
            }, 280);
        },

        async cargarMasSugerencias(evento) {
            const nodo = evento.target;
            if (!this.sugerenciasAbiertas || this.consultandoSugerencias ||
                !this.sugerenciasHayMasLocal ||
                nodo.scrollTop + nodo.clientHeight < nodo.scrollHeight - 45) return;
            this.consultandoSugerencias = true;
            const tipo = this.activo;
            const version = this.consultaSugerenciasVersion;
            try {
                const datos = await $wire.obtenerSugerenciasRapidas(tipo, this.sugerenciasTerminoLocal, this.sugerenciasPaginaLocal + 1);
                if (version !== this.consultaSugerenciasVersion || tipo !== this.activo) return;
                this.mostrarLote(datos, false);
            } catch (error) {
                console.error('Error al cargar más equipos:', error);
            } finally {
                this.consultandoSugerencias = false;
            }
        },

        seleccionarEquipo(codigo, nombre) {
            this.sugerenciasAbiertas = false;
            clearTimeout(this.temporizadorSugerencias);
            this.consultaSugerenciasVersion++;
            this.nombreEquipoVisible = nombre;
            $wire.seleccionarSugerencia(codigo);
        },

        reporteAEliminar: null,

        eliminandoReporte: false,

        filtros: [

            'busquedaActivo',

            'tipoEquipo',

            'estado',

            'fechaInicio',

            'fechaFin',

            'modelo',

            'marca'

        ],

        /*
         * Los avisos de Reportes usan el aviso global
         * (components/ui/toast-region).
         */
        iniciarMensaje(texto, tipo = 'success') {

            clearTimeout(this.temporizadorMensaje);

            this.mensajeVisible = false;

            this.mensajeActual = '';

            if (texto) {

                window.trackitToast?.(tipo === 'error' ? 'error' : 'success', texto);

            }

            $wire.set('successMessage', '', false);

            $wire.set('errorMessage', '', false);

        },

        limpiarMensajes() {

            clearTimeout(this.temporizadorMensaje);

            this.mensajeVisible = false;

            this.mensajeActual = '';

            $wire.set('successMessage', '', false);

            $wire.set('errorMessage', '', false);

        },

        elegir(tipo) {
            this.nombreEquipoVisible = null;

            if (this.activo === tipo) {

                return;

            }

            clearTimeout(this.temporizadorBusqueda);

            this.versionBusqueda++;

            /*

             * Cambio instantáneo sin esperar a Laravel.

             */

            this.sugerenciasAbiertas = false;
            clearTimeout(this.temporizadorSugerencias);
            this.consultaSugerenciasVersion++;
            this.sugerenciasLista = [];
            this.sugerenciasHayMasLocal = false;
            this.sugerenciasPaginaLocal = 0;
            this.activo = tipo;

            this.vistaLocalVacia = true;

            this.buscandoAutomaticamente = false;

            $wire.set('tipoReporte', tipo, false);

            $wire.set('tiposReporte', [tipo], false);

            this.filtros.forEach(campo => {

                $wire.set(campo, '', false);

            });

            $wire.set('previewReady', false, false);

            $wire.set('previewRecords', [], false);

            $wire.set('previewDocument', [], false);

            $wire.set('previewTotal', 0, false);

            this.limpiarMensajes();

            this.$dispatch('reportes-filtros-limpiados');

        },

        programarBusqueda(evento) {

            const elemento = evento.target;

            const modelo =

                elemento.getAttribute('wire:model') ||

                elemento.getAttribute('wire:model.live') ||

                elemento.getAttribute('wire:model.blur');

            if (!this.filtros.includes(modelo)) {

                return;

            }

            // El campo de equipo es un selector. No generar la vista previa
            // mientras se escribe: esperar a que el usuario elija un equipo.
            if (modelo === 'busquedaActivo') {
                clearTimeout(this.temporizadorBusqueda);
                this.versionBusqueda++;
                this.vistaLocalVacia = true;
                this.buscandoAutomaticamente = false;
                return;
            }

            clearTimeout(this.temporizadorBusqueda);

            this.vistaLocalVacia = true;

            const version = ++this.versionBusqueda;

            const espera = modelo === 'busquedaActivo'

                ? 400

                : 250;

            this.temporizadorBusqueda = setTimeout(() => {

                if (version !== this.versionBusqueda) {

                    return;

                }

                this.buscandoAutomaticamente = true;

                $wire.buscarAutomaticamente()

                    .catch(error => {

                        console.error(

                            'Error en búsqueda automática:',

                            error

                        );

                    })

                    .finally(() => {

                        if (version === this.versionBusqueda) {

                            this.buscandoAutomaticamente = false;

                        }

                    });

            }, espera);

        },

        programarBusquedaCampo(campo) {

            if (!this.filtros.includes(campo)) {

                return;

            }

            clearTimeout(this.temporizadorBusqueda);

            this.vistaLocalVacia = true;

            const version = ++this.versionBusqueda;

            this.temporizadorBusqueda = setTimeout(() => {

                if (version !== this.versionBusqueda) {

                    return;

                }

                this.buscandoAutomaticamente = true;

                $wire.buscarAutomaticamente()

                    .catch(error => {

                        console.error(

                            'Error en búsqueda automática:',

                            error

                        );

                    })

                    .finally(() => {

                        if (version === this.versionBusqueda) {

                            this.buscandoAutomaticamente = false;

                        }

                    });

            }, 150);

        },

        aplicarManualmente() {

            clearTimeout(this.temporizadorBusqueda);

            this.versionBusqueda++;

            this.buscandoAutomaticamente = true;

        },

        limpiarLocalmente() {
            this.nombreEquipoVisible = null;

            clearTimeout(this.temporizadorBusqueda);

            this.versionBusqueda++;

            this.vistaLocalVacia = true;

            this.buscandoAutomaticamente = false;

            this.filtros.forEach(campo => {

                $wire.set(campo, '', false);

            });

            $wire.set('previewReady', false, false);

            $wire.set('previewRecords', [], false);

            $wire.set('previewDocument', [], false);

            $wire.set('previewTotal', 0, false);

            this.limpiarMensajes();

        },

        abrirModalEliminar(id) {

            if (this.eliminandoReporte) return;

            this.reporteAEliminar = Number(id);

        },

        cerrarModalEliminar() {

            if (this.eliminandoReporte) return;

            this.reporteAEliminar = null;

        },

        async confirmarEliminarReporte() {

            if (this.reporteAEliminar === null || this.eliminandoReporte) return;

            const id = this.reporteAEliminar;

            this.eliminandoReporte = true;

            try {

                await $wire.eliminarReporte(id);

                this.reporteAEliminar = null;

            } catch (error) {

                console.error('No fue posible eliminar el reporte:', error);

                this.iniciarMensaje('No fue posible eliminar el reporte.', 'error');

            } finally {

                this.eliminandoReporte = false;

            }

        },

        async generarArchivo(formato) {

            if (this.generando !== null) {

                return;

            }

            clearTimeout(this.temporizadorBusqueda);

            this.versionBusqueda++;

            this.generando = formato;

            this.buscandoAutomaticamente = false;

            this.limpiarMensajes();

            try {

                if (formato === 'pdf') {

                    await $wire.generarPdf();

                } else if (

                    formato === 'xlsx' ||

                    formato === 'csv'

                ) {

                    await $wire.generarHojaCalculo(formato);

                }

            } catch (error) {

                console.error(

                    'Error al generar el archivo:',

                    error

                );

                this.iniciarMensaje(

                    'No fue posible generar el archivo.',

                    'error'

                );

            } finally {

                this.generando = null;

            }

        }

    }"

    x-init="

        $watch('$wire.successMessage', valor => {

            if (valor) {

                iniciarMensaje(valor, 'success');

            }

        });

        $watch('$wire.errorMessage', valor => {

            if (valor) {

                iniciarMensaje(valor, 'error');

            }

        });

        if ($wire.successMessage) {

            iniciarMensaje($wire.successMessage, 'success');

        }

        if ($wire.errorMessage) {

            iniciarMensaje($wire.errorMessage, 'error');

        }

        // Precarga diferida: no ralentiza la primera pintura de la página.
        setTimeout(() => precargarListas(), 0);

    "

    x-on:reportes-previa-lista.window="

        vistaLocalVacia = false;

        buscandoAutomaticamente = false

    "

    x-on:reportes-filtros-limpiados.window="

        vistaLocalVacia = true;

        buscandoAutomaticamente = false;

        clearTimeout(temporizadorBusqueda)

    "

    x-on:input.capture="programarBusqueda($event)"

    x-on:change.capture="programarBusqueda($event)"

    x-on:reporte-filtro-cambiado="programarBusquedaCampo($event.detail.campo)"

    x-on:reporte-sugerencia-seleccionada.window="sugerenciasAbiertas = false"
    x-on:keydown.escape.window="sugerenciasAbiertas = false"

>

    @php

        $tabs = [

            'altas' => 'Altas',

            'bajas' => 'Bajas',

            'asignaciones' => 'Asignación',

            'reasignaciones' => 'Reasignación',

            'mantenimientos' => 'Mantenimiento',

        ];

        $resumenes = [

            'altas' => 'Equipos registrados en el sistema.',

            'bajas' => 'Equipos incluidos en procesos de baja.',

            'asignaciones' => 'Equipos asignados a usuarios o áreas.',

            'reasignaciones' => 'Cambios de asignación de equipos.',

            'mantenimientos' => 'Historial de mantenimientos.',

        ];

        $tipoSeleccionado = $tiposReporte[0] ?? 'altas';

        $estadoLabel = match ($tipoReporte) {

            'bajas' => 'Estado de baja',

            'asignaciones' => 'Estado de asignación',

            'mantenimientos' => 'Estado de mantenimiento',

            default => 'Estado del activo',

        };

        $campoClase = 'h-10 w-full rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] focus:border-[var(--theme-primary)] focus:ring-1 focus:ring-[var(--theme-primary)]';

    @endphp

    <div class="mt-4 grid grid-cols-1 items-stretch gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(300px,1fr)]">

        {{-- CONFIGURACIÓN --}}

        <div class="min-w-0 space-y-4">

            {{-- NOTIFICACIÓN ÚNICA --}}

            <div

                x-show="mensajeVisible && mensajeActual !== ''"

                x-cloak

                x-transition.opacity.duration.200ms

                x-bind:class="

                    mensajeTipo === 'error'

                        ? 'border-[var(--theme-danger)] bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'

                        : 'border-[var(--theme-success-border)] bg-[var(--theme-success-soft)] text-[var(--theme-success)]'

                "

                class="flex items-center gap-2 rounded-lg border px-4 py-3 text-sm"

                role="status"

                aria-live="polite"

            >

                <svg

                    class="h-5 w-5 shrink-0"

                    viewBox="0 0 24 24"

                    fill="none"

                    stroke="currentColor"

                    stroke-width="1.8"

                >

                    <circle cx="12" cy="12" r="9"/>

                    <path

                        x-show="mensajeTipo === 'success'"

                        d="m8 12 2.5 2.5L16 9"

                    />

                    <path

                        x-show="mensajeTipo === 'error'"

                        d="M12 7v6M12 17h.01"

                    />

                </svg>

                <span x-text="mensajeActual"></span>

            </div>

            <form

                wire:submit="aplicarFiltros"

                x-on:submit="aplicarManualmente()"

                class="grid gap-6 rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4 sm:p-5 lg:grid-cols-[minmax(220px,0.85fr)_minmax(0,2fr)]"

            >

                <div class="lg:col-span-2">

                    <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">

                        Configuración del reporte

                    </h2>

                </div>

                {{-- TIPOS DE REPORTE --}}

                <section>

                    <div class="mb-3">

                        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">

                            Tipos de reporte

                        </h3>

                        <p class="text-xs text-[var(--theme-text-muted)]">

                            Selecciona un tipo de reporte.

                        </p>

                    </div>

                    <div class="space-y-2">

                        @foreach ($tabs as $valor => $etiqueta)

                            <button

                                type="button"

                                wire:key="selector-reporte-{{ $valor }}"

                                x-on:click="elegir(@js($valor))"

                                x-bind:aria-pressed="activo === @js($valor)"

                                x-bind:class="

                                    activo === @js($valor)

                                        ? '!border-[var(--theme-primary-border-strong)] !bg-[var(--theme-primary-soft)]'

                                        : ''

                                "

                                class="flex w-full items-center gap-3 rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-left transition-colors hover:border-[var(--theme-primary-border)]"

                            >

                                <span

                                    x-bind:class="

                                        activo === @js($valor)

                                            ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)] text-white'

                                            : 'border-[var(--theme-border-strong)] text-transparent'

                                    "

                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded border"

                                >

                                    <svg

                                        class="h-3.5 w-3.5"

                                        viewBox="0 0 24 24"

                                        fill="none"

                                        stroke="currentColor"

                                        stroke-width="2.5"

                                    >

                                        <path d="m5 12 4 4L19 6"/>

                                    </svg>

                                </span>

                                <span class="min-w-0">

                                    <span class="block text-sm font-semibold text-[var(--theme-text-strong)]">

                                        {{ $etiqueta }}

                                    </span>

                                    <span class="block text-xs text-[var(--theme-text-muted)]">

                                        {{ $resumenes[$valor] }}

                                    </span>

                                </span>

                            </button>

                        @endforeach

                    </div>

                </section>

                {{-- FILTROS --}}

                <section class="min-w-0">

                    <div class="mb-4">

                        <h3 class="text-sm font-semibold text-[var(--theme-text-strong)]">

                            Filtros

                        </h3>

                        <p class="text-xs text-[var(--theme-text-muted)]">

                            Define los criterios para tu reporte.

                        </p>

                    </div>

                    <div class="grid gap-4 md:grid-cols-2">

                        {{-- BUSCAR ACTIVO --}}

                        <div class="relative block md:col-span-2" x-on:click.outside="sugerenciasAbiertas = false">
                            <label for="busqueda-activo-reporte" class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">
                                Buscar activo
                            </label>

                            <input
                                id="busqueda-activo-reporte"
                                type="search"
                                wire:model="busquedaActivo"
                                x-bind:value="nombreEquipoVisible ?? $wire.busquedaActivo"
                                x-on:focus="mostrarSugerencias($event)"
                                x-on:click="if (!sugerenciasAbiertas) mostrarSugerencias($event)"
                                x-on:input="programarSugerencias($event)"
                                placeholder="Nombre, código de inventario o número de serie"
                                autocomplete="off"
                                class="{{ $campoClase }}"
                            >

                            <div
                                x-show="sugerenciasAbiertas && sugerenciasLista.length > 0"
                                x-cloak
                                x-on:scroll.passive="cargarMasSugerencias($event)"
                                class="absolute inset-x-0 top-full z-50 mt-1 max-h-64 overflow-y-auto rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] shadow-xl"
                            >
                                <template x-for="sugerencia in sugerenciasLista" :key="sugerencia.id">
                                    <button
                                        type="button"
                                        x-on:click="seleccionarEquipo(sugerencia.codigo, sugerencia.nombre)"
                                        class="block w-full border-b border-[var(--theme-border)] px-3 py-2.5 text-left hover:bg-[var(--theme-surface-soft)]"
                                    >
                                        <span class="block truncate text-sm font-medium text-[var(--theme-text-strong)]" x-text="sugerencia.nombre"></span>
                                        <span class="block truncate text-xs text-[var(--theme-text-muted)]" x-text="sugerencia.codigo + (sugerencia.serie ? ' · ' + sugerencia.serie : '')"></span>
                                    </button>
                                </template>
                                <div x-show="sugerenciasHayMasLocal" class="px-3 py-2 text-center text-xs text-[var(--theme-text-muted)]">
                                    Desplázate para ver más equipos
                                </div>
                            </div>
                        </div>

                        {{-- TIPO DE EQUIPO --}}

                        <x-searchable-select

                            :report-auto-search="true"

                            label="Tipo de equipo"

                            wire-model="tipoEquipo"

                            :options="collect($tiposEquipo)->map(fn ($item) => [

                                'value' => data_get($item, 'id_tipo_equipo'),

                                'label' => data_get($item, 'nombre'),

                            ])->filter(fn ($item) => filled($item['value']))->values()"

                            placeholder="Buscar tipo..."

                            :show-all-on-open="true"

                        />

                        {{-- ESTADO --}}

                        <x-searchable-select

                            :report-auto-search="true"

                            :label="$estadoLabel"

                            wire-model="estado"

                            :options="collect($estados)->map(fn ($item) => [

                                'value' => data_get($item, 'id_valor'),

                                'label' => data_get($item, 'nombre'),

                            ])->filter(fn ($item) => filled($item['value']))->values()"

                            placeholder="Buscar estado..."

                            :show-all-on-open="true"

                        />

                        {{-- FECHA INICIAL --}}

                        <label class="block">

                            <span class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">

                                Fecha inicial

                            </span>

                            <input

                                type="date"

                                wire:model="fechaInicio"

                                class="{{ $campoClase }}"

                            >

                            @error('fechaInicio')

                                <span class="mt-1 block text-xs text-[var(--theme-danger)]">

                                    {{ $message }}

                                </span>

                            @enderror

                        </label>

                        {{-- FECHA FINAL --}}

                        <label class="block">

                            <span class="mb-1.5 block text-xs font-medium text-[var(--theme-text)]">

                                Fecha final

                            </span>

                            <input

                                type="date"

                                wire:model="fechaFin"

                                class="{{ $campoClase }}"

                            >

                            @error('fechaFin')

                                <span class="mt-1 block text-xs text-[var(--theme-danger)]">

                                    {{ $message }}

                                </span>

                            @enderror

                        </label>

                        {{-- MODELO --}}

                        <x-searchable-select

                            :report-auto-search="true"

                            label="Modelo"

                            wire-model="modelo"

                            :options="collect($modelos)->map(fn ($item) => [

                                'value' => data_get($item, 'id_modelo'),

                                'label' => data_get($item, 'nombre'),

                            ])->filter(fn ($item) => filled($item['value']))->values()"

                            placeholder="Buscar modelo..."

                            :show-all-on-open="true"

                        />

                        {{-- MARCA --}}

                        <x-searchable-select

                            :report-auto-search="true"

                            label="Marca"

                            wire-model="marca"

                            :options="collect($marcas)->map(fn ($item) => [

                                'value' => data_get($item, 'id_marca'),

                                'label' => data_get($item, 'nombre'),

                            ])->filter(fn ($item) => filled($item['value']))->values()"

                            placeholder="Buscar marca..."

                            :show-all-on-open="true"

                        />

                    </div>

                    {{-- ACCIONES DE FILTROS --}}

                    <div class="mt-4 flex flex-wrap items-center justify-end gap-3 border-t border-[var(--theme-border)] pt-4">

                        <button

                            type="button"

                            wire:click="limpiarFiltros"

                            x-on:click="limpiarLocalmente()"

                            wire:loading.attr="disabled"

                            wire:target="limpiarFiltros"

                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-semibold text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"

                        >

                            <span wire:loading.remove wire:target="limpiarFiltros">

                                Limpiar filtros

                            </span>

                            <span wire:loading wire:target="limpiarFiltros">

                                Limpiando…

                            </span>

                        </button>

                        <x-ui.button
                            type="submit"
                            target="aplicarFiltros"
                            loading-text="Aplicando…"
                        >
                            Aplicar filtros
                        </x-ui.button>

                    </div>

                    {{-- BOTONES DE DESCARGA TRADICIONAL --}}

                    <div class="mt-auto flex justify-end pt-4">

                        <div class="flex flex-wrap items-center gap-2">

                            @foreach ([

                                'xlsx' => 'Excel',

                                'pdf' => 'PDF',

                                'csv' => 'CSV',

                            ] as $formato => $nombre)

                                {{--
                                    Mismo estilo que los botones secundarios (x-ui.button):
                                    fondo y borde neutros; el color solo identifica el
                                    formato en el icono. Mientras genera, el spinner ocupa
                                    el lugar del icono (el ancho no cambia).
                                --}}
                                <button
                                    type="button"
                                    x-on:click="generarArchivo(@js($formato))"
                                    x-bind:disabled="generando !== null"
                                    x-bind:aria-busy="generando === @js($formato) ? 'true' : 'false'"
                                    class="inline-flex h-9 select-none items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors duration-[var(--motion-fast)] hover:bg-[var(--theme-surface-soft)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--theme-primary)] disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    <span
                                        @class([
                                            'grid h-4 w-4 shrink-0 place-items-center',
                                            'text-emerald-600 dark:text-emerald-400' => $formato === 'xlsx',
                                            'text-red-600 dark:text-red-400' => $formato === 'pdf',
                                            'text-[var(--theme-text-muted)]' => $formato === 'csv',
                                        ])
                                    >
                                        {{-- Icono del formato --}}
                                        <svg
                                            x-show="generando !== @js($formato)"
                                            class="col-start-1 row-start-1 h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path d="M6 2h8l4 4v16H6z"/>
                                            <path d="M14 2v5h5M8 12h8M8 16h8"/>
                                        </svg>

                                        {{-- Generando (el x-show va en un span normal: dentro
                                             de <x-ui.spinner> Blade no procesaría @js) --}}
                                        <span
                                            x-show="generando === @js($formato)"
                                            x-cloak
                                            class="col-start-1 row-start-1 inline-flex text-[var(--theme-primary)]"
                                        >
                                            <x-ui.spinner size="sm" />
                                        </span>
                                    </span>

                                    <span>{{ $nombre }}</span>
                                </button>

                            @endforeach

                        </div>

                    </div>

                </section>

            </form>

        </div>

        {{-- VISTA PREVIA --}}

        <aside

            class="relative min-w-0 h-[650px] overflow-hidden rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-4 xl:h-auto xl:self-stretch"

        >

            <div class="flex items-center justify-between gap-3">

                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">

                    Vista rápida del documento final

                </h2>

                <span class="rounded border border-[var(--theme-border-strong)] px-2 py-1 text-[10px] font-semibold text-[var(--theme-text-muted)]">

                    PDF

                </span>

            </div>

            <div

                class="absolute inset-x-4 top-[62px] bottom-4 min-h-0 overflow-y-auto overflow-x-hidden overscroll-contain pr-1 [scrollbar-width:thin] [scrollbar-color:#64748b_transparent]"

                tabindex="0"

                aria-label="Vista previa desplazable del documento"

            >

                {{-- VISTA VACÍA INSTANTÁNEA --}}

                <div x-show="vistaLocalVacia" x-cloak>

                    <div x-show="activo === 'altas'">

                        @include('reportes.alta-previa', [

                            'alta' => []

                        ])

                    </div>

                    <div x-show="activo === 'bajas'">

                        @include('reportes.baja-acta-previa', [

                            'baja' => [

                                'equipo' => [],

                                'fotos' => []

                            ]

                        ])

                    </div>

                    <div x-show="activo === 'asignaciones'">

                        @include('reportes.acta-previa', [

                            'acta' => [

                                'tipo' => 'asignaciones',

                                'equipo' => [],

                                'anterior' => [],

                                'nuevo' => []

                            ]

                        ])

                    </div>

                    <div x-show="activo === 'reasignaciones'">

                        @include('reportes.acta-previa', [

                            'acta' => [

                                'tipo' => 'reasignaciones',

                                'equipo' => [],

                                'anterior' => [],

                                'nuevo' => []

                            ]

                        ])

                    </div>

                    <div x-show="activo === 'mantenimientos'">

                        @include('reportes.mantenimiento-previa', [

                            'm' => [

                                'equipo' => [],

                                'refacciones' => []

                            ]

                        ])

                    </div>

                </div>

                {{-- VISTA PREVIA CON RESULTADOS --}}

                <div x-show="!vistaLocalVacia" x-cloak>

                    @if ($tipoSeleccionado === 'altas')

                        @if (!$previewReady)

                            @include('reportes.alta-previa', [

                                'alta' => []

                            ])

                        @elseif (empty($previewDocument))

                            <div class="rounded-lg bg-white p-6 text-center text-sm text-slate-600">

                                No se encontraron altas con estos filtros.

                            </div>

                        @else

                            @include('reportes.alta-previa', [

                                'alta' => $previewDocument

                            ])

                        @endif

                    @elseif ($tipoSeleccionado === 'bajas')

                        @if (!$previewReady)

                            @include('reportes.baja-acta-previa', [

                                'baja' => [

                                    'equipo' => [],

                                    'fotos' => []

                                ]

                            ])

                        @elseif (empty($previewDocument))

                            <div class="rounded-lg bg-white p-6 text-center text-sm text-slate-600">

                                No se encontraron bajas con estos filtros.

                            </div>

                        @else

                            @include('reportes.baja-acta-previa', [

                                'baja' => $previewDocument

                            ])

                        @endif

                    @elseif (in_array(

                        $tipoSeleccionado,

                        ['asignaciones', 'reasignaciones'],

                        true

                    ))

                        @if (!$previewReady)

                            @include('reportes.acta-previa', [

                                'acta' => [

                                    'tipo' => $tipoSeleccionado,

                                    'equipo' => [],

                                    'anterior' => [],

                                    'nuevo' => []

                                ]

                            ])

                        @elseif (empty($previewDocument))

                            <div class="rounded-lg bg-white p-6 text-center text-sm text-slate-600">

                                No se encontraron movimientos con estos filtros.

                            </div>

                        @else

                            @include('reportes.acta-previa', [

                                'acta' => $previewDocument

                            ])

                        @endif

                    @elseif ($tipoSeleccionado === 'mantenimientos')

                        @if (!$previewReady)

                            @include('reportes.mantenimiento-previa', [

                                'm' => [

                                    'equipo' => [],

                                    'refacciones' => []

                                ]

                            ])

                        @elseif (empty($previewDocument))

                            <div class="rounded-lg bg-white p-6 text-center text-sm text-slate-600">

                                No se encontraron mantenimientos con estos filtros.

                            </div>

                        @else

                            @include('reportes.mantenimiento-previa', [

                                'm' => $previewDocument

                            ])

                        @endif

                    @else

                        <div class="rounded-lg bg-white p-6 text-center text-sm text-slate-600">

                            Selecciona un tipo de reporte.

                        </div>

                    @endif

                </div>

            </div>

        </aside>

    </div>

    {{-- HISTORIAL: tabla común (components/data-table) con paginación numerada --}}
    <section class="mt-5 space-y-3">

        <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
            Historial de reportes
        </h2>

        {{--
            Acciones en el menú ⋮ como acciones del navegador (js):
            «Ver» abre el archivo en otra pestaña y «Descargar» lo baja
            directamente; un enlace normal de la tabla usaría la navegación
            de Livewire, que no sirve para archivos.
        --}}
        <x-data-table
            :rows="$historial"
            row-key="id_reporte"
            key-prefix="reporte"
            page-name="historialPage"
            per-page-model="perPage"
            :per-page-options="[10, 25, 50]"
            sort-model="sort"
            refresh-action="$refresh"
            loading-target="previousPage,nextPage,gotoPage,perPage,sort,$refresh,eliminarReporte"
            min-width="980px"
            result-singular="reporte generado"
            result-plural="reportes generados"
            empty-title="Sin reportes"
            empty-variant="empty"
            empty-message="Todavía no se han generado reportes."
            :mobile-title="fn ($reporte) => $tabs[$reporte->tipo_reporte] ?? ucfirst($reporte->tipo_reporte)"
            :mobile-subtitle="fn ($reporte) => \App\Support\Fecha::conHora($reporte->fecha_generacion, '')"
            :columns="[
                [
                    'key' => 'tipo_reporte',
                    'label' => 'Tipo de reporte',
                    'width' => '160px',
                    'strong' => true,
                    'value' => fn ($reporte) => $tabs[$reporte->tipo_reporte] ?? ucfirst($reporte->tipo_reporte),
                    'mobile' => false,
                ],
                [
                    'key' => 'fecha_desde',
                    'label' => 'Fecha inicial',
                    'width' => '120px',
                    'value' => fn ($reporte) => $reporte->fecha_desde?->format('d/m/Y') ?? 'Sin límite',
                ],
                [
                    'key' => 'fecha_hasta',
                    'label' => 'Fecha final',
                    'width' => '120px',
                    'value' => fn ($reporte) => $reporte->fecha_hasta?->format('d/m/Y') ?? 'Sin límite',
                ],
                [
                    'key' => 'usuario',
                    'label' => 'Generado por',
                    'width' => '200px',
                    'value' => fn ($reporte) => trim(($reporte->usuario?->nombres ?? '') . ' ' . ($reporte->usuario?->apellido_paterno ?? '')) ?: 'Usuario',
                ],
                [
                    'key' => 'total_registros',
                    'label' => 'Registros',
                    'width' => '100px',
                    'value' => fn ($reporte) => number_format((int) $reporte->total_registros),
                ],
                [
                    'key' => 'formato',
                    'label' => 'Formato',
                    'width' => '100px',
                    'type' => 'list',
                    'value' => fn ($reporte) => [strtoupper((string) $reporte->formato)],
                ],
                [
                    'key' => 'fecha_generacion',
                    'label' => 'Fecha de creación',
                    'width' => '170px',
                    'value' => fn ($reporte) => \App\Support\Fecha::conHora($reporte->fecha_generacion, ''),
                    'mobile' => false,
                ],
            ]"
            :actions="fn ($reporte) => [
                'menu' => [
                    [
                        'label' => 'Ver reporte',
                        'js' => 'window.open(' . \Illuminate\Support\Js::from(route('reportes.show', $reporte)) . ', \'_blank\', \'noopener\')',
                    ],
                    [
                        'label' => 'Descargar',
                        'js' => 'window.location.href = ' . \Illuminate\Support\Js::from(route('reportes.download', $reporte)),
                    ],
                    [
                        'label' => 'Eliminar',
                        'js' => 'abrirModalEliminar(' . (int) $reporte->id_reporte . ')',
                        'danger' => true,
                    ],
                ],
            ]"
        />
    </section>

    {{-- MODAL DE CONFIRMACION DE ELIMINACION --}}

    <div

        x-show="reporteAEliminar !== null"

        x-cloak

        x-transition.opacity.duration.150ms

        x-on:keydown.escape.window="cerrarModalEliminar()"

        class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 px-4 py-6"

        x-on:click.self="cerrarModalEliminar()"

        role="presentation"

    >

        <section

            role="dialog"

            aria-modal="true"

            aria-labelledby="reporte-eliminar-titulo"

            aria-describedby="reporte-eliminar-descripcion"

            class="w-full max-w-md rounded-xl border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] p-5 shadow-2xl sm:p-6"

            x-on:click.stop

        >

            <div class="flex items-start justify-between gap-3">

                <h2 id="reporte-eliminar-titulo" class="text-base font-semibold text-[var(--theme-text-strong)]">

                    Eliminar reporte

                </h2>

                <button

                    type="button"

                    x-on:click="cerrarModalEliminar()"

                    x-bind:disabled="eliminandoReporte"

                    class="rounded-md p-1 text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)] disabled:opacity-50"

                    aria-label="Cerrar ventana"

                >

                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">

                        <path d="M18 6 6 18M6 6l12 12"/>

                    </svg>

                </button>

            </div>

            <p id="reporte-eliminar-descripcion" class="mt-3 text-sm text-[var(--theme-text)]">

                ¿Estás seguro de que deseas eliminar este reporte y su archivo generado?

            </p>

            <p class="mt-2 text-xs text-[var(--theme-text-muted)]">

                Esta acción no se puede deshacer.

            </p>

            <div class="mt-6 flex flex-wrap justify-end gap-3">

                <button

                    type="button"

                    x-on:click="cerrarModalEliminar()"

                    x-bind:disabled="eliminandoReporte"

                    class="inline-flex h-10 items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-sm font-medium text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)] disabled:opacity-50"

                >

                    Cancelar

                </button>

                <x-ui.button
                    x-on:click="confirmarEliminarReporte()"
                    x-loading="eliminandoReporte"
                    variant="danger-soft"
                    loading-text="Eliminando…"
                >
                    Confirmar eliminación
                </x-ui.button>

            </div>

        </section>

    </div>

</div>
