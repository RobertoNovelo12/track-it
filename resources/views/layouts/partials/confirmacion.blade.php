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
            class="relative z-10 w-full max-w-md overflow-hidden rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] theme-shadow-xl"
        >
            {{--
                Diseño sobrio: sin círculo de color ni ícono grande. Un ícono
                pequeño junto al título indica si la acción es destructiva.
            --}}
            <div class="px-5 pt-5">
                <div class="flex items-center gap-2">
                    <svg x-show="peligro" class="h-4 w-4 shrink-0 text-[var(--theme-danger)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-a049c21') }}"></use></svg>
                    <svg x-show="! peligro" class="h-4 w-4 shrink-0 text-[var(--theme-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-7eacb6d') }}"></use></svg>

                    <h2 id="confirmacion-titulo" class="text-[15px] font-semibold leading-snug text-[var(--theme-text-strong)]" x-text="titulo"></h2>
                </div>

                <p id="confirmacion-mensaje" class="mt-2 text-[13px] leading-relaxed text-[var(--theme-text-muted)]" x-text="mensaje"></p>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-2 px-5 pb-5 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-ref="cancelar"
                    x-on:click="cerrar()"
                    x-bind:disabled="procesando"
                    class="h-9 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-[13px] font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"
                    x-text="textoCancelar"
                ></button>

                {{-- Rojo sólido y oscuro en ambos temas (no el rojo claro del modo oscuro). --}}
                <button
                    type="button"
                    x-on:click="aceptar()"
                    x-bind:disabled="procesando"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-md px-4 text-[13px] font-medium text-white transition-colors disabled:cursor-wait disabled:opacity-80"
                    x-bind:class="peligro ? 'confirmacion-boton-peligro' : 'bg-[var(--theme-primary)] hover:bg-[var(--theme-primary-hover)]'"
                >
                    <span x-show="procesando"><x-ui.spinner size="sm" /></span>
                    <span x-text="procesando ? 'Procesando…' : textoConfirmar"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Botón de acción destructiva: rojo sobrio (contraste 6.5:1 con el texto blanco). */
    .confirmacion-boton-peligro {
        background-color: #B42318;
        border: 1px solid #B42318;
    }

    .confirmacion-boton-peligro:hover {
        background-color: #9A1E14;
        border-color: #9A1E14;
    }
</style>
