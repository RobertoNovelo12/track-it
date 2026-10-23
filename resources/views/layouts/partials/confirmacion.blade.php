{{-- ============================================================
    VENTANA DE CONFIRMACIÓN DEL SISTEMA
    ------------------------------------------------------------
    Reemplaza el confirm() del navegador. Se abre con:

        $dispatch('confirmar', {
            titulo: 'Eliminar…',
            mensaje: 'Esta acción no se puede deshacer.',
            confirmar: 'Eliminar',      // texto del botón (opcional)
            cancelar: 'Cancelar',       // opcional
            peligro: true,              // botón rojo (opcional)
            accion: () => $wire.metodo(),
        })

    «accion» puede devolver una promesa: el botón muestra
    «Procesando…» hasta que termine y luego se cierra.

    Desde JavaScript, como reemplazo directo de confirm():
        if (! await window.trackitConfirmar({ titulo: '…', peligro: true })) return;

    El foco queda dentro de la ventana mientras está abierta (Tab no
    se escapa) y al cerrarla vuelve al botón que la abrió.
============================================================ --}}
<div
    x-data="{
        abierto: false,
        procesando: false,
        titulo: '',
        mensaje: '',
        textoConfirmar: 'Confirmar',
        textoCancelar: 'Cancelar',
        peligro: false,
        accion: null,
        alCancelar: null,

        init() {
            window.trackitConfirmar = (datos = {}) => new Promise((resolve) => {
                this.abrir({
                    ...datos,
                    accion: () => resolve(true),
                    alCancelar: () => resolve(false),
                });
            });
        },

        abrir(datos = {}) {
            this.titulo = datos.titulo ?? '¿Confirmar acción?';
            this.mensaje = datos.mensaje ?? '';
            this.textoConfirmar = datos.confirmar ?? 'Confirmar';
            this.textoCancelar = datos.cancelar ?? 'Cancelar';
            this.peligro = Boolean(datos.peligro);
            this.accion = typeof datos.accion === 'function' ? datos.accion : null;
            this.alCancelar = typeof datos.alCancelar === 'function' ? datos.alCancelar : null;
            this.procesando = false;
            this.abierto = true;

            this.$nextTick(() => this.$refs.cancelar?.focus());
        },

        cerrar() {
            if (this.procesando) return;
            this.abierto = false;
            this.accion = null;
            this.alCancelar?.();
            this.alCancelar = null;
        },

        async aceptar() {
            if (this.procesando) return;

            this.alCancelar = null;

            if (! this.accion) {
                this.abierto = false;
                return;
            }

            this.procesando = true;

            try {
                await this.accion();
            } finally {
                this.procesando = false;
                this.abierto = false;
                this.accion = null;
            }
        },
    }"
    x-on:confirmar.window="abrir($event.detail ?? {})"
    x-on:keydown.escape.window="abierto && cerrar()"
>
    <div
        x-show="abierto"
        x-cloak
        class="fixed inset-0 z-[210] flex items-center justify-center p-4"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="confirmacion-titulo"
        aria-describedby="confirmacion-mensaje"
    >
        <div
            x-show="abierto"
            x-transition.opacity.duration.150ms
            class="absolute inset-0 bg-[var(--theme-overlay)] backdrop-blur-[2px]"
            x-on:click="cerrar()"
        ></div>

        <div
            x-show="abierto"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-trap.noscroll="abierto"
            class="relative z-10 w-full max-w-sm overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl"
        >
            <div class="flex items-start gap-3 px-5 pt-5">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    x-bind:class="peligro ? 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]' : 'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]'"
                >
                    <svg x-show="peligro" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h16M10 11v6M14 11v6"/>
                        <path d="M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/>
                    </svg>
                    <svg x-show="! peligro" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 8v5M12 16h.01"/>
                    </svg>
                </span>

                <div class="min-w-0 pt-0.5">
                    <h2 id="confirmacion-titulo" class="text-sm font-semibold text-[var(--theme-text-strong)]" x-text="titulo"></h2>
                    <p id="confirmacion-mensaje" class="mt-1 text-xs leading-relaxed text-[var(--theme-text-muted)]" x-text="mensaje"></p>
                </div>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-5 py-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-ref="cancelar"
                    x-on:click="cerrar()"
                    x-bind:disabled="procesando"
                    class="h-9 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"
                    x-text="textoCancelar"
                ></button>

                <button
                    type="button"
                    x-on:click="aceptar()"
                    x-bind:disabled="procesando"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-md px-4 text-xs font-medium text-white transition-colors disabled:cursor-wait disabled:opacity-80"
                    x-bind:class="peligro ? 'bg-[var(--theme-danger)] hover:opacity-90' : 'bg-[var(--theme-primary)] hover:bg-[var(--theme-primary-hover)]'"
                >
                    <span x-show="procesando"><x-ui.spinner size="sm" /></span>
                    <span x-text="procesando ? 'Procesando…' : textoConfirmar"></span>
                </button>
            </div>
        </div>
    </div>
</div>
