{{-- ============================================================
    MODAL: RESTABLECER CONTRASEÑA (contraseña temporal)
    ------------------------------------------------------------
    Se abre con: $dispatch('abrir-restablecer-contrasena', { id, nombre, username })
    (desde «Restablecimiento de contraseña» o «Ver detalles»).
    La contraseña temporal se muestra una sola vez.
============================================================ --}}
<div
    x-data="{
        abierto: false,
        usuario: null,
        paso: 'confirmar',      // confirmar | resultado
        cargando: false,
        error: '',
        temporal: '',
        copiado: false,

        abrir(datos) {
            this.usuario = datos;
            this.paso = 'confirmar';
            this.cargando = false;
            this.error = '';
            this.temporal = '';
            this.copiado = false;
            this.abierto = true;
            document.body.style.overflow = 'hidden';
        },

        cerrar() {
            if (this.cargando) return;
            this.abierto = false;
            this.temporal = '';
            document.body.style.overflow = '';
        },

        async restablecer() {
            if (this.cargando || ! this.usuario) return;
            this.cargando = true;
            this.error = '';

            try {
                const respuesta = await $wire.restablecerContrasena(this.usuario.id);

                if (respuesta?.ok) {
                    this.temporal = respuesta.temporal;
                    this.paso = 'resultado';
                } else {
                    this.error = respuesta?.mensaje || 'No se pudo restablecer la contraseña.';
                }
            } catch (e) {
                this.error = window.trackitMensajeError(e, 'restablecer la contraseña');
            } finally {
                this.cargando = false;
            }
        },

        async copiar() {
            try {
                await navigator.clipboard.writeText(this.temporal);
                this.copiado = true;
                setTimeout(() => this.copiado = false, 2000);
            } catch (e) {
                this.$refs.temporal?.select();
            }
        },
    }"
    x-on:abrir-restablecer-contrasena.window="abrir($event.detail)"
    x-on:keydown.escape.window="abierto && cerrar()"
>
    <template x-teleport="body">
        <div
            x-show="abierto"
            x-cloak
            class="fixed inset-0 z-[120] flex items-center justify-center px-4 py-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-password-title"
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
                class="relative z-10 w-full max-w-md rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] theme-shadow-xl"
            >
                <div class="border-b border-[var(--theme-border)] px-5 py-4">
                    <h3 id="reset-password-title" class="text-base font-semibold text-[var(--theme-text-strong)]">
                        Restablecer contraseña
                    </h3>
                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                        <span x-text="usuario?.nombre"></span>
                        <span x-show="usuario?.username" x-text="'(@' + usuario?.username + ')'"></span>
                    </p>
                </div>

                {{-- Confirmar --}}
                <div x-show="paso === 'confirmar'" class="space-y-3 px-5 py-4 text-sm text-[var(--theme-text)]">
                    <p>
                        Se generará una <strong>contraseña temporal</strong> y la contraseña actual dejará de funcionar.
                        Al iniciar sesión, el usuario deberá elegir una nueva.
                    </p>
                    <p class="text-xs text-[var(--theme-text-muted)]">
                        Entrégasela en persona o por un medio seguro. Solo podrás verla una vez.
                    </p>
                    <p x-show="error" x-text="error" class="rounded-md bg-[var(--theme-danger-soft)] px-3 py-2 text-xs text-[var(--theme-danger)]"></p>
                </div>

                {{-- Resultado --}}
                <div x-show="paso === 'resultado'" x-cloak class="space-y-3 px-5 py-4">
                    <p class="text-sm text-[var(--theme-text)]">Contraseña temporal:</p>

                    <div class="flex items-center gap-2">
                        <input
                            x-ref="temporal"
                            type="text"
                            readonly
                            x-bind:value="temporal"
                            x-on:focus="$el.select()"
                            class="h-11 w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface-soft)] px-3 font-mono text-base tracking-wider text-[var(--theme-text-strong)]"
                            aria-label="Contraseña temporal"
                        >

                        <button
                            type="button"
                            x-on:click="copiar()"
                            class="inline-flex h-11 shrink-0 items-center justify-center rounded-md border border-[var(--theme-border-strong)] px-3 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)]"
                            x-text="copiado ? 'Copiada' : 'Copiar'"
                        ></button>
                    </div>

                    <p class="text-xs text-[var(--theme-warning)]">
                        Anótala ahora: al cerrar esta ventana ya no se podrá ver.
                    </p>
                </div>

                <div class="flex justify-end gap-2 border-t border-[var(--theme-border)] bg-[var(--theme-surface-soft)] px-5 py-4 rounded-b-xl">
                    <button
                        type="button"
                        x-on:click="cerrar()"
                        x-bind:disabled="cargando"
                        class="h-9 rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-4 text-xs font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] disabled:opacity-60"
                        x-text="paso === 'resultado' ? 'Listo' : 'Cancelar'"
                    ></button>

                    <button
                        type="button"
                        x-show="paso === 'confirmar'"
                        x-on:click="restablecer()"
                        x-bind:disabled="cargando"
                        class="inline-flex h-9 items-center gap-2 rounded-md bg-[var(--theme-primary)] px-4 text-xs font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-wait disabled:opacity-80"
                    >
                        <x-ui.spinner size="sm" x-show="cargando" />
                        <span x-text="cargando ? 'Generando…' : 'Generar contraseña temporal'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
