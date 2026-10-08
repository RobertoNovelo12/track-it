<div wire:key="assignment-create-ui">

    <div
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

            nombreColaborador: '',
            numeroColaborador: '',

            idSedeAsignar: '',
            idAreaAsignar: '',
            idDepartamentoAsignar: '',
            idUbicacionAsignar: '',

            fechaAsignacion: @js(
                $assignmentData['defaults']['fechaAsignacion'] ?? ''
            ),

            idTipoAsignacion: @js(
                $assignmentData['defaults']['idTipoAsignacion'] ?? ''
            ),

            observacionesAsignacion: '',

            init() {
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
                    'idSedeAsignar',
                    (value, previous) => {
                        if (
                            String(value ?? '') ===
                            String(previous ?? '')
                        ) {
                            return;
                        }

                        this.idAreaAsignar = '';
                        this.idDepartamentoAsignar = '';
                        this.idUbicacionAsignar = '';

                        this.clearValidationError(
                            'idSedeAsignar'
                        );

                        this.clearValidationError(
                            'idAreaAsignar'
                        );

                        this.clearValidationError(
                            'idDepartamentoAsignar'
                        );
                    }
                );

                this.$watch(
                    'idAreaAsignar',
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
                            'idAreaAsignar'
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

            get areasDisponibles() {
                const sedeId =
                    String(
                        this.idSedeAsignar ?? ''
                    );

                if (sedeId === '') {
                    return [];
                }

                return (
                    this.data?.areas ?? []
                ).filter(
                    area =>
                        String(
                            area?.idSede ?? ''
                        ) === sedeId
                );
            },

            get departamentosDisponibles() {
                const areaId =
                    String(
                        this.idAreaAsignar ?? ''
                    );

                if (areaId === '') {
                    return [];
                }

                return (
                    this.data?.departamentos ?? []
                ).filter(
                    departamento =>
                        String(
                            departamento?.idArea ?? ''
                        ) === areaId
                );
            },

            get ubicacionesDisponibles() {
                const sedeId =
                    String(
                        this.idSedeAsignar ?? ''
                    );

                const areaId =
                    String(
                        this.idAreaAsignar ?? ''
                    );

                if (sedeId === '') {
                    return [];
                }

                return (
                    this.data?.ubicaciones ?? []
                ).filter(
                    ubicacion => {
                        const mismaSede =
                            String(
                                ubicacion?.idSede ?? ''
                            ) === sedeId;

                        if (!mismaSede) {
                            return false;
                        }

                        if (areaId === '') {
                            return true;
                        }

                        const ubicacionArea =
                            String(
                                ubicacion?.idArea ?? ''
                            );

                        return (
                            ubicacionArea === ''
                            || ubicacionArea === areaId
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

                this.successVisible = true;

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

                this.errorVisible = true;

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

                    nombreColaborador:
                        String(
                            this.nombreColaborador ?? ''
                        ),

                    numeroColaborador:
                        String(
                            this.numeroColaborador ?? ''
                        ),

                    idSedeAsignar:
                        String(
                            this.idSedeAsignar ?? ''
                        ),

                    idAreaAsignar:
                        String(
                            this.idAreaAsignar ?? ''
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
                        'Escribe el nombre completo del colaborador.'
                    ];
                }

                if (
                    String(
                        payload.idSedeAsignar
                        ?? ''
                    ) === ''
                ) {
                    errors.idSedeAsignar = [
                        'Selecciona una sede.'
                    ];
                }

                if (
                    String(
                        payload.idAreaAsignar
                        ?? ''
                    ) === ''
                ) {
                    errors.idAreaAsignar = [
                        'Selecciona un área.'
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
                        || 'No fue posible guardar la asignación. Inténtalo nuevamente.'
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
            ENCABEZADO
        ============================================================ --}}
        <section class="mb-6">

            <div
                class="
                    flex
                    items-center
                    flex-wrap
                    gap-2

                    mb-2

                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate.hover
                    class="
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    Vista General
                </a>

                <svg
                    class="w-3 h-3"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M9 6l6 6-6 6"/>
                </svg>

                <a
                    href="{{ route('asignaciones.index') }}"
                    wire:navigate.hover
                    class="
                        hover:text-[var(--theme-primary)]
                        transition-colors
                    "
                >
                    Asignación y Movimientos
                </a>

                <svg
                    class="w-3 h-3"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M9 6l6 6-6 6"/>
                </svg>

                <span>
                    Asignar equipo
                </span>
            </div>

            <h1
                class="
                    text-xl
                    font-semibold
                    leading-tight
                    text-[var(--theme-text-strong)]
                "
            >
                Asignación de Equipo
            </h1>

        </section>


        {{-- ============================================================
            FORMULARIO
        ============================================================ --}}
        <form
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
                            Nombre Completo de Colaborador *
                        </label>

                        <input
                            id="assignment-collaborator"
                            type="text"

                            x-model="nombreColaborador"

                            @input="
                                clearValidationError(
                                    'nombreColaborador'
                                )
                            "

                            placeholder="Nombre completo del colaborador"

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
                        SEDE
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idSedeAsignar"
                            x-options="data.sedes"

                            label="Sede *"
                            placeholder="Buscar sede..."

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idSedeAsignar')"
                            x-cloak
                            x-text="fieldError('idSedeAsignar')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- =================================================
                        ÁREA
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idAreaAsignar"
                            x-options="areasDisponibles"

                            label="Área *"

                            placeholder="Selecciona una sede primero"

                            x-placeholder="
                                idSedeAsignar === ''
                                    ? 'Selecciona una sede primero'
                                    : 'Buscar área...'
                            "

                            x-disabled="idSedeAsignar === ''"

                            :show-clear="false"
                        />

                        <p
                            x-show="fieldError('idAreaAsignar')"
                            x-cloak
                            x-text="fieldError('idAreaAsignar')"
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

                            placeholder="Selecciona un área primero"

                            x-placeholder="
                                idAreaAsignar === ''
                                    ? 'Selecciona un área primero'
                                    : 'Buscar departamento...'
                            "

                            x-disabled="idAreaAsignar === ''"

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
                    ================================================= --}}
                    <div class="min-w-0">

                        <x-searchable-select
                            x-model="idUbicacionAsignar"
                            x-options="ubicacionesDisponibles"

                            label="Ubicación"

                            placeholder="Selecciona una sede primero"

                            x-placeholder="
                                idSedeAsignar === ''
                                    ? 'Selecciona una sede primero'
                                    : 'Buscar ubicación...'
                            "

                            x-disabled="idSedeAsignar === ''"

                            clear-label="Sin ubicación específica"
                        />

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


                <button
                    type="submit"

                    :disabled="requestPending"
                    :aria-busy="requestPending ? 'true' : 'false'"

                    class="
                        w-full
                        sm:w-auto

                        h-11

                        px-5

                        flex
                        items-center
                        justify-center
                        gap-2

                        rounded-lg

                        bg-[var(--theme-primary)]
                        hover:bg-[var(--theme-primary-hover)]

                        text-sm
                        font-medium
                        text-white

                        disabled:opacity-60
                        disabled:cursor-not-allowed

                        transition-colors
                    "
                >

                    {{-- ICONO NORMAL --}}
                    <svg
                        x-show="!savingVisual"
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path d="M5 4h12l2 2v14H5z"/>
                        <path d="M8 4v6h8V4"/>
                        <path d="M8 20v-6h8v6"/>
                    </svg>


                    {{-- SPINNER --}}
                    <svg
                        x-show="savingVisual"
                        x-cloak

                        class="w-4 h-4 animate-spin"

                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"

                            stroke="currentColor"
                            stroke-width="2"

                            opacity="0.25"
                        />

                        <path
                            d="M21 12a9 9 0 0 0-9-9"

                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span
                        x-text="
                            savingVisual
                                ? 'Guardando...'
                                : 'Guardar Asignación'
                        "
                    ></span>

                </button>

            </div>

        </form>

    </div>

</div>