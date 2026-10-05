<div wire:key="reassignment-create-ui">

    <div
        x-data="{
            data: @js($reassignmentData),

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

            idEquipoReasignar: '',
            idAsignacionActual: '',

            responsableActual: '',
            numeroColaboradorActual: '',
            hostActual: '',
            areaDepartamentoActual: '',
            sedeActual: '',
            fechaAsignacionActual: '',
            tipoAsignacionActual: '',

            idAreaActual: '',
            idDepartamentoActual: '',
            idUbicacionActual: '',
            idTipoAsignacionActual: '',

            motivoReasignacion: '',
            nombreNuevoColaborador: '',

            idSedeNueva: '',
            idAreaNueva: '',
            idDepartamentoNuevo: '',
            idUbicacionNueva: '',

            fechaReasignacion: @js(
                $reassignmentData['defaults']['fechaReasignacion'] ?? ''
            ),

            observacionesReasignacion: '',

            init() {
                this.$watch('idEquipoReasignar', () => {
                    this.loadSelectedAssignment();
                    this.clearValidationError('idEquipoReasignar');
                });

                this.$watch('idSedeNueva', (value, previous) => {
                    if (String(value ?? '') === String(previous ?? '')) {
                        return;
                    }

                    this.idAreaNueva = '';
                    this.idDepartamentoNuevo = '';
                    this.idUbicacionNueva = '';

                    this.clearValidationError('idSedeNueva');
                    this.clearValidationError('idAreaNueva');
                    this.clearValidationError('idDepartamentoNuevo');
                });

                this.$watch('idAreaNueva', (value, previous) => {
                    if (String(value ?? '') === String(previous ?? '')) {
                        return;
                    }

                    this.idDepartamentoNuevo = '';
                    this.idUbicacionNueva = '';

                    this.clearValidationError('idAreaNueva');
                    this.clearValidationError('idDepartamentoNuevo');
                });

                this.$watch('idDepartamentoNuevo', (value, previous) => {
                    if (String(value ?? '') === String(previous ?? '')) {
                        return;
                    }

                    this.idUbicacionNueva = '';

                    this.clearValidationError('idDepartamentoNuevo');
                });
            },

            get areasDisponibles() {
                const sedeId = String(this.idSedeNueva ?? '');

                if (sedeId === '') {
                    return [];
                }

                return (this.data?.areas ?? []).filter(
                    area =>
                        String(area?.idSede ?? '') === sedeId
                );
            },

            get departamentosDisponibles() {
                const areaId = String(this.idAreaNueva ?? '');

                if (areaId === '') {
                    return [];
                }

                return (this.data?.departamentos ?? []).filter(
                    departamento =>
                        String(departamento?.idArea ?? '') === areaId
                );
            },

            get ubicacionesDisponibles() {
                const sedeId = String(this.idSedeNueva ?? '');
                const areaId = String(this.idAreaNueva ?? '');

                if (sedeId === '') {
                    return [];
                }

                return (this.data?.ubicaciones ?? []).filter(
                    ubicacion => {
                        const mismaSede =
                            String(ubicacion?.idSede ?? '') === sedeId;

                        if (!mismaSede) {
                            return false;
                        }

                        if (areaId === '') {
                            return true;
                        }

                        const ubicacionArea =
                            String(ubicacion?.idArea ?? '');

                        return (
                            ubicacionArea === ''
                            || ubicacionArea === areaId
                        );
                    }
                );
            },

            resetCurrentAssignment() {
                this.idAsignacionActual = '';

                this.responsableActual = '';
                this.numeroColaboradorActual = '';
                this.hostActual = '';
                this.areaDepartamentoActual = '';
                this.sedeActual = '';
                this.fechaAsignacionActual = '';
                this.tipoAsignacionActual = '';

                this.idAreaActual = '';
                this.idDepartamentoActual = '';
                this.idUbicacionActual = '';
                this.idTipoAsignacionActual = '';
            },

            resetNewDestination() {
                this.idSedeNueva = '';
                this.idAreaNueva = '';
                this.idDepartamentoNuevo = '';
                this.idUbicacionNueva = '';
            },

            loadSelectedAssignment() {
                this.resetCurrentAssignment();
                this.resetNewDestination();

                const equipoId =
                    String(this.idEquipoReasignar ?? '');

                if (equipoId === '') {
                    return;
                }

                const equipo =
                    (this.data?.equipos ?? []).find(
                        item =>
                            String(item?.value ?? '') === equipoId
                    );

                if (!equipo) {
                    this.idEquipoReasignar = '';
                    return;
                }

                const assignment =
                    equipo.assignment ?? {};

                this.idAsignacionActual =
                    String(assignment.idAsignacion ?? '');

                this.responsableActual =
                    String(assignment.responsable ?? '');

                this.numeroColaboradorActual =
                    String(assignment.numeroColaborador ?? '');

                this.hostActual =
                    String(assignment.host ?? '');

                this.areaDepartamentoActual =
                    String(assignment.areaDepartamento ?? '');

                this.sedeActual =
                    String(assignment.sede ?? '');

                this.fechaAsignacionActual =
                    String(assignment.fechaAsignacion ?? '');

                this.tipoAsignacionActual =
                    String(assignment.tipoAsignacion ?? '');

                this.idAreaActual =
                    String(assignment.idArea ?? '');

                this.idDepartamentoActual =
                    String(assignment.idDepartamento ?? '');

                this.idUbicacionActual =
                    String(assignment.idUbicacion ?? '');

                this.idTipoAsignacionActual =
                    String(assignment.idTipoAsignacion ?? '');
            },

            clearValidationError(field) {
                if (
                    !this.validationErrors
                    || typeof this.validationErrors !== 'object'
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
                    || 'El equipo fue reasignado correctamente.';

                this.successVisible = true;

                if (this.successTimer) {
                    clearTimeout(this.successTimer);
                }

                this.successTimer = setTimeout(() => {
                    this.successVisible = false;
                }, 3000);
            },

            showError(message) {
                this.errorMessage =
                    message
                    || 'No fue posible guardar la reasignación.';

                this.errorVisible = true;

                if (this.errorTimer) {
                    clearTimeout(this.errorTimer);
                }

                this.errorTimer = setTimeout(() => {
                    this.errorVisible = false;
                }, 5000);
            },

            captureSnapshot() {
                return {
                    idEquipoReasignar:
                        String(this.idEquipoReasignar ?? ''),

                    idAsignacionActual:
                        String(this.idAsignacionActual ?? ''),

                    motivoReasignacion:
                        String(this.motivoReasignacion ?? ''),

                    nombreNuevoColaborador:
                        String(this.nombreNuevoColaborador ?? ''),

                    idSedeNueva:
                        String(this.idSedeNueva ?? ''),

                    idAreaNueva:
                        String(this.idAreaNueva ?? ''),

                    idDepartamentoNuevo:
                        String(this.idDepartamentoNuevo ?? ''),

                    idUbicacionNueva:
                        String(this.idUbicacionNueva ?? ''),

                    fechaReasignacion:
                        String(this.fechaReasignacion ?? ''),

                    observacionesReasignacion:
                        String(this.observacionesReasignacion ?? ''),
                };
            },

            fieldError(field) {
                const error =
                    this.validationErrors?.[field];

                if (!error) {
                    return null;
                }

                if (Array.isArray(error)) {
                    return error[0] ?? null;
                }

                return typeof error === 'string'
                    ? error
                    : null;
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

                if (Array.isArray(value)) {
                    return value[0] ?? null;
                }

                return typeof value === 'string'
                    ? value
                    : null;
            },

            validateSnapshot(payload) {
                const errors = {};

                if (
                    String(
                        payload.idEquipoReasignar ?? ''
                    ) === ''
                ) {
                    errors.idEquipoReasignar = [
                        'Selecciona el equipo que se reasignará.'
                    ];
                }

                if (
                    String(
                        payload.idAsignacionActual ?? ''
                    ) === ''
                ) {
                    errors.idEquipoReasignar = [
                        'No se encontró la asignación actual del equipo.'
                    ];
                }

                if (
                    String(
                        payload.motivoReasignacion ?? ''
                    ).trim() === ''
                ) {
                    errors.motivoReasignacion = [
                        'Escribe el motivo de la reasignación.'
                    ];
                }

                if (
                    String(
                        payload.nombreNuevoColaborador ?? ''
                    ).trim() === ''
                ) {
                    errors.nombreNuevoColaborador = [
                        'Escribe el nombre del nuevo colaborador.'
                    ];
                }

                if (
                    String(
                        payload.idSedeNueva ?? ''
                    ) === ''
                ) {
                    errors.idSedeNueva = [
                        'Selecciona la nueva sede.'
                    ];
                }

                if (
                    String(
                        payload.idAreaNueva ?? ''
                    ) === ''
                ) {
                    errors.idAreaNueva = [
                        'Selecciona la nueva área.'
                    ];
                }

                if (
                    String(
                        payload.idDepartamentoNuevo ?? ''
                    ) === ''
                ) {
                    errors.idDepartamentoNuevo = [
                        'Selecciona el nuevo departamento.'
                    ];
                }

                if (
                    String(
                        payload.fechaReasignacion ?? ''
                    ) === ''
                ) {
                    errors.fechaReasignacion = [
                        'Selecciona la fecha de reasignación.'
                    ];
                }

                this.validationErrors = errors;

                const keys =
                    Object.keys(errors);

                if (keys.length === 0) {
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
                    clearTimeout(this.spinnerTimer);
                }

                this.spinnerTimer = setTimeout(() => {
                    this.savingVisual = false;
                }, 350);
            },

            async saveReassignment() {
                if (this.requestPending) {
                    return;
                }

                const payload =
                    this.captureSnapshot();

                if (!this.validateSnapshot(payload)) {
                    return;
                }

                this.errorVisible = false;
                this.requestPending = true;

                this.pulseSpinner();

                try {
                    const result =
                        await $wire.save(payload);

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
                        || 'El equipo fue reasignado correctamente.'
                    );

                    setTimeout(() => {
                        const url =
                            this.$root.dataset.indexUrl;

                        if (
                            window.Livewire
                            && typeof window.Livewire.navigate
                                === 'function'
                        ) {
                            window.Livewire.navigate(url);
                            return;
                        }

                        window.location.href = url;
                    }, 650);

                } catch (error) {
                    this.validationErrors =
                        error?.errors ?? {};

                    this.showError(
                        this.firstError(error)
                        || 'No fue posible guardar la reasignación. Inténtalo nuevamente.'
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
                fixed top-20 left-4 right-4 sm:left-auto sm:right-6 z-[150]
                sm:w-96
                flex items-start gap-3
                px-4 py-3
                rounded-xl
                bg-[var(--theme-surface)]
                border border-emerald-500/30
                shadow-lg
            "
        >
            <div
                class="
                    shrink-0 w-8 h-8
                    rounded-full
                    flex items-center justify-center
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
                        text-sm font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Reasignación realizada
                </p>

                <p
                    class="
                        mt-0.5 text-xs
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
                fixed top-20 left-4 right-4 sm:left-auto sm:right-6 z-[150]
                sm:w-96
                flex items-start gap-3
                px-4 py-3
                rounded-xl
                bg-[var(--theme-surface)]
                border border-[var(--theme-danger)]
                shadow-lg
            "
        >
            <div
                class="
                    shrink-0 w-8 h-8
                    rounded-full
                    flex items-center justify-center
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
                        text-sm font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    No se pudo guardar
                </p>

                <p
                    class="
                        mt-0.5 text-xs
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
                    flex items-center flex-wrap gap-2
                    mb-2
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
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
                    wire:navigate
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
                    Reasignar equipo
                </span>
            </div>

            <h1
                class="
                    text-xl font-semibold leading-tight
                    text-[var(--theme-text-strong)]
                "
            >
                Reasignación de Equipo
            </h1>
        </section>


        <form
            @submit.prevent="saveReassignment()"
            class="space-y-5"
        >

            {{-- ========================================================
                TIPO DE MOVIMIENTO
            ======================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border border-[var(--theme-border)]
                    rounded-xl
                    p-4 sm:p-5
                "
            >
                <div
                    class="
                        flex flex-col lg:flex-row
                        lg:items-center
                        gap-3 lg:gap-6
                    "
                >
                    <p
                        class="
                            shrink-0
                            text-sm font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Tipo de movimiento
                    </p>

                    <div
                        class="
                            grid grid-cols-2
                            w-full max-w-2xl
                        "
                    >

                        {{-- ASIGNACIÓN --}}
                        <a
                            href="{{ route('asignaciones.create') }}"
                            wire:navigate
                            class="
                                h-10
                                flex items-center justify-center gap-2
                                rounded-l-lg
                                border border-[var(--theme-border-strong)]
                                bg-[var(--theme-surface)]
                                text-sm font-medium
                                text-[var(--theme-text-muted)]
                                hover:bg-[var(--theme-surface-soft)]
                                hover:text-[var(--theme-text)]
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
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                                <path d="M18 7v6"/>
                                <path d="M15 10h6"/>
                            </svg>

                            Asignación
                        </a>


                        {{-- REASIGNACIÓN --}}
                        <button
                            type="button"
                            class="
                                h-10
                                flex items-center justify-center gap-2
                                rounded-r-lg
                                border border-l-0
                                border-[var(--theme-primary-border)]
                                bg-[var(--theme-primary-soft)]
                                text-sm font-medium
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
                                <path d="M4 7h15"/>
                                <path d="M16 4l3 3-3 3"/>
                                <path d="M20 17H5"/>
                                <path d="M8 14l-3 3 3 3"/>
                            </svg>

                            Reasignación
                        </button>

                    </div>
                </div>
            </section>


            {{-- ========================================================
                ASIGNACIÓN ACTUAL
            ======================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border border-[var(--theme-border)]
                    rounded-xl
                    p-4 sm:p-6
                "
            >
                <h2
                    class="
                        mb-5
                        text-base font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Asignación actual
                </h2>

                {{-- EQUIPO --}}
                <div class="mb-4">
                    <x-searchable-select
                        x-model="idEquipoReasignar"
                        x-options="data.equipos"
                        label="Equipo a reasignar *"
                        placeholder="Buscar equipo actualmente asignado..."
                    />

                    <p
                        x-show="fieldError('idEquipoReasignar')"
                        x-cloak
                        x-text="fieldError('idEquipoReasignar')"
                        class="mt-1 text-xs text-[var(--theme-danger)]"
                    ></p>
                </div>


                <div
                    class="
                        grid grid-cols-1
                        md:grid-cols-2
                        xl:grid-cols-3
                        gap-4
                    "
                >

                    {{-- RESPONSABLE --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Responsable actual
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="responsableActual"
                                placeholder="Selecciona un equipo"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>


                    {{-- ÁREA / DEPARTAMENTO --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Área / departamento
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="areaDepartamentoActual"
                                placeholder="Selecciona un equipo"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>


                    {{-- SEDE --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Sede actual
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="sedeActual"
                                placeholder="Selecciona un equipo"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>


                    {{-- HOST --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Host actual
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="hostActual"
                                placeholder="Sin host"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>


                    {{-- FECHA ACTUAL --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Fecha de Asignación
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="fechaAsignacionActual"
                                placeholder="Selecciona un equipo"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>


                    {{-- TIPO ACTUAL --}}
                    <div>
                        <label
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Tipo de Asignación
                        </label>

                        <div class="relative">
                            <input
                                type="text"
                                readonly
                                :value="tipoAsignacionActual"
                                placeholder="Selecciona un equipo"
                                class="
                                    w-full
                                    px-3 py-2.5 pr-9
                                    rounded-md
                                    border border-[var(--theme-border)]
                                    bg-[var(--theme-surface-soft)]
                                    text-sm
                                    text-[var(--theme-text-muted)]
                                    placeholder:text-[var(--theme-text-muted)]
                                    cursor-default
                                "
                            >

                            <svg
                                class="
                                    absolute right-3 top-1/2
                                    -translate-y-1/2
                                    w-4 h-4
                                    text-[var(--theme-text-muted)]
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <rect x="6" y="10" width="12" height="9" rx="1.5"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </div>
                    </div>

                </div>
            </section>


            {{-- ========================================================
                NUEVA REASIGNACIÓN
            ======================================================== --}}
            <section
                class="
                    bg-[var(--theme-surface)]
                    border border-[var(--theme-border)]
                    rounded-xl
                    p-4 sm:p-6
                "
            >
                <h2
                    class="
                        mb-5
                        text-base font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Nueva reasignación
                </h2>


                <div
                    class="
                        grid grid-cols-1
                        lg:grid-cols-2
                        gap-x-5 gap-y-4
                    "
                >

                    {{-- MOTIVO --}}
                    <div>
                        <label
                            for="reassignment-reason"
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Motivo de reasignación *
                        </label>

                        <input
                            id="reassignment-reason"
                            type="text"
                            x-model="motivoReasignacion"
                            @input="clearValidationError('motivoReasignacion')"
                            placeholder="Escribe el motivo de la reasignación"
                            class="
                                w-full
                                px-3 py-2.5
                                rounded-md
                                border border-[var(--theme-border-strong)]
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
                            x-show="fieldError('motivoReasignacion')"
                            x-cloak
                            x-text="fieldError('motivoReasignacion')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- NUEVO COLABORADOR --}}
                    <div>
                        <label
                            for="reassignment-collaborator"
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Nombre del Nuevo Colaborador *
                        </label>

                        <input
                            id="reassignment-collaborator"
                            type="text"
                            x-model="nombreNuevoColaborador"
                            @input="clearValidationError('nombreNuevoColaborador')"
                            placeholder="Nombre completo del nuevo colaborador"
                            class="
                                w-full
                                px-3 py-2.5
                                rounded-md
                                border border-[var(--theme-border-strong)]
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
                            x-show="fieldError('nombreNuevoColaborador')"
                            x-cloak
                            x-text="fieldError('nombreNuevoColaborador')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- NUEVA SEDE --}}
                    <div>
                        <x-searchable-select
                            x-model="idSedeNueva"
                            x-options="data.sedes"

                            label="Nueva Sede *"

                            placeholder="Selecciona un equipo primero"
                            :disabled="true"

                            x-placeholder="idAsignacionActual === '' ? 'Selecciona un equipo primero' : 'Buscar sede...'"
                            x-disabled="idAsignacionActual === ''"
                        />

                        <p
                            x-show="fieldError('idSedeNueva')"
                            x-cloak
                            x-text="fieldError('idSedeNueva')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- NUEVA ÁREA --}}
                    <div>
                        <x-searchable-select
                            x-model="idAreaNueva"
                            x-options="areasDisponibles"

                            label="Nueva Área *"

                            placeholder="Selecciona una sede primero"
                            :disabled="true"

                            x-placeholder="idSedeNueva === '' ? 'Selecciona una sede primero' : 'Buscar área...'"
                            x-disabled="idSedeNueva === ''"
                        />

                        <p
                            x-show="fieldError('idAreaNueva')"
                            x-cloak
                            x-text="fieldError('idAreaNueva')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- NUEVO DEPARTAMENTO --}}
                    <div>
                        <x-searchable-select
                            x-model="idDepartamentoNuevo"
                            x-options="departamentosDisponibles"

                            label="Nuevo Departamento *"

                            placeholder="Selecciona un área primero"
                            :disabled="true"

                            x-placeholder="idAreaNueva === '' ? 'Selecciona un área primero' : 'Buscar departamento...'"
                            x-disabled="idAreaNueva === ''"
                        />

                        <p
                            x-show="fieldError('idDepartamentoNuevo')"
                            x-cloak
                            x-text="fieldError('idDepartamentoNuevo')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- NUEVA UBICACIÓN --}}
                    <div>
                        <x-searchable-select
                            x-model="idUbicacionNueva"
                            x-options="ubicacionesDisponibles"

                            label="Nueva Ubicación"

                            placeholder="Selecciona una sede primero"
                            :disabled="true"

                            x-placeholder="idSedeNueva === '' ? 'Selecciona una sede primero' : 'Buscar ubicación...'"
                            x-disabled="idSedeNueva === ''"
                        />
                    </div>


                    {{-- FECHA --}}
                    <div>
                        <label
                            for="reassignment-date"
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Fecha de Reasignación *
                        </label>

                        <input
                            id="reassignment-date"
                            type="date"
                            x-model="fechaReasignacion"
                            @change="clearValidationError('fechaReasignacion')"
                            class="
                                w-full
                                px-3 py-2.5
                                rounded-md
                                border border-[var(--theme-border-strong)]
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
                            x-show="fieldError('fechaReasignacion')"
                            x-cloak
                            x-text="fieldError('fechaReasignacion')"
                            class="
                                mt-1
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        ></p>
                    </div>


                    {{-- ESPACIO --}}
                    <div class="hidden lg:block"></div>


                    {{-- OBSERVACIONES --}}
                    <div class="lg:col-span-2">
                        <label
                            for="reassignment-observations"
                            class="
                                block mb-1.5
                                text-xs
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Observaciones
                            <span>
                                (Opcional)
                            </span>
                        </label>

                        <textarea
                            id="reassignment-observations"
                            x-model="observacionesReasignacion"
                            rows="5"
                            placeholder="Escribe alguna observación relacionada con la reasignación..."
                            class="
                                w-full
                                px-3 py-2.5
                                rounded-lg
                                border border-[var(--theme-border-strong)]
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
                ACCIONES
            ======================================================== --}}
            <div
                class="
                    flex flex-col-reverse
                    sm:flex-row
                    sm:items-center
                    sm:justify-end
                    gap-3
                    pb-2
                "
            >
                <a
                    href="{{ route('asignaciones.index') }}"
                    wire:navigate
                    class="
                        w-full sm:w-auto
                        h-11 px-5
                        flex items-center justify-center
                        rounded-lg
                        border border-[var(--theme-border-strong)]
                        bg-[var(--theme-surface)]
                        text-sm font-medium
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
                    :disabled="savingVisual"
                    :aria-busy="savingVisual ? 'true' : 'false'"
                    class="
                        w-full sm:w-auto
                        h-11 px-5
                        flex items-center justify-center gap-2
                        rounded-lg
                        bg-[var(--theme-primary)]
                        hover:bg-[var(--theme-primary-hover)]
                        text-sm font-medium
                        text-white
                        disabled:opacity-60
                        disabled:cursor-not-allowed
                        transition-colors
                    "
                >
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
                                : 'Guardar Reasignación'
                        "
                    ></span>
                </button>
            </div>

        </form>

    </div>

</div>