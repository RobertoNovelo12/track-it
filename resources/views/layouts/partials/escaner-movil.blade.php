{{-- ============================================================
    ESCÁNER DE CÓDIGOS DE BARRAS (teléfono)
    ------------------------------------------------------------
    Se abre con el botón del buscador en móvil ($dispatch('abrir-escaner')).
    Al leer un código pregunta qué hacer:
    - Abrir aquí: ver el equipo en el teléfono.
    - Enviar a mi PC: llega a «Equipos recibidos» de la misma cuenta
      (bandeja personal en la barra superior de escritorio). La flecha
      junto al botón permite elegir a varias personas (y si incluirte);
      la selección se conserva para los siguientes códigos. La cámara sigue abierta
      para escanear el siguiente (ráfaga).
    Lectura: resources/js/escaner.js (@zxing/browser). Exige HTTPS.
============================================================ --}}
<div
    x-data="{
        abierto: false,
        estado: 'camara',      // camara | consultando | equipo | nuevo | enviado | error
        // Código que no está en el inventario (probable número de serie).
        codigoNuevo: null,
        mensaje: '',
        equipo: null,
        puedeEnviar: false,
        enviando: false,

        /*
         * Destinos (se mantienen para los siguientes códigos):
         * incluirme = mi propia bandeja; destinos = otros usuarios.
         */
        incluirme: true,
        destinos: [],
        eligiendo: false,
        destinatarios: null,
        cargandoDestinatarios: false,
        errorDestinatarios: false,
        busqueda: '',
        ultimoCodigo: '',
        ultimoMomento: 0,
        temporizador: null,

        async abrir() {
            this.abierto = true;
            this.equipo = null;
            this.mensaje = '';
            document.documentElement.style.overflow = 'hidden';

            if (! window.isSecureContext) {
                this.mostrarError('La cámara solo funciona en conexiones seguras (HTTPS). Abre el sistema con https://.');

                return;
            }

            await this.$nextTick();
            this.reanudar();
        },

        async reanudar() {
            clearTimeout(this.temporizador);
            this.eligiendo = false;
            this.estado = 'camara';
            this.equipo = null;
            this.codigoNuevo = null;
            this.mensaje = '';

            try {
                // Por id y no con $refs: reanudar() también se llama desde botones
                // dentro de <template x-if>, donde $refs.video llega undefined.
                const video = document.getElementById('trackitEscanerVideo');

                await window.trackitEscaner.iniciar(video, (codigo) => this.leido(codigo));
            } catch (error) {
                console.error('[Track-IT] No se pudo abrir la cámara.', error);

                const nombre = error?.name ?? '';

                this.mostrarError(
                    nombre === 'NotAllowedError'
                        ? 'Permite el acceso a la cámara en tu navegador para escanear.'
                        : nombre === 'NotFoundError'
                            ? 'No se encontró una cámara en este dispositivo.'
                            : nombre === 'NotReadableError'
                                ? 'La cámara está ocupada por otra aplicación. Ciérrala e inténtalo de nuevo.'
                                : 'No se pudo abrir la cámara (' + [nombre, error?.message].filter(Boolean).join(': ') + '). Inténtalo de nuevo.'
                );
            }
        },

        cerrar() {
            clearTimeout(this.temporizador);
            this.eligiendo = false;
            window.trackitEscaner?.detener();
            this.abierto = false;
            document.documentElement.style.overflow = '';
        },

        /*
         * apagarCamara: solo en errores de la propia cámara. Si el código no
         * existe o falla la conexión, la cámara queda encendida (en pausa)
         * para que «Volver a intentar» sea inmediato.
         */
        mostrarError(texto, apagarCamara = true) {
            if (apagarCamara) {
                window.trackitEscaner?.detener();
            } else {
                window.trackitEscaner?.pausar();
            }

            this.estado = 'error';
            this.mensaje = texto;
        },

        async leido(codigo) {
            if (this.estado !== 'camara' || ! codigo) {
                return;
            }

            // El mismo código sigue frente a la cámara: no repetir.
            const ahora = Date.now();

            if (codigo === this.ultimoCodigo && ahora - this.ultimoMomento < 3000) {
                return;
            }

            this.ultimoCodigo = codigo;
            this.ultimoMomento = ahora;
            navigator.vibrate?.(60);

            this.estado = 'consultando';
            window.trackitEscaner.pausar();

            try {
                const respuesta = await fetch(
                    @js(url('/equipos/escaneo')) + '/' + encodeURIComponent(codigo),
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const datos = await respuesta.json().catch(() => ({}));

                // No está en el inventario: ofrecer darlo de alta (código = número de serie).
                if (respuesta.status === 404 && datos.encontrado === false && datos.codigo) {
                    this.codigoNuevo = {
                        codigo: datos.codigo,
                        puedeAgregar: Boolean(datos.puede_agregar),
                        urlAgregar: datos.url_agregar ?? null,
                    };
                    this.puedeEnviar = Boolean(datos.puede_enviar);
                    this.estado = 'nuevo';

                    return;
                }

                if (! respuesta.ok || ! datos.encontrado) {
                    this.mostrarError(datos.mensaje ?? ('No hay ningún equipo con el código ' + codigo + '.'), false);

                    return;
                }

                this.equipo = datos.equipo;
                this.puedeEnviar = Boolean(datos.puede_enviar);
                this.estado = 'equipo';
            } catch (error) {
                this.mostrarError('Sin conexión con el servidor. Revisa tu conexión e inténtalo de nuevo.', false);
            }
        },

        agregarAqui() {
            const url = this.codigoNuevo?.urlAgregar;
            this.cerrar();

            if (url) {
                window.Livewire?.navigate ? window.Livewire.navigate(url) : (window.location.href = url);
            }
        },

        abrirAqui() {
            const url = this.equipo?.url;
            this.cerrar();

            if (url) {
                window.Livewire?.navigate ? window.Livewire.navigate(url) : (window.location.href = url);
            }
        },

        /*
         * Lista de usuarios: se pide una sola vez, al tocar la flecha.
         */
        async abrirDestinatarios() {
            this.busqueda = '';
            this.eligiendo = true;

            if (this.destinatarios !== null || this.cargandoDestinatarios) {
                return;
            }

            this.cargandoDestinatarios = true;
            this.errorDestinatarios = false;

            try {
                const respuesta = await fetch(@js(route('equipos.recibidos.destinatarios')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (! respuesta.ok) {
                    throw new Error(respuesta.status);
                }

                const datos = await respuesta.json();
                this.destinatarios = datos.destinatarios ?? [];
            } catch (error) {
                this.errorDestinatarios = true;
            } finally {
                this.cargandoDestinatarios = false;
            }
        },

        get destinatariosFiltrados() {
            const texto = this.busqueda.trim().toLowerCase();
            const lista = this.destinatarios ?? [];

            if (! texto) {
                return lista;
            }

            return lista.filter((d) => [d.nombre, d.detalle, d.usuario].join(' ').toLowerCase().includes(texto));
        },

        estaElegido(id) {
            return this.destinos.some((d) => d.id === id);
        },

        alternarDestino(persona) {
            this.destinos = this.estaElegido(persona.id)
                ? this.destinos.filter((d) => d.id !== persona.id)
                : [...this.destinos, { id: persona.id, nombre: persona.nombre, corto: persona.nombre.split(' ')[0] }];
        },

        get totalDestinos() {
            return this.destinos.length + (this.incluirme ? 1 : 0);
        },

        /* Texto del botón principal. */
        get etiquetaEnvio() {
            if (this.totalDestinos === 0 || (this.incluirme && this.destinos.length === 0)) {
                return 'Enviar a mi PC';
            }

            if (! this.incluirme && this.destinos.length === 1) {
                return 'Enviar a ' + this.destinos[0].corto;
            }

            return 'Enviar a ' + this.totalDestinos + ' personas';
        },

        /* Resumen de la selección (lista de destinatarios). */
        get resumenDestinos() {
            const nombres = [...(this.incluirme ? ['Mi PC'] : []), ...this.destinos.map((d) => d.corto)];

            return nombres.length ? nombres.join(', ') : 'Nadie seleccionado';
        },

        confirmarDestinos() {
            if (this.totalDestinos === 0) {
                return;
            }

            this.eligiendo = false;
            this.enviar();
        },

        async enviar() {
            const esNuevo = this.estado === 'nuevo' && this.codigoNuevo;

            if ((! this.equipo && ! esNuevo) || this.enviando) {
                return;
            }

            this.enviando = true;

            try {
                const respuesta = await fetch(@js(route('equipos.recibidos.enviar')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        ...(esNuevo ? { codigo: this.codigoNuevo.codigo } : { id_equipo: this.equipo.id }),
                        incluirme: this.totalDestinos === 0 ? true : this.incluirme,
                        id_destinatarios: this.destinos.map((d) => d.id),
                    }),
                });
                const datos = await respuesta.json().catch(() => ({}));

                if (! respuesta.ok) {
                    this.mostrarError(datos.message ?? window.trackitMensajeError({ status: respuesta.status }, 'enviar el equipo'), false);

                    return;
                }

                /*
                 * Mensaje según a quién llegó. «resultados» trae un registro
                 * por destinatario (enviado o ya estaba en su bandeja).
                 */
                const resultados = datos.resultados ?? [{ propio: datos.propio !== false, estado: datos.estado, pendientes: datos.pendientes }];
                const nombre = (r) => r.propio ? 'tu PC' : (this.destinos.find((d) => d.id === r.id)?.nombre ?? 'otro usuario');
                const enviados = resultados.filter((r) => r.estado === 'enviado');
                const repetidos = resultados.filter((r) => r.estado === 'duplicado');
                const que = esNuevo ? 'Código enviado' : 'Enviado';

                if (resultados.length === 1) {
                    const r = resultados[0];
                    const bandeja = r.propio ? 'tu bandeja' : 'la bandeja de ' + nombre(r);

                    this.mensaje = r.estado === 'duplicado'
                        ? (esNuevo ? 'Este código ya estaba en ' : 'Este equipo ya estaba en ') + bandeja + '.'
                        : que + ' a ' + nombre(r) + (esNuevo ? ' para agregarlo' : '') + ' (' + r.pendientes + (r.pendientes === 1 ? ' pendiente' : ' pendientes') + ').';
                } else if (enviados.length === 0) {
                    this.mensaje = 'Ya estaba en la bandeja de las ' + resultados.length + ' personas.';
                } else {
                    this.mensaje = que + ' a ' + enviados.map(nombre).join(', ') + '.'
                        + (repetidos.length ? ' Ya lo tenía: ' + repetidos.map(nombre).join(', ') + '.' : '');
                }

                this.estado = 'enviado';

                // Ráfaga: volver a la cámara para el siguiente equipo.
                this.temporizador = setTimeout(() => this.abierto && this.reanudar(), 1300);
            } catch (error) {
                this.mostrarError('Sin conexión con el servidor. Revisa tu conexión e inténtalo de nuevo.', false);
            } finally {
                this.enviando = false;
            }
        },
    }"
    x-on:abrir-escaner.window="abrir()"
    x-on:keydown.escape.window="abierto && (eligiendo ? (eligiendo = false) : cerrar())"
>
    <div
        x-show="abierto"
        x-cloak
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[150] flex flex-col bg-black text-white"
        role="dialog"
        aria-modal="true"
        aria-label="Escanear código de barras"
    >
        {{-- Encabezado --}}
        <div class="flex items-center justify-between gap-3 px-4 pb-3 pt-[max(0.75rem,env(safe-area-inset-top))]">
            <p class="text-sm font-semibold">Escanear código de barras</p>

            <button
                type="button"
                x-on:click="cerrar()"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 hover:bg-white/20"
                aria-label="Cerrar escáner"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        {{-- Cámara --}}
        <div class="relative min-h-0 flex-1 overflow-hidden">
            <video id="trackitEscanerVideo" x-ref="video" class="absolute inset-0 h-full w-full object-cover" playsinline muted></video>

            {{-- Guía de encuadre --}}
            <div
                x-show="estado === 'camara' || estado === 'consultando'"
                class="pointer-events-none absolute inset-0 flex items-center justify-center"
            >
                <div class="relative h-32 w-[80%] max-w-sm rounded-xl border-2 border-white/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]">
                    <div class="absolute inset-x-4 top-1/2 h-0.5 -translate-y-1/2 bg-[var(--theme-danger)] opacity-80"></div>
                </div>
            </div>

            <p
                x-show="estado === 'camara'"
                class="absolute inset-x-0 bottom-6 text-center text-xs text-white/80"
            >
                Apunta al código de barras del equipo
            </p>

            <div
                x-show="estado === 'consultando'"
                class="absolute inset-x-0 bottom-6 flex items-center justify-center gap-2 text-xs text-white/90"
            >
                <x-ui.spinner size="sm" />
                Buscando equipo…
            </div>
        </div>

        {{-- Resultado / acciones --}}
        <div
            x-show="eligiendo || ['equipo', 'nuevo', 'enviado', 'error'].includes(estado)"
            x-transition.opacity
            class="rounded-t-2xl bg-[var(--theme-surface)] px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-4 text-[var(--theme-text)]"
        >
            {{-- Equipo encontrado --}}
            <template x-if="estado === 'equipo' && equipo && ! eligiendo">
                <div>
                    <p class="text-xs text-[var(--theme-text-muted)]" x-text="equipo.codigo"></p>
                    <p class="mt-0.5 text-base font-semibold text-[var(--theme-text-strong)]" x-text="equipo.nombre || equipo.codigo"></p>
                    <p class="mt-0.5 text-xs text-[var(--theme-text-muted)]">
                        <span x-text="equipo.detalle"></span>
                        <span x-show="equipo.estado"> · <span x-text="equipo.estado"></span></span>
                    </p>

                    <p x-show="equipo.dado_de_baja" class="mt-2 text-xs text-[var(--theme-warning)]">
                        Este equipo ya está dado de baja.
                    </p>

                    <div class="mt-4 grid gap-2" x-bind:class="puedeEnviar ? 'grid-cols-2' : 'grid-cols-1'">
                        <button
                            type="button"
                            x-on:click="abrirAqui()"
                            class="inline-flex h-11 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-sm font-medium text-[var(--theme-text)]"
                        >
                            Abrir aquí
                        </button>

                        @include('layouts.partials.escaner-boton-enviar')
                    </div>

                    <button
                        type="button"
                        x-on:click="reanudar()"
                        class="mt-2 w-full py-2 text-xs font-medium text-[var(--theme-text-muted)]"
                    >
                        Seguir escaneando
                    </button>
                </div>
            </template>

            {{-- Código que no está en el inventario: se puede agregar con ese número de serie --}}
            <template x-if="estado === 'nuevo' && codigoNuevo && ! eligiendo">
                <div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 9v4M12 17h.01"/>
                                <path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-[var(--theme-text-strong)]">No está en el inventario</p>
                            <p class="mt-0.5 break-all font-mono text-xs text-[var(--theme-text)]" x-text="codigoNuevo.codigo"></p>
                            <p class="mt-1 text-xs text-[var(--theme-text-muted)]" x-show="codigoNuevo.puedeAgregar">
                                Puedes agregarlo como equipo nuevo; este código se guardará como su número de serie.
                            </p>
                        </div>
                    </div>

                    <div
                        x-show="codigoNuevo.puedeAgregar"
                        class="mt-4 grid gap-2"
                        x-bind:class="puedeEnviar ? 'grid-cols-2' : 'grid-cols-1'"
                    >
                        <button
                            type="button"
                            x-on:click="agregarAqui()"
                            class="inline-flex h-11 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] text-sm font-medium text-[var(--theme-text)]"
                        >
                            Agregar aquí
                        </button>

                        @include('layouts.partials.escaner-boton-enviar')
                    </div>

                    <button
                        type="button"
                        x-on:click="reanudar()"
                        class="mt-2 w-full py-2 text-xs font-medium text-[var(--theme-text-muted)]"
                    >
                        Seguir escaneando
                    </button>
                </div>
            </template>

            {{-- Elegir a quién enviarlo (varios). La selección se conserva para los siguientes códigos. --}}
            <div x-show="eligiendo" x-cloak>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        x-on:click="eligiendo = false"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[var(--theme-text-muted)] hover:bg-[var(--theme-surface-soft)]"
                        aria-label="Volver"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-[var(--theme-text-strong)]">Enviar a…</p>
                        <p class="truncate text-xs text-[var(--theme-text-muted)]" x-text="resumenDestinos"></p>
                    </div>

                    <button
                        type="button"
                        x-show="destinos.length > 0"
                        x-on:click="destinos = []; incluirme = true"
                        class="shrink-0 px-2 py-1 text-xs font-medium text-[var(--theme-text-muted)] hover:text-[var(--theme-text)]"
                    >
                        Limpiar
                    </button>
                </div>

                <input
                    type="search"
                    x-model="busqueda"
                    placeholder="Buscar usuario…"
                    autocomplete="off"
                    class="mt-3 h-10 w-full rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                >

                <ul class="mt-2 max-h-[40vh] divide-y divide-[var(--theme-border)] overflow-y-auto overscroll-contain" role="listbox" aria-multiselectable="true" aria-label="Destinatarios">
                    {{-- Mi cuenta --}}
                    <li x-show="! busqueda.trim()">
                        <button
                            type="button"
                            x-on:click="incluirme = ! incluirme"
                            class="flex w-full items-center gap-3 py-2.5 text-left"
                            role="option"
                            x-bind:aria-selected="incluirme ? 'true' : 'false'"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-medium text-[var(--theme-text-strong)]">Mi PC</span>
                                <span class="block text-xs text-[var(--theme-text-muted)]">Tu propia bandeja</span>
                            </span>
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition-colors"
                                x-bind:class="incluirme ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)] text-white' : 'border-[var(--theme-border-strong)] text-transparent'"
                                aria-hidden="true"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"/></svg>
                            </span>
                        </button>
                    </li>

                    <template x-for="persona in destinatariosFiltrados" x-bind:key="persona.id">
                        <li>
                            <button
                                type="button"
                                x-on:click="alternarDestino(persona)"
                                class="flex w-full items-center gap-3 py-2.5 text-left"
                                role="option"
                                x-bind:aria-selected="estaElegido(persona.id) ? 'true' : 'false'"
                            >
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--theme-surface-soft)] text-xs font-semibold text-[var(--theme-text)]"
                                    x-text="persona.nombre.split(' ').slice(0, 2).map((p) => p.charAt(0)).join('').toUpperCase()"
                                    aria-hidden="true"
                                ></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-[var(--theme-text-strong)]" x-text="persona.nombre"></span>
                                    <span class="block truncate text-xs text-[var(--theme-text-muted)]" x-text="persona.detalle || persona.usuario"></span>
                                </span>
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded border transition-colors"
                                    x-bind:class="estaElegido(persona.id) ? 'border-[var(--theme-primary)] bg-[var(--theme-primary)] text-white' : 'border-[var(--theme-border-strong)] text-transparent'"
                                    aria-hidden="true"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"/></svg>
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>

                <div x-show="cargandoDestinatarios" class="flex items-center justify-center gap-2 py-4 text-xs text-[var(--theme-text-muted)]">
                    <x-ui.spinner size="sm" />
                    Cargando usuarios…
                </div>

                <p x-show="errorDestinatarios" class="py-4 text-center text-xs text-[var(--theme-danger)]">
                    No se pudo cargar la lista de usuarios.
                    <button type="button" class="font-medium underline" x-on:click="destinatarios = null; abrirDestinatarios()">Reintentar</button>
                </p>

                <p
                    x-show="destinatarios !== null && ! cargandoDestinatarios && destinatariosFiltrados.length === 0"
                    class="py-4 text-center text-xs text-[var(--theme-text-muted)]"
                    x-text="busqueda.trim() ? 'Ningún usuario coincide con la búsqueda.' : 'No hay otros usuarios que puedan recibir equipos.'"
                ></p>

                <button
                    type="button"
                    x-on:click="confirmarDestinos()"
                    x-bind:disabled="totalDestinos === 0 || enviando"
                    class="mt-3 inline-flex h-11 w-full items-center justify-center gap-2 rounded-[var(--radius-md)] bg-[var(--theme-primary)] text-sm font-medium text-white disabled:opacity-50"
                >
                    <span x-show="enviando"><x-ui.spinner size="sm" /></span>
                    <span x-text="totalDestinos === 0 ? 'Elige al menos a una persona' : (totalDestinos === 1 ? 'Enviar' : 'Enviar a ' + totalDestinos + ' personas')"></span>
                </button>
            </div>

            {{-- Enviado (vuelve solo a la cámara) --}}
            <div x-show="estado === 'enviado'" class="flex items-center gap-3 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[var(--theme-success-soft)] text-[var(--theme-success)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12.5l4.5 4.5L19 7"/>
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="text-sm font-medium text-[var(--theme-text-strong)]" x-text="mensaje"></p>
                    <p class="text-xs text-[var(--theme-text-muted)]">Prepara el siguiente código…</p>
                </div>
            </div>

            {{-- Error --}}
            <div x-show="estado === 'error'">
                <p class="text-sm text-[var(--theme-text-strong)]" x-text="mensaje"></p>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        x-on:click="cerrar()"
                        class="inline-flex h-11 items-center justify-center rounded-[var(--radius-md)] border border-[var(--theme-border-strong)] text-sm font-medium"
                    >
                        Cerrar
                    </button>

                    <button
                        type="button"
                        x-on:click="reanudar()"
                        class="inline-flex h-11 items-center justify-center rounded-[var(--radius-md)] bg-[var(--theme-primary)] text-sm font-medium text-white"
                    >
                        Volver a intentar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
