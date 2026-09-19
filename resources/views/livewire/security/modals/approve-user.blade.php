{{-- ============================================================
    MODAL: APROBAR USUARIO

    Apertura/cierre instantáneos en navegador.
    La aprobación sigue siendo procesada por Livewire.
============================================================ --}}

<input
    id="approval-model-open"
    type="checkbox"
    wire:model="approvalModalOpen"
    class="hidden"
>

<input
    id="approval-model-user-id"
    type="hidden"
    wire:model="approvalUserId"
>

<input
    id="approval-model-user-name"
    type="hidden"
    wire:model="approvalUserName"
>

<input
    id="approval-model-user-email"
    type="hidden"
    wire:model="approvalUserEmail"
>


<div
    id="approval-user-modal"

    aria-hidden="{{ $approvalModalOpen ? 'false' : 'true' }}"

    class="
        {{ $approvalModalOpen ? '' : 'hidden' }}

        fixed
        inset-0
        z-[100]

        flex
        items-center
        justify-center

        px-4
        py-6
    "

    onclick="
        if (event.target === this) {
            window.closeApprovalUserModalFast();
        }
    "
>

    {{-- ========================================================
        FONDO
    ======================================================== --}}
    <div
        id="approval-user-backdrop"

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "

        style="
            opacity: 0;
            transition: opacity 180ms ease-out;
        "

        onclick="window.closeApprovalUserModalFast()"
    ></div>


    {{-- ========================================================
        MODAL
    ======================================================== --}}
    <div
        id="approval-user-panel"

        class="
            relative
            z-10

            w-full
            max-w-md

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl

            overflow-hidden
        "

        style="
            opacity: 0;
            transform: translateY(10px) scale(0.975);

            transition:
                opacity 180ms ease-out,
                transform 180ms cubic-bezier(0.22, 1, 0.36, 1);

            will-change:
                opacity,
                transform;
        "

        role="dialog"
        aria-modal="true"
        aria-labelledby="approval-user-modal-title"
    >

        {{-- ====================================================
            ENCABEZADO
        ==================================================== --}}
        <div
            class="
                px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                justify-between
                gap-4
            "
        >

            <div>

                <h3
                    id="approval-user-modal-title"

                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Aprobar usuario
                </h3>

                <p
                    class="
                        mt-1

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Asigna un rol antes de activar la cuenta.
                </p>

            </div>


            <button
                id="approval-user-close-button"

                type="button"

                onclick="window.closeApprovalUserModalFast()"

                wire:loading.attr="disabled"
                wire:target="approveUser"

                class="
                    w-8
                    h-8
                    shrink-0

                    rounded-md

                    flex
                    items-center
                    justify-center

                    text-[var(--theme-text-muted)]

                    hover:bg-[var(--theme-surface-soft)]
                    hover:text-[var(--theme-text-strong)]

                    disabled:opacity-50

                    transition-colors
                "

                aria-label="Cerrar"
            >
                <svg
                    class="w-4 h-4"

                    viewBox="0 0 24 24"

                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6L6 18"/>
                </svg>
            </button>

        </div>


        {{-- ====================================================
            FORMULARIO
        ==================================================== --}}
        <form wire:submit="approveUser">

            <div class="p-5 space-y-5">

                {{-- ====================================================
                    USUARIO
                ==================================================== --}}
                <div
                    class="
                        p-4

                        rounded-lg

                        bg-[var(--theme-surface-soft)]

                        border
                        border-[var(--theme-border)]
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            gap-3
                        "
                    >

                        {{-- Inicial --}}
                        <div
                            id="approval-user-initial"

                            class="
                                w-10
                                h-10
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-primary-soft)]
                                text-[var(--theme-primary)]

                                font-semibold
                                text-sm
                            "
                        >
                            {{ $approvalUserName !== ''
                                ? mb_strtoupper(mb_substr($approvalUserName, 0, 1))
                                : 'U'
                            }}
                        </div>


                        <div class="min-w-0">

                            {{-- Nombre --}}
                            <p
                                id="approval-user-name"

                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]

                                    truncate
                                "
                            >
                                {{ $approvalUserName }}
                            </p>


                            {{-- Correo --}}
                            <p
                                id="approval-user-email"

                                class="
                                    mt-0.5

                                    text-xs
                                    text-[var(--theme-text-muted)]

                                    truncate
                                "
                            >
                                {{ $approvalUserEmail }}
                            </p>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    ROL
                ==================================================== --}}
                <div>

                    <label
                        for="approvalRoleId"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Rol del usuario

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <select
                        id="approvalRoleId"

                        wire:model="approvalRoleId"

                        class="
                            w-full
                            h-10

                            px-3

                            rounded-md

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    >

                        <option value="">
                            Selecciona un rol
                        </option>

                        @foreach ($roles as $rol)

                            <option value="{{ $rol->id_rol }}">
                                {{ $rol->nombre }}
                            </option>

                        @endforeach

                    </select>


                    @error('approvalRoleId')

                        <p
                            id="approval-role-error"

                            class="
                                mt-1.5

                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>

                    @enderror


                    <p
                        class="
                            mt-2

                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        El usuario quedará activo inmediatamente después
                        de confirmar la aprobación.
                    </p>

                </div>

            </div>


            {{-- ====================================================
                ACCIONES
            ==================================================== --}}
            <div
                class="
                    px-5
                    py-4

                    border-t
                    border-[var(--theme-border)]

                    flex
                    items-center
                    justify-end
                    gap-2

                    bg-[var(--theme-surface-soft)]
                "
            >

                {{-- Cancelar --}}
                <button
                    type="button"

                    onclick="window.closeApprovalUserModalFast()"

                    wire:loading.attr="disabled"
                    wire:target="approveUser"

                    class="
                        h-9
                        px-4

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        font-medium
                        text-[var(--theme-text)]

                        hover:bg-[var(--theme-surface-soft)]

                        disabled:opacity-50

                        transition-colors
                    "
                >
                    Cancelar
                </button>


                {{-- Aprobar --}}
                <button
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="approveUser"

                    class="
                        relative

                        h-9
                        px-4

                        rounded-md

                        bg-[var(--theme-primary)]
                        text-white

                        text-xs
                        font-medium

                        hover:bg-[var(--theme-primary-hover)]

                        disabled:opacity-60
                        disabled:cursor-not-allowed

                        transition-colors
                    "
                >

                    {{-- Estado normal --}}
                    <span
                        wire:loading.class="invisible"
                        wire:target="approveUser"
                    >
                        Aprobar usuario
                    </span>


                    {{-- Estado cargando --}}
                    <span
                        wire:loading.flex
                        wire:target="approveUser"

                        class="
                            absolute
                            inset-0

                            items-center
                            justify-center
                        "
                    >

                        <svg
                            class="w-5 h-5 animate-spin"

                            viewBox="0 0 24 24"

                            fill="none"

                            aria-hidden="true"
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

                        <span class="sr-only">
                            Aprobando usuario...
                        </span>

                    </span>

                </button>

            </div>

        </form>

    </div>

</div>


@script
<script>

(() => {

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    const setText = (id, value) => {

        const element =
            document.getElementById(id);

        if (element) {
            element.textContent =
                value ?? '';
        }

    };


    const setModelValue = (id, value) => {

        const input =
            document.getElementById(id);

        if (!input) {
            return;
        }

        input.value =
            value ?? '';

        input.dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true
                }
            )
        );

        input.dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true
                }
            )
        );

    };


    const setModalOpenModel = (open) => {

        const checkbox =
            document.getElementById(
                'approval-model-open'
            );

        if (!checkbox) {
            return;
        }

        checkbox.checked =
            Boolean(open);

        checkbox.dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true
                }
            )
        );

    };


    const hideValidationErrors = () => {

        document
            .getElementById('approval-role-error')
            ?.classList.add('hidden');

    };


    const resetRole = () => {

        const select =
            document.getElementById(
                'approvalRoleId'
            );

        if (!select) {
            return;
        }

        select.value = '';

        select.dispatchEvent(
            new Event(
                'input',
                {
                    bubbles: true
                }
            )
        );

        select.dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true
                }
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Abrir modal inmediatamente
    |--------------------------------------------------------------------------
    */

    window.openApprovalUserModalFromButton =
        (button) => {

            const modal =
                document.getElementById(
                    'approval-user-modal'
                );

            if (!modal || !button) {
                return;
            }


            /*
             * Los datos vienen directamente de la fila
             * que ya está renderizada en pantalla.
             */

            const userId =
                button.dataset.approvalUserId ?? '';

            const userName =
                button.dataset.approvalUserName ?? '';

            const userEmail =
                button.dataset.approvalUserEmail ?? '';


            /*
             * Sincronizamos con Livewire.
             *
             * wire:model es diferido, por lo que esto
             * no realiza una petición al servidor.
             * Los valores viajarán junto con approveUser().
             */

            setModalOpenModel(true);

            setModelValue(
                'approval-model-user-id',
                userId
            );

            setModelValue(
                'approval-model-user-name',
                userName
            );

            setModelValue(
                'approval-model-user-email',
                userEmail
            );


            /*
             * Reiniciamos el rol para cada usuario.
             */

            resetRole();

            hideValidationErrors();


            /*
             * Actualización visual inmediata.
             */

            setText(
                'approval-user-initial',
                userName
                    .trim()
                    .charAt(0)
                    .toUpperCase() || 'U'
            );

            setText(
                'approval-user-name',
                userName
            );

            setText(
                'approval-user-email',
                userEmail
            );


            /*
             * Elementos de animación.
             */

            const backdrop =
                document.getElementById(
                    'approval-user-backdrop'
                );

            const panel =
                document.getElementById(
                    'approval-user-panel'
                );

            const reduceMotion =
                window.matchMedia?.(
                    '(prefers-reduced-motion: reduce)'
                )?.matches ?? false;


            /*
             * Si estaba cerrándose, cancelamos el cierre.
             */

            if (
                window.__approvalUserCloseTimer
            ) {
                clearTimeout(
                    window.__approvalUserCloseTimer
                );

                window.__approvalUserCloseTimer =
                    null;
            }


            /*
             * Mostramos el modal ahora mismo.
             */

            modal.classList.remove('hidden');

            modal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style.overflow =
                'hidden';


            /*
             * Estado inicial de la animación.
             */

            if (backdrop) {

                backdrop.style.opacity =
                    '0';

                backdrop.style.transitionDuration =
                    reduceMotion
                        ? '0ms'
                        : '180ms';

            }


            if (panel) {

                panel.style.opacity =
                    '0';

                panel.style.transform =
                    'translateY(10px) scale(0.975)';

                panel.style.transitionDuration =
                    reduceMotion
                        ? '0ms'
                        : '180ms';

            }


            /*
             * Entrada.
             */

            requestAnimationFrame(() => {

                requestAnimationFrame(() => {

                    if (backdrop) {
                        backdrop.style.opacity =
                            '1';
                    }

                    if (panel) {

                        panel.style.opacity =
                            '1';

                        panel.style.transform =
                            'translateY(0) scale(1)';

                    }


                    document
                        .getElementById(
                            'approval-user-close-button'
                        )
                        ?.focus({
                            preventScroll: true
                        });

                });

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Cerrar modal inmediatamente
    |--------------------------------------------------------------------------
    */

    window.closeApprovalUserModalFast =
        () => {

            const modal =
                document.getElementById(
                    'approval-user-modal'
                );

            const backdrop =
                document.getElementById(
                    'approval-user-backdrop'
                );

            const panel =
                document.getElementById(
                    'approval-user-panel'
                );


            if (
                !modal ||
                modal.classList.contains('hidden')
            ) {
                return;
            }


            const reduceMotion =
                window.matchMedia?.(
                    '(prefers-reduced-motion: reduce)'
                )?.matches ?? false;

            const duration =
                reduceMotion
                    ? 0
                    : 160;


            if (backdrop) {
                backdrop.style.opacity =
                    '0';
            }


            if (panel) {

                panel.style.opacity =
                    '0';

                panel.style.transform =
                    'translateY(8px) scale(0.985)';

            }


            modal.setAttribute(
                'aria-hidden',
                'true'
            );


            if (
                window.__approvalUserCloseTimer
            ) {
                clearTimeout(
                    window.__approvalUserCloseTimer
                );
            }


            window.__approvalUserCloseTimer =
                window.setTimeout(
                    () => {

                        modal.classList.add(
                            'hidden'
                        );

                        setModalOpenModel(false);

                        document.body.style.overflow =
                            '';

                        window.__approvalUserCloseTimer =
                            null;

                    },
                    duration
                );

        };


    /*
    |--------------------------------------------------------------------------
    | Listeners globales
    |--------------------------------------------------------------------------
    */

    if (
        !window.__approvalUserModalListenersInstalled
    ) {

        window.__approvalUserModalListenersInstalled =
            true;


        /*
         * Escape
         */

        window.addEventListener(
            'keydown',
            (event) => {

                if (
                    event.key !== 'Escape'
                ) {
                    return;
                }


                const modal =
                    document.getElementById(
                        'approval-user-modal'
                    );


                if (
                    modal &&
                    !modal.classList.contains(
                        'hidden'
                    )
                ) {
                    window
                        .closeApprovalUserModalFast();
                }

            }
        );


        /*
         * Livewire terminó correctamente.
         *
         * approveUser() ya emite usuario-aprobado.
         */

        window.addEventListener(
            'usuario-aprobado',
            () => {

                window
                    .closeApprovalUserModalFast();

            }
        );

    }

})();

</script>
@endscript