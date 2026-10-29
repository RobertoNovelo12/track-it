{{-- ============================================================
    ASIGNAR VARIOS EQUIPOS (App\Livewire\AsignacionVarios)
    Todo se arma en el navegador; solo «Asignar» envía al servidor.
============================================================ --}}
@php
    $campo = 'w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none placeholder:text-[var(--theme-text-muted)] focus:border-[var(--theme-primary)]';
    $etiqueta = 'mb-1.5 block text-xs text-[var(--theme-text-muted)]';
    $seccion = 'rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)] p-4 sm:p-6';
    $etiquetasUnidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas();
@endphp

<div
    x-data="{
        data: @js($datos),

        seleccion: [],
        agregarId: '',
        destino: 'persona',
        colaborador: '',
        numeroColaborador: '',
        idUnidadAsignar: '',
        idDepartamentoAsignar: '',
        idUbicacionAsignar: '',
        fechaAsignacion: '',
        idTipoAsignacion: '',
        observacionesAsignacion: '',

        errores: {},
        guardando: false,

        init() {
            this.seleccion = [...(this.data.preseleccion ?? [])];
            this.fechaAsignacion = this.data.defaults?.fechaAsignacion ?? '';
            this.idTipoAsignacion = this.data.defaults?.idTipoAsignacion ?? '';

            this.$watch('agregarId', valor => {
                if (! valor) return;
                this.agregar(valor);
                this.$nextTick(() => this.agregarId = '');
            });

            this.$watch('idUnidadAsignar', () => { this.idDepartamentoAsignar = ''; this.idUbicacionAsignar = ''; });
            this.$watch('idDepartamentoAsignar', () => { this.idUbicacionAsignar = ''; });
        },

        equipo(id) {
            return (this.data.equipos ?? []).find(e => String(e.value) === String(id)) ?? null;
        },

        get equiposRestantes() {
            return (this.data.equipos ?? []).filter(e => ! this.seleccion.includes(String(e.value)));
        },

        agregar(id) {
            id = String(id);
            if (this.seleccion.includes(id)) return;
            if (this.seleccion.length >= this.data.maximo) {
                window.trackitToast?.('warning', 'Puedes asignar hasta ' + this.data.maximo + ' equipos a la vez.', 'Demasiados equipos');
                return;
            }
            this.seleccion.push(id);
            delete this.errores.equipos;
        },

        quitar(id) {
            this.seleccion = this.seleccion.filter(x => x !== String(id));
        },

        get departamentosDisponibles() {
            const unidad = String(this.idUnidadAsignar ?? '');
            if (unidad === '') return [];
            return (this.data.departamentos ?? []).filter(d => d?.todas || (d?.unidades ?? []).includes(unidad));
        },

        get ubicacionesDisponibles() {
            const unidad = String(this.idUnidadAsignar ?? '');
            const departamento = String(this.idDepartamentoAsignar ?? '');
            if (unidad === '' || departamento === '') return [];
            return (this.data.ubicaciones ?? []).filter(u => {
                const suya = String(u?.unidad ?? '');
                if (suya !== '' && suya !== unidad) return false;
                const deps = u?.departamentos ?? [];
                return deps.length === 0 || deps.includes(departamento);
            });
        },

        colaboradorSinCuenta() {
            return this.destino === 'persona' && String(this.colaborador ?? '').startsWith('texto:');
        },

        error(campo) {
            const e = this.errores?.[campo];
            return Array.isArray(e) ? e[0] : (e ?? '');
        },

        async guardar() {
            if (this.guardando) return;

            this.errores = {};
            if (this.seleccion.length === 0) this.errores.equipos = ['Agrega al menos un equipo.'];
            if (this.destino === 'persona' && String(this.colaborador ?? '').trim() === '') this.errores.colaborador = ['Busca un usuario o escribe el nombre del colaborador.'];
            if (! this.idUnidadAsignar) this.errores.idUnidadAsignar = ['Selecciona un hotel.'];
            if (! this.idDepartamentoAsignar) this.errores.idDepartamentoAsignar = ['Selecciona un departamento.'];
            if (! this.fechaAsignacion) this.errores.fechaAsignacion = ['Selecciona la fecha de asignación.'];
            if (! this.idTipoAsignacion) this.errores.idTipoAsignacion = ['Selecciona el tipo de asignación.'];

            if (Object.keys(this.errores).length) {
                window.trackitToast?.('error', this.error(Object.keys(this.errores)[0]), 'Revisa el formulario');
                return;
            }

            this.guardando = true;

            const aviso = window.trackitToast?.(
                'loading',
                'Esto puede tardar unos segundos.',
                'Asignando ' + this.seleccion.length + (this.seleccion.length === 1 ? ' equipo' : ' equipos')
            ) ?? null;

            try {
                const resultado = await $wire.guardarVarios({
                    equipos: this.seleccion.map(Number),
                    destino: this.destino,
                    colaborador: this.destino === 'persona' ? this.colaborador : '',
                    numeroColaborador: this.destino === 'persona' ? this.numeroColaborador : '',
                    idUnidadAsignar: this.idUnidadAsignar,
                    idDepartamentoAsignar: this.idDepartamentoAsignar,
                    idUbicacionAsignar: this.idUbicacionAsignar,
                    fechaAsignacion: this.fechaAsignacion,
                    idTipoAsignacion: this.idTipoAsignacion,
                    observacionesAsignacion: this.observacionesAsignacion,
                });

                if (aviso) window.trackitToastTerminar?.(aviso);

                // El aviso de éxito se muestra al llegar (sesión).
                window.Livewire?.navigate
                    ? Livewire.navigate(resultado?.volver ?? @js(route('asignaciones.index')))
                    : (window.location.href = resultado?.volver ?? @js(route('asignaciones.index')));
            } catch (error) {
                this.errores = error?.errors ?? {};
                const primero = Object.keys(this.errores)[0];

                if (aviso && window.trackitToastActualizar) {
                    window.trackitToastActualizar(aviso, 'error', primero ? this.error(primero) : window.trackitMensajeError(error, 'asignar los equipos'), 'No se pudo asignar');
                } else {
                    window.trackitToast?.('error', primero ? this.error(primero) : 'No se pudo asignar. Intenta de nuevo.', 'No se pudo asignar');
                }
            } finally {
                this.guardando = false;
            }
        },
    }"
    class="space-y-5"
>
    {{-- EQUIPOS --}}
    <section class="{{ $seccion }}">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                    Equipos (<span x-text="seleccion.length"></span>)
                </h2>
                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Todos se asignan al mismo destino. Hasta {{ \App\Livewire\AsignacionVarios::MAXIMO }} a la vez.</p>
            </div>
        </div>

        @if ($noDisponibles !== [])
            <div class="mb-4 rounded-md border border-[var(--theme-warning)] bg-[var(--theme-warning-soft)] px-4 py-3 text-xs text-[var(--theme-text)]">
                No se agregaron {{ count($noDisponibles) }} {{ count($noDisponibles) === 1 ? 'equipo' : 'equipos' }} de tu selección porque ya están asignados o dados de baja:
                <strong class="font-semibold">{{ implode(', ', $noDisponibles) }}</strong>.
            </div>
        @endif

        <div class="max-w-xl">
            <x-searchable-select
                x-model="agregarId"
                x-options="equiposRestantes"
                label="Agregar equipo"
                placeholder="Buscar equipo disponible por código o nombre…"
                :show-clear="false"
            />
        </div>

        <ul class="mt-4 divide-y divide-[var(--theme-border)] rounded-md border border-[var(--theme-border)]" x-show="seleccion.length > 0" x-cloak>
            <template x-for="id in seleccion" :key="id">
                <li class="flex items-center gap-3 px-4 py-2.5">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-[var(--theme-text-strong)]" x-text="equipo(id)?.code ?? ('#' + id)"></p>
                        <p class="truncate text-xs text-[var(--theme-text-muted)]" x-text="equipo(id)?.name ?? ''"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="quitar(id)"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[var(--theme-text-muted)] transition-colors hover:bg-[var(--theme-surface-soft)] hover:text-[var(--theme-danger)]"
                        :aria-label="'Quitar ' + (equipo(id)?.code ?? '')"
                        title="Quitar de la lista"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </li>
            </template>
        </ul>

        <p x-show="seleccion.length === 0" class="mt-4 rounded-md border border-dashed border-[var(--theme-border-strong)] px-4 py-6 text-center text-sm text-[var(--theme-text-muted)]">
            Todavía no hay equipos. Búscalos arriba o selecciónalos en Inventario.
        </p>

        <p x-show="error('equipos')" x-cloak x-text="error('equipos')" class="mt-2 text-xs text-[var(--theme-danger)]"></p>
    </section>

    {{-- ASIGNAR A --}}
    <section class="{{ $seccion }}">
        <h2 class="mb-4 text-sm font-semibold text-[var(--theme-text-strong)]">Asignar a</h2>

        <div class="inline-flex rounded-md border border-[var(--theme-border-strong)] p-1" role="radiogroup" aria-label="Asignar a">
            <button type="button" role="radio" :aria-checked="(destino === 'persona').toString()" x-on:click="destino = 'persona'"
                class="h-9 rounded px-4 text-sm font-medium transition-colors"
                :class="destino === 'persona' ? 'bg-[var(--theme-primary)] text-white' : 'text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]'">
                Una persona
            </button>
            <button type="button" role="radio" :aria-checked="(destino === 'departamento').toString()" x-on:click="destino = 'departamento'"
                class="h-9 rounded px-4 text-sm font-medium transition-colors"
                :class="destino === 'departamento' ? 'bg-[var(--theme-primary)] text-white' : 'text-[var(--theme-text)] hover:bg-[var(--theme-surface-soft)]'">
                Un departamento
            </button>
        </div>

        <div x-show="destino === 'persona'" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="{{ $etiqueta }}">Colaborador *</label>
                <x-searchable-select
                    x-model="colaborador"
                    x-options="data.usuarios ?? []"
                    :show-clear="false"
                    allow-custom
                    placeholder="Busca un usuario o escribe un nombre"
                />
                <p x-show="colaboradorSinCuenta()" x-cloak class="mt-1.5 text-xs text-[var(--theme-warning)]">
                    Este colaborador no tiene cuenta. Se asignará igual y un administrador deberá crearla en Seguridad y roles.
                </p>
                <p x-show="error('colaborador')" x-cloak x-text="error('colaborador')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div x-show="colaboradorSinCuenta()" x-cloak>
                <label for="varios-numero" class="{{ $etiqueta }}">Número de colaborador</label>
                <input id="varios-numero" type="text" x-model="numeroColaborador" maxlength="255" placeholder="Opcional" class="{{ $campo }}">
            </div>
        </div>

        <p x-show="destino === 'departamento'" x-cloak class="mt-4 text-sm text-[var(--theme-text-muted)]">
            Los equipos quedan asignados al departamento que elijas abajo, sin una persona responsable.
            Sirve para equipos de uso común (impresoras, teléfonos de recepción, access points…).
        </p>
    </section>

    {{-- DÓNDE Y CUÁNDO --}}
    <section class="{{ $seccion }}">
        <h2 class="mb-4 text-sm font-semibold text-[var(--theme-text-strong)]">Dónde y cuándo</h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <x-searchable-select
                    x-model="idUnidadAsignar"
                    x-options="data.unidades"
                    :label="$etiquetasUnidad['unidad_singular'] . ' *'"
                    :placeholder="$etiquetasUnidad['buscar_unidad']"
                    :show-clear="false"
                />
                <p x-show="error('idUnidadAsignar')" x-cloak x-text="error('idUnidadAsignar')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div>
                <x-searchable-select
                    x-model="idDepartamentoAsignar"
                    x-options="departamentosDisponibles"
                    label="Departamento *"
                    placeholder="Selecciona un hotel primero"
                    x-placeholder="idUnidadAsignar === '' ? 'Selecciona un hotel primero' : 'Buscar departamento…'"
                    x-disabled="idUnidadAsignar === ''"
                    :show-clear="false"
                />
                <p x-show="error('idDepartamentoAsignar')" x-cloak x-text="error('idDepartamentoAsignar')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div>
                <x-searchable-select
                    x-model="idUbicacionAsignar"
                    x-options="ubicacionesDisponibles"
                    label="Ubicación"
                    placeholder="Selecciona un departamento primero"
                    x-placeholder="idDepartamentoAsignar === '' ? 'Selecciona un departamento primero' : (ubicacionesDisponibles.length ? 'Buscar ubicación…' : 'Sin ubicaciones para este departamento')"
                    x-disabled="idDepartamentoAsignar === '' || ! ubicacionesDisponibles.length"
                    clear-label="Sin ubicación"
                    :empty-as-null="false"
                />
            </div>

            <div>
                <label for="varios-fecha" class="{{ $etiqueta }}">Fecha de asignación *</label>
                <input id="varios-fecha" type="date" x-model="fechaAsignacion" class="{{ $campo }}">
                <p x-show="error('fechaAsignacion')" x-cloak x-text="error('fechaAsignacion')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div>
                <x-searchable-select
                    x-model="idTipoAsignacion"
                    x-options="data.tiposAsignacion"
                    label="Tipo de asignación *"
                    placeholder="Buscar tipo…"
                    :show-clear="false"
                />
                <p x-show="error('idTipoAsignacion')" x-cloak x-text="error('idTipoAsignacion')" class="mt-1 text-xs text-[var(--theme-danger)]"></p>
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <label for="varios-observaciones" class="{{ $etiqueta }}">Observaciones</label>
                <textarea id="varios-observaciones" x-model="observacionesAsignacion" rows="3" maxlength="2000" placeholder="Se guardan en cada asignación." class="{{ $campo }}"></textarea>
            </div>
        </div>
    </section>

    {{-- ACCIONES --}}
    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ $volverA }}"
            wire:navigate.hover
            class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-5 text-sm font-medium text-[var(--theme-text)] transition-colors hover:bg-[var(--theme-surface-soft)] sm:w-auto"
        >
            Cancelar
        </a>

        <button
            type="button"
            x-on:click="guardar()"
            :disabled="guardando || seleccion.length === 0"
            class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[var(--theme-primary)] px-5 text-sm font-medium text-white transition-colors hover:bg-[var(--theme-primary-hover)] disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
        >
            <span x-text="guardando ? 'Asignando…' : (seleccion.length === 1 ? 'Asignar 1 equipo' : 'Asignar ' + seleccion.length + ' equipos')">Asignar equipos</span>
        </button>
    </div>
</div>
