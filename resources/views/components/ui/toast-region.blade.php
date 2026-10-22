{{-- ============================================================
    AVISOS GLOBALES (TOAST)
    ------------------------------------------------------------
    Se incluye una sola vez en layouts/app.blade.php.

    Desde Livewire:
        $this->dispatch('toast', type: 'success', message: 'Equipo guardado.');
        $this->dispatch('toast', type: 'error', title: 'No se pudo guardar', message: '...');

    Desde JavaScript / Alpine:
        window.trackitToast('success', 'Copiado al portapapeles');
        $dispatch('toast', { type: 'info', message: '...' })

    Tipos: success | error | warning | info | loading

    «loading» (proceso en curso) no se cierra solo y muestra un
    indicador girando. trackitToast() devuelve su id para cambiarlo
    cuando termine:
        const id = window.trackitToast('loading', 'Esto puede tardar…', 'Generando PDF…');
        window.trackitToastActualizar(id, 'success', 'La descarga comenzó.', 'Archivo listo');
    - Máximo 3 visibles; el más antiguo sale primero.
    - Éxito/info: 4 s. Error/advertencia: 7 s (más tiempo para leer).
    - Se pausan al pasar el cursor. Anunciados a lectores de pantalla.
============================================================ --}}
<div
    x-data="{
        avisos: [],
        siguiente: 1,

        /*
         * Se registra aquí y no en x-init: Alpine ejecuta la función que
         * devuelve una expresión de x-init, y eso creaba un aviso vacío
         * en cada carga de página.
         */
        init() {
            window.trackitToast = (type, message, title = null) =>
                this.agregar({ type, message, title });

            window.trackitToastActualizar = (id, type, message, title = null) =>
                this.actualizar(id, { type, message, title });
        },

        agregar(detalle) {
            const datos = Array.isArray(detalle) ? (detalle[0] ?? {}) : (detalle ?? {});

            /*
             * Un aviso sin título ni mensaje no informa nada: se ignora
             * (antes salía solo «Aviso»). La advertencia en consola
             * incluye la pila de llamadas para ubicar quién lo envió.
             */
            const texto = (valor) => typeof valor === 'string' ? valor.trim() : '';

            if (texto(datos.message) === '' && texto(datos.title) === '') {
                console.warn('[Track-IT] Aviso vacío ignorado', detalle, new Error().stack);

                return null;
            }

            const tipo = this.tipoValido(datos.type);

            const aviso = {
                id: this.siguiente++,
                tipo,
                titulo: datos.title ?? this.titulos[tipo],
                mensaje: datos.message ?? '',
                visible: true,
                restante: this.duracion(tipo),
                timer: null,
                inicio: 0,
            };

            this.avisos.push(aviso);

            if (this.avisos.length > 3) {
                // Un proceso en curso no se quita para hacer espacio.
                const antiguo = this.avisos.find(item => item.tipo !== 'loading') ?? this.avisos[0];
                this.cerrar(antiguo.id);
            }

            this.$nextTick(() => this.reanudar(aviso.id));

            return aviso.id;
        },

        /*
         * Cambia un aviso existente (p. ej. «Generando…» → «Archivo
         * listo»). Si ya se cerró, se muestra uno nuevo.
         */
        actualizar(id, detalle) {
            const aviso = this.buscar(id);

            if (! aviso || ! aviso.visible) {
                return this.agregar(detalle);
            }

            clearTimeout(aviso.timer);
            aviso.timer = null;
            aviso.tipo = this.tipoValido(detalle.type);
            aviso.titulo = detalle.title ?? this.titulos[aviso.tipo];
            aviso.mensaje = detalle.message ?? '';
            aviso.restante = this.duracion(aviso.tipo);

            this.reanudar(id);

            return id;
        },

        titulos: {
            success: 'Listo',
            error: 'Algo salió mal',
            warning: 'Atención',
            info: 'Aviso',
            loading: 'Procesando…',
        },

        tipoValido(tipo) {
            return ['success', 'error', 'warning', 'info', 'loading'].includes(tipo) ? tipo : 'info';
        },

        // null = no se cierra solo (proceso en curso).
        duracion(tipo) {
            if (tipo === 'loading') return null;

            return tipo === 'error' || tipo === 'warning' ? 7000 : 4000;
        },

        buscar(id) {
            return this.avisos.find(aviso => aviso.id === id);
        },

        pausar(id) {
            const aviso = this.buscar(id);

            if (! aviso || ! aviso.timer || aviso.restante === null) return;

            clearTimeout(aviso.timer);
            aviso.timer = null;
            aviso.restante -= Date.now() - aviso.inicio;
        },

        reanudar(id) {
            const aviso = this.buscar(id);

            if (! aviso || aviso.restante === null) return;

            aviso.inicio = Date.now();
            aviso.timer = setTimeout(() => this.cerrar(id), Math.max(aviso.restante, 1500));
        },

        cerrar(id) {
            const aviso = this.buscar(id);

            if (! aviso) return;

            clearTimeout(aviso.timer);
            aviso.visible = false;

            setTimeout(() => {
                this.avisos = this.avisos.filter(item => item.id !== id);
            }, 200);
        },
    }"


    @toast.window="agregar($event.detail)"

    class="pointer-events-none fixed inset-x-4 bottom-4 z-[200] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-6 sm:bottom-6"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="aviso in avisos" :key="aviso.id">
        <div
            x-show="aviso.visible"
            x-transition:enter="transition ease-out duration-[220ms]"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-[160ms]"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"

            @mouseenter="pausar(aviso.id)"
            @mouseleave="reanudar(aviso.id)"

            :role="aviso.tipo === 'error' ? 'alert' : 'status'"
            :aria-busy="aviso.tipo === 'loading' ? 'true' : 'false'"

            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-[var(--radius-lg)] border border-[var(--theme-border)] bg-[var(--theme-surface)] px-4 py-3 theme-shadow-xl"
        >
            {{-- Icono por tipo --}}
            <span
                class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-[var(--theme-success-soft)] text-[var(--theme-success)]': aviso.tipo === 'success',
                    'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]': aviso.tipo === 'error',
                    'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]': aviso.tipo === 'warning',
                    'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]': aviso.tipo === 'info' || aviso.tipo === 'loading',
                }"
            >
                <svg x-show="aviso.tipo === 'loading'" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.4" opacity="0.25"/>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                </svg>

                <svg x-show="aviso.tipo !== 'loading'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path x-show="aviso.tipo === 'success'" d="M5 12.5l4.5 4.5L19 7"/>
                    <path x-show="aviso.tipo === 'error'" d="M6 6l12 12M18 6L6 18"/>
                    <path x-show="aviso.tipo === 'warning'" d="M12 8v5M12 16.5v.5"/>
                    <path x-show="aviso.tipo === 'info'" d="M12 11v5M12 7.5v.5"/>
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-[var(--theme-text-strong)]" x-text="aviso.titulo"></p>
                <p
                    x-show="aviso.mensaje"
                    class="mt-0.5 text-xs leading-relaxed text-[var(--theme-text-muted)]"
                    x-text="aviso.mensaje"
                ></p>
            </div>

            <button
                type="button"
                @click="cerrar(aviso.id)"
                class="-mr-1 -mt-0.5 rounded-[var(--radius-sm)] p-1 text-[var(--theme-text-muted)] transition-colors duration-[var(--motion-fast)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]"
                aria-label="Cerrar aviso"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>
    </template>
</div>
