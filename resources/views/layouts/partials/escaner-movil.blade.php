{{-- ============================================================
    ESCÁNER DE CÓDIGOS DE BARRAS (teléfono)
    ------------------------------------------------------------
    Se abre con el botón del buscador en móvil ($dispatch('abrir-escaner')).
    Al leer un código pregunta qué hacer:
    - Abrir aquí: ver el equipo en el teléfono.
    - Enviar a la PC: llega a «Equipos recibidos» de la misma cuenta
      (bandeja personal en la barra superior de escritorio). La cámara
      sigue abierta para escanear el siguiente (ráfaga).
    Lectura: resources/js/escaner.js (@zxing/browser). Exige HTTPS.
============================================================ --}}
<div
    x-data="{
        abierto: false,
        estado: 'camara',      // camara | consultando | equipo | enviado | error
        mensaje: '',
        equipo: null,
        puedeEnviar: false,
        enviando: false,
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
            this.estado = 'camara';
            this.equipo = null;
            this.mensaje = '';

            try {
                await window.trackitEscaner.iniciar(this.$refs.video, (codigo) => this.leido(codigo));
            } catch (error) {
                const nombre = error?.name ?? '';

                this.mostrarError(
                    nombre === 'NotAllowedError'
                        ? 'Permite el acceso a la cámara en tu navegador para escanear.'
                        : nombre === 'NotFoundError'
                            ? 'No se encontró una cámara en este dispositivo.'
                            : 'No se pudo abrir la cámara. Inténtalo de nuevo.'
                );
            }
        },

        cerrar() {
            clearTimeout(this.temporizador);
            window.trackitEscaner?.detener();
            this.abierto = false;
            document.documentElement.style.overflow = '';
        },

        mostrarError(texto) {
            window.trackitEscaner?.detener();
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
            await window.trackitEscaner.detener();

            try {
                const respuesta = await fetch(
                    @js(url('/equipos/escaneo')) + '/' + encodeURIComponent(codigo),
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const datos = await respuesta.json().catch(() => ({}));

                if (! respuesta.ok || ! datos.encontrado) {
                    this.mostrarError(datos.mensaje ?? ('No hay ningún equipo con el código ' + codigo + '.'));

                    return;
                }

                this.equipo = datos.equipo;
                this.puedeEnviar = Boolean(datos.puede_enviar);
                this.estado = 'equipo';
            } catch (error) {
                this.mostrarError('Sin conexión con el servidor. Revisa tu conexión e inténtalo de nuevo.');
            }
        },

        abrirAqui() {
            const url = this.equipo?.url;
            this.cerrar();

            if (url) {
                window.Livewire?.navigate ? window.Livewire.navigate(url) : (window.location.href = url);
            }
        },

        async enviar() {
            if (! this.equipo || this.enviando) {
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
                    body: JSON.stringify({ id_equipo: this.equipo.id }),
                });
                const datos = await respuesta.json().catch(() => ({}));

                if (! respuesta.ok) {
                    this.mostrarError(datos.message ?? 'No se pudo enviar el equipo. Inténtalo de nuevo.');

                    return;
                }

                this.mensaje = datos.estado === 'duplicado'
                    ? 'Este equipo ya estaba en tu bandeja.'
                    : 'Enviado a tu PC (' + datos.pendientes + (datos.pendientes === 1 ? ' equipo' : ' equipos') + ' en la bandeja).';
                this.estado = 'enviado';

                // Ráfaga: volver a la cámara para el siguiente equipo.
                this.temporizador = setTimeout(() => this.abierto && this.reanudar(), 1300);
            } catch (error) {
                this.mostrarError('Sin conexión con el servidor. Revisa tu conexión e inténtalo de nuevo.');
            } finally {
                this.enviando = false;
            }
        },
    }"
    x-on:abrir-escaner.window="abrir()"
    x-on:keydown.escape.window="abierto && cerrar()"
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
            <video x-ref="video" class="absolute inset-0 h-full w-full object-cover" playsinline muted></video>

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
            x-show="['equipo', 'enviado', 'error'].includes(estado)"
            x-transition.opacity
            class="rounded-t-2xl bg-[var(--theme-surface)] px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-4 text-[var(--theme-text)]"
        >
            {{-- Equipo encontrado --}}
            <template x-if="estado === 'equipo' && equipo">
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

                        <button
                            type="button"
                            x-show="puedeEnviar"
                            x-on:click="enviar()"
                            x-bind:disabled="enviando"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-[var(--radius-md)] bg-[var(--theme-primary)] text-sm font-medium text-white disabled:opacity-60"
                        >
                            <span x-show="enviando"><x-ui.spinner size="sm" /></span>
                            Enviar a la PC
                        </button>
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
