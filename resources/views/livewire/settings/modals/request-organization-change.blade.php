{{-- ============================================================
    MODAL: SOLICITAR CAMBIO DE INFORMACIÓN ORGANIZACIONAL
============================================================ --}}
<div
    x-data="{ open: false }"

    @abrir-solicitud-cambio-organizacion.window="open = true"

    @solicitud-organizacion-enviada.window="open = false"

    x-show="open"
    x-cloak

    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"

    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"

    @keydown.escape.window="
        if (open) {
            open = false;
            $wire.closeOrganizationChangeRequestModal();
        }
    "

    class="
        fixed
        inset-0
        z-[100]

        flex
        items-center
        justify-center

        px-4
        py-6
    "
>

    {{-- Fondo --}}
    <div
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"

        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        @click="
            open = false;
            $wire.closeOrganizationChangeRequestModal();
        "

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
    ></div>


    {{-- Modal --}}
    <div
        class="
            relative
            z-10

            w-full
            max-w-lg

            max-h-[90vh]
            overflow-y-auto

            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            theme-shadow-xl
        "
    >

        {{-- ====================================================
            CABECERA
        ==================================================== --}}
        <div
            class="
                sticky
                top-0
                z-10

                px-5
                py-4

                bg-[var(--theme-surface)]

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
                    class="
                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Solicitar actualización
                </h3>

                <p
                    class="
                        mt-1
                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Solicita la corrección de información asociada a tu organización.
                </p>

            </div>


            <button
                type="button"
                @click="
                    open = false;
                    $wire.closeOrganizationChangeRequestModal();
                "
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


        <form wire:submit="requestOrganizationChange">

            <div class="p-5 space-y-4">

                {{-- ====================================================
                    AVISO
                ==================================================== --}}
                <div
                    class="
                        p-3

                        rounded-lg

                        bg-[var(--theme-primary-soft-subtle)]

                        border
                        border-[var(--theme-primary-border)]

                        flex
                        items-start
                        gap-2.5
                    "
                >

                    <svg
                        class="
                            w-4
                            h-4
                            shrink-0
                            mt-0.5

                            text-[var(--theme-primary)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v6"/>
                        <path d="M12 7h.01"/>
                    </svg>


                    <p
                        class="
                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Estos datos son administrados por tu organización.
                        Tu solicitud será revisada por un administrador y
                        no modificará automáticamente la información de tu cuenta.
                    </p>

                </div>


                {{-- ====================================================
                    DATO A MODIFICAR
                ==================================================== --}}
                <div>

                    <label
                        for="organizationChangeField"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Información que deseas actualizar

                        <span class="text-[var(--theme-danger)]">*</span>
                    </label>


                    <select
                        id="organizationChangeField"
                        wire:model.live="organizationChangeField"
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
                            Selecciona una opción
                        </option>

                        <option value="numero_colaborador">
                            Número de colaborador
                        </option>

                        <option value="puesto">
                            Puesto
                        </option>

                        <option value="rol">
                            Rol
                        </option>

                        <option value="area">
                            Área
                        </option>

                        <option value="departamento">
                            Departamento
                        </option>
                    </select>


                    @error('organizationChangeField')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- ====================================================
                    VALOR ACTUAL
                ==================================================== --}}
                @if ($organizationChangeField !== '')

                    <div>

                        <label
                            class="
                                block
                                mb-1.5

                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Información actual
                        </label>


                        <div
                            class="
                                min-h-10

                                px-3
                                py-2.5

                                rounded-md

                                bg-[var(--theme-surface-soft)]

                                border
                                border-[var(--theme-border)]

                                text-sm
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $organizationCurrentValue ?: 'Sin asignar' }}
                        </div>

                    </div>

                @endif


                {{-- ====================================================
                    VALOR SOLICITADO
                ==================================================== --}}
                <div>

                    <label
                        for="organizationRequestedValue"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Valor solicitado

                        <span class="text-[var(--theme-danger)]">*</span>
                    </label>


                    <input
                        id="organizationRequestedValue"
                        type="text"
                        wire:model="organizationRequestedValue"

                        placeholder="Ingresa la información correcta"

                        @disabled($organizationChangeField === '')

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

                            placeholder:text-[var(--theme-text-muted)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]

                            disabled:opacity-50
                            disabled:cursor-not-allowed
                            disabled:bg-[var(--theme-surface-soft)]
                        "
                    >


                    @error('organizationRequestedValue')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- ====================================================
                    MOTIVO
                ==================================================== --}}
                <div>

                    <label
                        for="organizationChangeReason"
                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Motivo o comentario

                        <span
                            class="
                                font-normal
                                text-[var(--theme-text-muted)]
                            "
                        >
                            (opcional)
                        </span>
                    </label>


                    <textarea
                        id="organizationChangeReason"
                        wire:model="organizationChangeReason"

                        rows="3"

                        placeholder="Agrega información que ayude al administrador a revisar tu solicitud."

                        class="
                            w-full

                            px-3
                            py-2.5

                            rounded-md

                            resize-none

                            bg-[var(--theme-surface)]

                            border
                            border-[var(--theme-border-strong)]

                            text-sm
                            text-[var(--theme-text)]

                            placeholder:text-[var(--theme-text-muted)]

                            focus:ring-1
                            focus:ring-[var(--theme-primary)]
                            focus:border-[var(--theme-primary)]
                        "
                    ></textarea>


                    @error('organizationChangeReason')
                        <p
                            class="
                                mt-1.5
                                text-xs
                                text-[var(--theme-danger)]
                            "
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- ====================================================
                ACCIONES
            ==================================================== --}}
            <div
                class="
                    sticky
                    bottom-0

                    px-5
                    py-4

                    bg-[var(--theme-surface-soft)]

                    border-t
                    border-[var(--theme-border)]

                    flex
                    items-center
                    justify-end
                    gap-2
                "
            >

                <button
                    type="button"

                    @click="
                        open = false;
                        $wire.closeOrganizationChangeRequestModal();
                    "

                    wire:loading.attr="disabled"
                    wire:target="requestOrganizationChange"

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


                <button
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="requestOrganizationChange"

                    @disabled($organizationChangeField === '')

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
                    <span
                        wire:loading.class="invisible"
                        wire:target="requestOrganizationChange"
                    >
                        Enviar solicitud
                    </span>

                    <span
                        wire:loading.flex
                        wire:target="requestOrganizationChange"

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
                            Enviando solicitud...
                        </span>
                    </span>
                </button>

            </div>

        </form>

    </div>

</div>