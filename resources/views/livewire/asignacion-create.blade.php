<div wire:key="assignment-create-ui">

    {{-- ?equipo=ID (enlace desde Editar equipo): el equipo llega preseleccionado. --}}
    <div
        x-init="
            $nextTick(() => {
                const equipo = new URLSearchParams(window.location.search).get('equipo') ?? '';

                if (equipo !== '' && (data?.equipos ?? []).some(opcion => String(opcion.value) === equipo)) {
                    idEquipoAsignar = equipo;
                }
            })
        "
        x-data="{
            data: @js($assignmentData),

            successVisible: false,
            successMessage: '',

            errorVisible: false,
            errorMessage: '',

            validationErrors: {},

            savingVisual: false,
            requestPending: false,

            spinnerTimer: null,
            successTimer: null,
            errorTimer: null,

            idEquipoAsignar: '',
            hostEquipo: '',

            // Selector: id del usuario o «texto:Nombre» (sin cuenta).
            colaborador: '',
            nombreColaborador: '',
            numeroColaborador: '',

            idUnidadAsignar: '',
            idDepartamentoAsignar: '',
            idUbicacionAsignar: '',

            fechaAsignacion: @js(
                $assignmentData['defaults']['fechaAsignacion'] ?? ''
            ),

            idTipoAsignacion: @js(
                $assignmentData['defaults']['idTipoAsignacion'] ?? ''
            ),

            observacionesAsignacion: '',

            /*
             * Colaborador: con cuenta toma nombre y número de la cuenta;
             * escrito a mano queda «sin cuenta» (se avisa debajo).
             */
            usuarioSeleccionado() {
                const valor = String(this.colaborador ?? '');

                return (this.data?.usuarios ?? []).find(
                    usuario => String(usuario.value) === valor
                ) ?? null;
            },

            colaboradorSinCuenta() {
                return String(this.colaborador ?? '').startsWith('texto:');
            },

            init() {
                this.$watch('colaborador', (valor) => {
                    this.clearValidationError('nombreColaborador');

                    const texto = String(valor ?? '');

                    if (texto.startsWith('texto:')) {
                        this.nombreColaborador = texto.slice(6);

                        return;
                    }

                    const usuario = this.usuarioSeleccionado();

                    this.nombreColaborador = usuario ? usuario.nombre : '';

                    if (usuario && usuario.numero) {
                        this.numeroColaborador = usuario.numero;
                    }
                });

                this.$watch(
                    'idEquipoAsignar',
                    (value, previous) => {
                        if (
                            String(value ?? '') ===
                            String(previous ?? '')
                        ) {
                            return;
                        }

                        this.loadSelectedEquipment();
                        this.clearValidationError(
                            'idEquipoAsignar'
                        );
                    }
                );

                this.$watch(
                    'idUnidadAsignar',
                    (value, previous) => {
                        if (
                            String(value ?? '') ===
                            String(previous ?? '')
                        ) {
                            return;
                        }

                        this.idDepartamentoAsignar = '';
                        this.idUbicacionAsignar = '';

                        this.clearValidationError(
                            'idUnidadAsignar'
                        );

                        this.clearValidationError(
                            'idDepartamentoAsignar'
                        );
                    }
                );

                this.$watch(
                    'idDepartamentoAsignar',
                    (value, previous) => {
                        if (
                            String(value ?? '') ===
                            String(previous ?? '')
                        ) {
                            return;
                        }

                        this.idUbicacionAsignar = '';

                        this.clearValidationError(
                            'idDepartamentoAsignar'
                        );
                    }
                );

                this.$watch(
                    'idTipoAsignacion',
                    () => {
                        this.clearValidationError(
                            'idTipoAsignacion'
                        );
                    }
                );
            },

            /*
             * Departamentos que atienden el hotel elegido:
             * los que atienden todos o los vinculados a él.
             */
            get departamentosDisponibles() {
                const unidadId =
                    String(
                        this.idUnidadAsignar ?? ''
                    );

                if (unidadId === '') {
                    return [];
                }

                return (
                    this.data?.departamentos ?? []
                ).filter(
                    departamento =>
                        departamento?.todas
                        || (departamento?.unidades ?? []).includes(unidadId)
                );
            },

            /*
             * Ubicaciones del hotel (o generales) y, si tienen
             * departamentos configurados, sólo los de ese departamento.
             */
            get ubicacionesDisponibles() {
                const unidadId =
                    String(
                        this.idUnidadAsignar ?? ''
                    );

                const departamentoId =
                    String(
                        this.idDepartamentoAsignar ?? ''
                    );

                if (unidadId === '' || departamentoId === '') {
                    return [];
                }

                return (
                    this.data?.ubicaciones ?? []
                ).filter(
                    ubicacion => {
                        const unidad =
                            String(ubicacion?.unidad ?? '');

                        if (unidad !== '' && unidad !== unidadId) {
                            return false;
                        }

                        const departamentos =
                            ubicacion?.departamentos ?? [];

                        return (
                            departamentos.length === 0
                            || departamentos.includes(departamentoId)
                        );
                    }
                );
            },

            loadSelectedEquipment() {
                this.hostEquipo = '';

                const equipoId =
                    String(
                        this.idEquipoAsignar ?? ''
                    );

                if (equipoId === '') {
                    return;
                }

                const equipo =
                    (
                        this.data?.equipos ?? []
                    ).find(
                        item =>
                            String(
                                item?.value ?? ''
                            ) === equipoId
                    );

                if (!equipo) {
                    this.idEquipoAsignar = '';

                    return;
                }

                this.hostEquipo =
                    String(
                        equipo.host ?? ''
                    );
            },

            clearValidationError(field) {
                if (
                    !this.validationErrors
                    || typeof this.validationErrors
                        !== 'object'
                    || !Object.prototype.hasOwnProperty.call(
                        this.validationErrors,
                        field
                    )
                ) {
                    return;
                }

                const errors = {
                    ...this.validationErrors
                };

                delete errors[field];

                this.validationErrors = errors;
            },

            showSuccess(message) {
                this.successMessage =
                    message
                    || 'El equipo fue asignado correctamente.';

                window.trackitToast?.('success', this.successMessage); /* aviso global */

                if (this.successTimer) {
                    clearTimeout(
                        this.successTimer
                    );
                }

                this.successTimer =
                    setTimeout(() => {
                        this.successVisible = false;
                    }, 3000);
            },

            showError(message) {
                this.errorMessage =
                    message
                    || 'No fue posible guardar la asignación.';

                window.trackitToast?.('error', this.errorMessage); /* aviso global */

                if (this.errorTimer) {
                    clearTimeout(
                        this.errorTimer
                    );
                }

                this.errorTimer =
                    setTimeout(() => {
                        this.errorVisible = false;
                    }, 5000);
            },

            captureSnapshot() {
                return {
                    tipoMovimiento:
                        'asignacion',

                    idEquipoAsignar:
                        String(
                            this.idEquipoAsignar ?? ''
                        ),

                    colaborador:
                        String(
                            this.colaborador ?? ''
                        ),

                    nombreColaborador:
                        String(
                            this.nombreColaborador ?? ''
                        ),

                    numeroColaborador:
                        String(
                            this.numeroColaborador ?? ''
                        ),

                    idUnidadAsignar:
                        String(
                            this.idUnidadAsignar ?? ''
                        ),

                    idDepartamentoAsignar:
                        String(
                            this.idDepartamentoAsignar ?? ''
                        ),

                    idUbicacionAsignar:
                        String(
                            this.idUbicacionAsignar ?? ''
                        ),

                    fechaAsignacion:
                        String(
                            this.fechaAsignacion ?? ''
                        ),

                    idTipoAsignacion:
                        String(
                            this.idTipoAsignacion ?? ''
                        ),

                    observacionesAsignacion:
                        String(
                            this.observacionesAsignacion
                            ?? ''
                        ),
                };
            },

            fieldError(field) {
                const error =
                    this.validationErrors?.[field];

                if (!error) {
                    return null;
                }

                if (
                    Array.isArray(error)
                ) {
                    return error[0] ?? null;
                }

                return (
                    typeof error === 'string'
                        ? error
                        : null
                );
            },

            firstError(error) {
                const errors =
                    error?.errors;

                if (
                    !errors
                    || typeof errors !== 'object'
                ) {
                    return null;
                }

                const firstKey =
                    Object.keys(errors)[0];

                if (!firstKey) {
                    return null;
                }

                const value =
                    errors[firstKey];

                if (
                    Array.isArray(value)
                ) {
                    return value[0] ?? null;
                }

                return (
                    typeof value === 'string'
                        ? value
                        : null
                );
            },

            validateSnapshot(payload) {
                const errors = {};

                if (
                    String(
                        payload.idEquipoAsignar
                        ?? ''
                    ) === ''
                ) {
                    errors.idEquipoAsignar = [
                        'Selecciona un equipo.'
                    ];
                }

                if (
                    String(
                        payload.nombreColaborador
                        ?? ''
                    ).trim() === ''
                ) {
                    errors.nombreColaborador = [
                        'Busca un usuario o escribe el nombre del colaborador.'
                    ];
                }

                if (
                    String(
                        payload.idUnidadAsignar
                        ?? ''
                    ) === ''
                ) {
                    errors.idUnidadAsignar = [
                        'Selecciona un hotel.'
                    ];
                }

                if (
                    String(
                        payload.idDepartamentoAsignar
                        ?? ''
                    ) === ''
                ) {
                    errors.idDepartamentoAsignar = [
                        'Selecciona un departamento.'
                    ];
                }

                if (
                    String(
                        payload.fechaAsignacion
                        ?? ''
                    ) === ''
                ) {
                    errors.fechaAsignacion = [
                        'Selecciona la fecha de asignación.'
                    ];
                }

                if (
                    String(
                        payload.idTipoAsignacion
                        ?? ''
                    ) === ''
                ) {
                    errors.idTipoAsignacion = [
                        'Selecciona el tipo de asignación.'
                    ];
                }

                this.validationErrors = errors;

                const keys =
                    Object.keys(errors);

                if (
                    keys.length === 0
                ) {
                    return true;
                }

                const first =
                    errors[keys[0]];

                this.showError(
                    Array.isArray(first)
                        ? first[0]
                        : 'Revisa los campos obligatorios.'
                );

                return false;
            },

            pulseSpinner() {
                this.savingVisual = true;

                if (this.spinnerTimer) {
                    clearTimeout(
                        this.spinnerTimer
                    );
                }

                this.spinnerTimer =
                    setTimeout(() => {
                        this.savingVisual = false;
                    }, 350);
            },

            async saveAssignment() {
                if (this.requestPending) {
                    return;
                }

                const payload =
                    this.captureSnapshot();

                if (
                    !this.validateSnapshot(
                        payload
                    )
                ) {
                    return;
                }

                this.errorVisible = false;
                this.requestPending = true;

                this.pulseSpinner();

                try {
                    const result =
                        await $wire.save(
                            payload
                        );

                    if (
                        !result
                        || result.ok !== true
                    ) {
                        throw new Error(
                            'Respuesta de guardado no válida.'
                        );
                    }

                    this.validationErrors = {};

                    this.showSuccess(
                        result.message
                        || 'El equipo fue asignado correctamente.'
                    );

                    setTimeout(() => {
                        const url =
                            this.$root.dataset.indexUrl;

                        if (
                            window.Livewire
                            && typeof window.Livewire.navigate
                                === 'function'
                        ) {
                            window.Livewire.navigate(
                                url
                            );

                            return;
                        }

                        window.location.href =
                            url;
                    }, 650);

                } catch (error) {
                    this.validationErrors =
                        error?.errors ?? {};

                    this.showError(
                        this.firstError(error)
                        || window.trackitMensajeError(error, 'guardar la asignación')
                    );

                } finally {
                    this.requestPending = false;
                }
            }
        }"

        data-index-url="{{ route('asignaciones.index') }}"
    >

        {{-- ============================================================
            TOAST ÉXITO
        ============================================================ --}}
        <div
            x-show="successVisible"
            x-cloak
            x-transition
            class="
                fixed
                top-20
                left-4
                right-4
                sm:left-auto
                sm:right-6

                z-[150]

                sm:w-96

                flex
                items-start
                gap-3

                px-4
                py-3

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-emerald-500/30

                shadow-lg
            "
        >
            <div
                class="
                    shrink-0

                    w-8
                    h-8

                    rounded-full

                    flex
                    items-center
                    justify-center

                    bg-emerald-500/10
                    text-emerald-500
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M5 12l4 4L19 7"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignación realizada
                </p>

                <p
                    class="
                        mt-0.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                    x-text="successMessage"
                ></p>
            </div>
        </div>


        {{-- ============================================================
            TOAST ERROR
        ============================================================ --}}
        <div
            x-show="errorVisible"
            x-cloak
            x-transition
            class="
                fixed
                top-20
                left-4
                right-4
                sm:left-auto
                sm:right-6

                z-[150]

                sm:w-96

                flex
                items-start
                gap-3

                px-4
                py-3

                rounded-xl

                bg-[var(--theme-surface)]

                border
                border-[var(--theme-danger)]

                shadow-lg
            "
        >
            <div
                class="
                    shrink-0

                    w-8
                    h-8

                    rounded-full

                    flex
                    items-center
                    justify-center

                    bg-red-500/10
                    text-[var(--theme-danger)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    No se pudo guardar
                </p>

                <p
                    class="
                        mt-0.5
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                    x-text="errorMessage"
                ></p>
            </div>
        </div>


        {{-- ============================================================
            FORMULARIO
        ============================================================ --}}
        <form
            data-avisar-cambios
            @submit.prevent="saveAssignment()"
            class="space-y-5"
        >

            {{-- ========================================================
                TIPO DE MOVIMIENTO
            ======================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    p-4
                    sm:p-5
                "
            >
                <div
                    class="
                        flex
                        flex-col

                        lg:flex-row
                        lg:items-center

                        gap-3
                        lg:gap-6
                    "
                >
                    <p
                        class="
                            shrink-0

                            text-sm
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Tipo de movimiento
                    </p>

                    <div
                        class="
                            grid
                            grid-cols-2

                            w-full
                            max-w-2xl
                        "
                    >

                        {{-- ASIGNACIÓN --}}
                        <button
                            type="button"
                            class="
                                h-10

                                flex
                                items-center
                                justify-center
                                gap-2

                                rounded-l-lg

                                border
                                border-[var(--theme-primary-border)]

                                bg-[var(--theme-primary-soft)]

                                text-sm
                                font-medium
                                text-[var(--theme-primary)]
                            "
                        >
                            <svg
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                                <path d="M18 7v6"/>
                                <path d="M15 10h6"/>
                            </svg>

                            Asignación
                        </button>


                        {{-- REASIGNACIÓN --}}
                        <a
                            href="{{ route('asignaciones.reassign') }}"
                            wire:navigate.hover
                            class="
                                h-10

                                flex
                                items-center
                                justify-center
                                gap-2

                                rounded-r-lg

                                border
                                border-l-0
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                text-sm
                                font-medium
                                text-[var(--theme-text-muted)]

                                hover:bg-[var(--theme-surface-soft)]
                                hover:text-[var(--theme-text)]
                                hover:border-[var(--theme-primary-border)]

                                transition-colors
                            "
                        >
                            <svg
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <path d="M4 7h15"/>
                                <path d="M16 4l3 3-3 3"/>
                                <path d="M20 17H5"/>
                                <path d="M8 14l-3 3 3 3"/>
                            </svg>

                            Reasignación
                        </a>

                    </div>
                </div>
            </section>


            {{-- ========================================================
                DATOS DE ASIGNACIÓN
            ======================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]

                    border
                    border-[var(--theme-border)]

                    rounded-xl

                    p-4
                    sm:p-6
                "
            >
                <div
                    class="
                        grid
                        grid-cols-1
                        lg:grid-cols-2

                        gap-x-5
                        gap-y-4
                    "
                >

                    {{-- =================================================
                        EQUIPO
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idEquipoAsignar"
                            x-options="data.equipos"

                            label="Nombre de Equipo *"
                            placeholder="Buscar equipo..."

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idEquipoAsignar')"
                            x-cloak
                            x-text="fieldError('idEquipoAsignar')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        COLABORADOR
                    ================================================= --}}
                    <div class="min-w-0">

                        <label
                            for="assignment-collaborator"
                            class="
                                block
                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Colaborador *
                        </label>

                        <div id="assignment-collaborator">
                            <x-searchable-select
                                x-model="colaborador"
                                x-options="data.usuarios ?? []"
                                :options="$assignmentData['usuarios'] ?? []"
                                :show-clear="false"
                                allow-custom
                                placeholder="Busca un usuario o escribe un nombre"
                            />
                        </div>

                        {{-- Sin cuenta: se guarda igual; un administrador la crea después --}}
                        <p
                            x-show="colaboradorSinCuenta()"
                            x-cloak
                            class="mt-1.5 flex items-start gap-1.5 text-xs text-[var(--theme-warning)]"
                        >
                            <svg class="mt-px h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 9v4M12 16.5v.5"/>
                                <path d="M10.3 3.9L2.6 17.3A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.7L13.7 3.9a2 2 0 0 0-3.4 0z"/>
                            </svg>
                            <span>
                                Este colaborador no tiene cuenta. La asignación se guardará y un
                                administrador deberá crear su cuenta en Seguridad y roles.
                            </span>
                        </p>

                        <p
                            x-show="fieldError('nombreColaborador')"
                            x-cloak
                            x-text="fieldError('nombreColaborador')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        NÚMERO DE COLABORADOR
                    ================================================= --}}
                    <div class="min-w-0">

                        <label
                            for="assignment-collaborator-number"
                            class="
                                block
                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Número de colaborador
                        </label>

                        <input
                            id="assignment-collaborator-number"
                            type="text"

                            x-model="numeroColaborador"
                            x-bind:readonly="!! (usuarioSeleccionado()?.numero)"
                            x-bind:title="usuarioSeleccionado()?.numero ? 'Número tomado de la cuenta del usuario' : ''"

                            placeholder="Ej. 001245"

                            class="
                                w-full

                                px-3
                                py-2.5

                                rounded-md

                                border
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                outline-none

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                transition-colors
                            "
                        >

                    </div>


                    {{-- =================================================
                        HOST
                    ================================================= --}}
                    <div class="min-w-0">

                        <label
                            class="
                                block
                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Host
                        </label>

                        <div class="relative">

                            <input
                                type="text"

                                :value="hostEquipo"

                                readonly

                                placeholder="Se obtiene del equipo seleccionado"

                                class="
                                    w-full

                                    px-3
                                    py-2.5
                                    pr-9

                                    rounded-md

                                    border
                                    border-[var(--theme-border)]

                                    bg-[var(--theme-surface-soft)]

                                    text-sm
                                    text-[var(--theme-text-muted)]

                                    placeholder:text-[var(--theme-text-muted)]

                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute
                                    right-3
                                    top-1/2
                                    -translate-y-1/2

                                    w-4
                                    h-4

                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect
                                    x="6"
                                    y="10"
                                    width="12"
                                    height="9"
                                    rx="1.5"
                                />

                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>

                        </div>
                    </div>


                    {{-- =================================================
                        HOTEL
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idUnidadAsignar"
                            x-options="data.unidades"

                            :label="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] . ' *'"
                            :placeholder="app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['buscar_unidad']"

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idUnidadAsignar')"
                            x-cloak
                            x-text="fieldError('idUnidadAsignar')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        DEPARTAMENTO
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idDepartamentoAsignar"
                            x-options="departamentosDisponibles"

                            label="Departamento *"

                            placeholder="Selecciona un hotel primero"

                            x-placeholder="
                                idUnidadAsignar === ''
                                    ? 'Selecciona un hotel primero'
                                    : 'Buscar departamento...'
                            "

                            x-disabled="idUnidadAsignar === ''"

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idDepartamentoAsignar')"
                            x-cloak
                            x-text="fieldError('idDepartamentoAsignar')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        UBICACIÓN
                        Depende del hotel y del departamento.
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idUbicacionAsignar"
                            x-options="ubicacionesDisponibles"

                            label="Ubicación"

                            placeholder="Selecciona un departamento primero"

                            x-placeholder="
                                idDepartamentoAsignar === ''
                                    ? 'Selecciona un departamento primero'
                                    : (ubicacionesDisponibles.length
                                        ? 'Buscar ubicación...'
                                        : 'Sin ubicaciones para este departamento')
                            "

                            x-disabled="idDepartamentoAsignar === '' || ! ubicacionesDisponibles.length"

                            clear-label="Sin ubicación específica"
                        />

                        <p
                            x-show="fieldError('idUbicacionAsignar')"
                            x-cloak
                            x-text="fieldError('idUbicacionAsignar')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        FECHA
                    ================================================= --}}
                    <div class="min-w-0">

                        <label
                            for="assignment-date"
                            class="
                                block
                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Fecha de Asignación *
                        </label>

                        <input
                            id="assignment-date"
                            type="date"

                            x-model="fechaAsignacion"

                            @change="
                                clearValidationError(
                                    'fechaAsignacion'
                                )
                            "

                            class="
                                w-full

                                px-3
                                py-2.5

                                rounded-md

                                border
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                text-sm
                                text-[var(--theme-text)]

                                outline-none

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                transition-colors
                            "
                        >

                        <p
                            x-show="fieldError('fechaAsignacion')"
                            x-cloak
                            x-text="fieldError('fechaAsignacion')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>

                    </div>


                    {{-- =================================================
                        TIPO DE ASIGNACIÓN
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idTipoAsignacion"
                            x-options="data.tiposAsignacion"

                            label="Tipo de asignación *"
                            placeholder="Buscar tipo de asignación..."

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idTipoAsignacion')"
                            x-cloak
                            x-text="fieldError('idTipoAsignacion')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>

                    </div>


                    {{-- =================================================
                        OBSERVACIONES
                    ================================================= --}}
                    <div class="lg:col-span-2 min-w-0">

                        <label
                            for="assignment-observations"
                            class="
                                block
                                mb-1.5

                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Observaciones

                            <span class="text-[var(--theme-text-muted)]">
                                (Opcional)
                            </span>
                        </label>

                        <textarea
                            id="assignment-observations"

                            x-model="observacionesAsignacion"

                            rows="5"

                            placeholder="Escribe alguna observación relacionada con la asignación..."

                            class="
                                w-full

                                px-3
                                py-2.5

                                rounded-lg

                                border
                                border-[var(--theme-border-strong)]

                                bg-[var(--theme-surface)]

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                resize-y

                                outline-none

                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                transition-colors
                            "
                        ></textarea>

                    </div>

                </div>
            </section>


            {{-- ========================================================
                INFORMACIÓN ADICIONAL
            ======================================================== --}}
            <section
                class="
                    flex
                    items-start
                    gap-3

                    px-4
                    py-3.5

                    rounded-xl

                    border
                    border-[var(--theme-primary-border)]

                    bg-[var(--theme-primary-soft-subtle)]
                "
            >
                <div
                    class="
                        shrink-0

                        w-8
                        h-8

                        rounded-full

                        border
                        border-[var(--theme-primary-border)]

                        flex
                        items-center
                        justify-center

                        text-[var(--theme-primary)]
                    "
                >
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 10v6"/>
                        <path d="M12 7h.01"/>
                    </svg>
                </div>

                <div class="min-w-0">

                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-primary)]
                        "
                    >
                        Información adicional
                    </p>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-primary)]
                        "
                    >
                        Al guardar la información, el equipo quedará asignado al colaborador y a la ubicación organizacional seleccionada.
                    </p>

                </div>
            </section>


            {{-- ========================================================
                ACCIONES
            ======================================================== --}}
            <div
                class="
                    flex
                    flex-col-reverse

                    sm:flex-row
                    sm:items-center
                    sm:justify-end

                    gap-3

                    pb-2
                "
            >

                <a
                    href="{{ route('asignaciones.index') }}"
                    wire:navigate.hover
                    class="
                        w-full
                        sm:w-auto

                        h-11

                        px-5

                        flex
                        items-center
                        justify-center

                        rounded-lg

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-sm
                        font-medium
                        text-[var(--theme-text-muted)]

                        hover:bg-[var(--theme-surface-soft)]
                        hover:text-[var(--theme-text)]
                        hover:border-[var(--theme-primary-border)]

                        transition-colors
                    "
                >
                    Cancelar
                </a>


                <x-ui.button
                    type="submit"
                    x-loading="savingVisual"
                    x-disabled="requestPending"
                    size="lg"
                    loading-text="Guardando…"
                    class="w-full sm:w-auto"
                >
                    Guardar asignación
                </x-ui.button>

            </div>

        </form>

    </div>

</div>
