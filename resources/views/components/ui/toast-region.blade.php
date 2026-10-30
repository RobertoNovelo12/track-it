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

    Botón de acción opcional (p. ej. «Deshacer»): al pulsarlo se envía
    un evento de Livewire y el aviso se cierra.
        $this->dispatch('toast', type: 'success', message: '…',
            accion: ['texto' => 'Deshacer', 'evento' => 'mi-evento', 'datos' => [...]]);

    Tipos: success | error | warning | info | loading

    «loading» (proceso en curso) no se cierra solo y muestra una barra
    de avance. El servidor no informa cuánto lleva: la barra avanza cada
    vez más lento (sin llegar al final) y se llena al terminar.
    trackitToast() devuelve su id para cambiarlo cuando termine:
        const id = window.trackitToast('loading', 'Esto puede tardar…', 'Generando PDF…');
        window.trackitToastActualizar(id, 'success', 'La descarga comenzó.', 'Archivo listo');
    O para solo cerrarlo (la barra se llena y se va):
        window.trackitToastTerminar(id);
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

            window.trackitToastTerminar = (id) => this.terminar(id);
        },

        /*
         * Barra de avance de «loading»: se acerca al 90 % cada vez más
         * lento; al terminar se llena (100 %) y se queda un momento.
         */
        empezarAvance(aviso) {
            aviso.progreso = 4;
            aviso.conBarra = true;
            clearInterval(aviso.avance);

            aviso.avance = setInterval(() => {
                const actual = this.buscar(aviso.id);

                if (! actual || actual.tipo !== 'loading') {
                    clearInterval(aviso.avance);

                    return;
                }

                actual.progreso += (90 - actual.progreso) * 0.04;
            }, 200);
        },

        completarAvance(aviso) {
            clearInterval(aviso.avance);
            aviso.avance = null;

            if (! aviso.conBarra) return;

            aviso.progreso = 100;

            // La barra llena se ve un instante y luego se oculta.
            setTimeout(() => {
                const actual = this.buscar(aviso.id);

                if (actual && actual.tipo !== 'loading') actual.conBarra = false;
            }, 700);
        },

        terminar(id) {
            const aviso = this.buscar(id);

            if (! aviso) return;

            this.completarAvance(aviso);
            setTimeout(() => this.cerrar(id), 450);
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
                accion: datos.accion?.texto && datos.accion?.evento ? datos.accion : null,
                visible: true,
                // Con acción, más tiempo para alcanzar a pulsarla.
                restante: datos.accion ? 8000 : this.duracion(tipo),
                timer: null,
                inicio: 0,
                progreso: 0,
                conBarra: false,
                avance: null,
                temblar: false,
            };

            this.avisos.push(aviso);

            if (tipo === 'loading') {
                this.empezarAvance(this.buscar(aviso.id));
            }

            if (this.avisos.length > 3) {
                // Un proceso en curso no se quita para hacer espacio.
                const antiguo = this.avisos.find(item => item.tipo !== 'loading') ?? this.avisos[0];
                this.cerrar(antiguo.id);
            }

            this.$nextTick(() => {
                this.reanudar(aviso.id);

                if (tipo !== 'loading') this.sacudir(aviso.id);
            });

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

            const eraCarga = aviso.tipo === 'loading';

            aviso.tipo = this.tipoValido(detalle.type);

            if (aviso.tipo === 'loading' && ! eraCarga) {
                this.empezarAvance(aviso);
            } else if (eraCarga && aviso.tipo !== 'loading') {
                this.completarAvance(aviso);
            }
            aviso.titulo = detalle.title ?? this.titulos[aviso.tipo];
            aviso.mensaje = detalle.message ?? '';
            aviso.restante = this.duracion(aviso.tipo);

            this.reanudar(id);

            if (aviso.tipo !== 'loading') this.sacudir(id);

            return id;
        },

        /* Temblor corto para que el aviso se note (se omite con «reducir movimiento»). */
        sacudir(id) {
            const aviso = this.buscar(id);

            if (! aviso) return;

            aviso.temblar = false;

            requestAnimationFrame(() => {
                aviso.temblar = true;
                setTimeout(() => { aviso.temblar = false; }, 650);
            });
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

        ejecutarAccion(aviso) {
            const accion = aviso.accion;

            this.cerrar(aviso.id);

            if (accion) {
                window.Livewire?.dispatch(accion.evento, accion.datos ?? {});
            }
        },

        cerrar(id) {
            const aviso = this.buscar(id);

            if (! aviso) return;

            clearTimeout(aviso.timer);
            clearInterval(aviso.avance);
            aviso.visible = false;

            setTimeout(() => {
                this.avisos = this.avisos.filter(item => item.id !== id);
            }, 200);
        },
    }"


    @toast.window="agregar($event.detail)"

    id="trackitToastRegion"

    class="pointer-events-none fixed inset-x-4 top-[max(1rem,env(safe-area-inset-top))] z-[200] flex flex-col items-start gap-2 sm:inset-x-auto sm:left-6 sm:top-6"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="aviso in avisos" :key="aviso.id">
        <div
            x-show="aviso.visible"
            x-transition:enter="transition ease-out duration-[220ms]"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-[160ms]"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"

            @mouseenter="pausar(aviso.id)"
            @mouseleave="reanudar(aviso.id)"

            :role="aviso.tipo === 'error' ? 'alert' : 'status'"
            :aria-busy="aviso.tipo === 'loading' ? 'true' : 'false'"

            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-[var(--radius-lg)] border-2 bg-[var(--theme-surface)] px-4 py-3.5 shadow-2xl theme-shadow-xl"
            :class="{
                'trackit-temblar': aviso.temblar,
                'border-[var(--theme-success)]': aviso.tipo === 'success',
                'border-[var(--theme-danger)]': aviso.tipo === 'error',
                'border-[var(--theme-warning)]': aviso.tipo === 'warning',
                'border-[var(--theme-primary)]': aviso.tipo === 'info' || aviso.tipo === 'loading',
            }"
        >
            {{-- Icono por tipo --}}
            <span
                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-[var(--theme-success-soft)] text-[var(--theme-success)]': aviso.tipo === 'success',
                    'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]': aviso.tipo === 'error',
                    'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]': aviso.tipo === 'warning',
                    'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]': aviso.tipo === 'info' || aviso.tipo === 'loading',
                }"
            >
                <svg x-show="aviso.tipo === 'loading'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M7.5 10.5 12 15l4.5-4.5M5 19.5h14"/></svg>

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

                {{-- Barra de avance (procesos en curso) --}}
                <div
                    x-show="aviso.conBarra"
                    class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-[var(--theme-surface-soft)]"
                    role="progressbar"
                    aria-label="Avance"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-valuenow="Math.round(aviso.progreso)"
                >
                    <div
                        class="h-full rounded-full transition-[width] duration-300 ease-out motion-reduce:transition-none"
                        :class="aviso.tipo === 'error' ? 'bg-[var(--theme-danger)]' : (aviso.tipo === 'loading' ? 'bg-[var(--theme-primary)]' : 'bg-[var(--theme-success)]')"
                        :style="{ width: aviso.progreso + '%' }"
                    ></div>
                </div>
            </div>

            <button
                type="button"
                x-show="aviso.accion"
                @click="ejecutarAccion(aviso)"
                class="-my-1 shrink-0 self-center rounded-[var(--radius-sm)] px-2.5 py-1.5 text-xs font-semibold text-[var(--theme-primary)] transition-colors hover:bg-[var(--theme-primary-soft)]"
                x-text="aviso.accion?.texto"
            ></button>

            <button
                type="button"
                @click="cerrar(aviso.id)"
                class="-mr-1 -mt-0.5 rounded-[var(--radius-sm)] p-1 text-[var(--theme-text-muted)] transition-colors duration-[var(--motion-fast)] hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-text)]"
                aria-label="Cerrar aviso"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><use href="{{ \App\Support\Icono::url('i-c00fbb2') }}"></use></svg>
            </button>
        </div>
    </template>
</div>

<style>
    /* Temblor horizontal corto al aparecer un aviso. */
    @keyframes trackit-temblar {
        0%, 100% { transform: translateX(0); }
        15% { transform: translateX(-7px) rotate(-1deg); }
        30% { transform: translateX(6px) rotate(1deg); }
        45% { transform: translateX(-5px); }
        60% { transform: translateX(4px); }
        75% { transform: translateX(-2px); }
    }

    .trackit-temblar {
        animation: trackit-temblar 0.6s ease-in-out;
    }

    @media (prefers-reduced-motion: reduce) {
        .trackit-temblar {
            animation: none;
        }
    }
</style>
