<div
    x-data="{
        open: false,

        close() {
            this.open = false;
            $wire.resetPasswordForm();
        }
    }"

    @abrir-cambio-password.window="
        open = true;
        $wire.resetPasswordForm();
    "

    @password-updated.window="
        open = false;
    "

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
            close();
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
    {{-- ============================================================
        FONDO
    ============================================================ --}}
    <div
        @click="close()"

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
    ></div>


    {{-- ============================================================
        MODAL
    ============================================================ --}}
    <div
        @click.stop

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
        {{-- ========================================================
            CABECERA
        ======================================================== --}}
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
                    Cambiar contraseña
                </h3>

                <p
                    class="
                        mt-1

                        text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Actualiza la contraseña utilizada para acceder a tu cuenta.
                </p>

            </div>


            <button
                type="button"
                @click="close()"

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


        {{-- ========================================================
            FORMULARIO
        ======================================================== --}}
        <form wire:submit="updatePassword">

            <div class="p-5 space-y-4">

                {{-- ====================================================
                    CONTRASEÑA ACTUAL
                ==================================================== --}}
                <div
                    x-data="{
                        visible: false
                    }"
                >
                    <label
                        for="currentPassword"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Contraseña actual

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <div class="relative">

                        <input
                            id="currentPassword"

                            :type="visible ? 'text' : 'password'"

                            wire:model="currentPassword"

                            autocomplete="current-password"

                            placeholder="Ingresa tu contraseña actual"

                            class="
                                w-full
                                h-10

                                pl-3
                                pr-10

                                rounded-md

                                bg-[var(--theme-surface)]

                                border
                                border-[var(--theme-border-strong)]

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('currentPassword')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        <button
                            type="button"

                            @click="visible = !visible"

                            class="
                                absolute
                                inset-y-0
                                right-0

                                w-10

                                flex
                                items-center
                                justify-center

                                text-[var(--theme-text-muted)]

                                hover:text-[var(--theme-text)]

                                transition-colors
                            "
                        >
                            <svg
                                x-show="!visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                />
                            </svg>


                            <svg
                                x-show="visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path d="M3 3l18 18"/>

                                <path
                                    d="M10.6 10.6a2 2 0 002.8 2.8"
                                />

                                <path
                                    d="M9.9 5.2A10.6 10.6 0 0112 5c6 0 9.5 7 9.5 7"
                                />

                                <path
                                    d="M6.6 6.6C4 8.3 2.5 12 2.5 12S6 19 12 19"
                                />
                            </svg>
                        </button>

                    </div>


                    @error('currentPassword')
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
                    NUEVA CONTRASEÑA
                ==================================================== --}}
                <div
                    x-data="{
                        visible: false
                    }"
                >
                    <label
                        for="newPassword"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Nueva contraseña

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <div class="relative">

                        <input
                            id="newPassword"

                            :type="visible ? 'text' : 'password'"

                            wire:model="newPassword"

                            autocomplete="new-password"

                            placeholder="Ingresa tu nueva contraseña"

                            class="
                                w-full
                                h-10

                                pl-3
                                pr-10

                                rounded-md

                                bg-[var(--theme-surface)]

                                border
                                border-[var(--theme-border-strong)]

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('newPassword')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        <button
                            type="button"

                            @click="visible = !visible"

                            class="
                                absolute
                                inset-y-0
                                right-0

                                w-10

                                flex
                                items-center
                                justify-center

                                text-[var(--theme-text-muted)]

                                hover:text-[var(--theme-text)]

                                transition-colors
                            "
                        >
                            <svg
                                x-show="!visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                />
                            </svg>


                            <svg
                                x-show="visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path d="M3 3l18 18"/>

                                <path
                                    d="M10.6 10.6a2 2 0 002.8 2.8"
                                />

                                <path
                                    d="M9.9 5.2A10.6 10.6 0 0112 5c6 0 9.5 7 9.5 7"
                                />

                                <path
                                    d="M6.6 6.6C4 8.3 2.5 12 2.5 12S6 19 12 19"
                                />
                            </svg>
                        </button>

                    </div>


                    @error('newPassword')
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


                    <p
                        class="
                            mt-1.5

                            text-[11px]
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Utiliza al menos 8 caracteres y una contraseña diferente
                        a la actual.
                    </p>

                </div>


                {{-- ====================================================
                    CONFIRMAR CONTRASEÑA
                ==================================================== --}}
                <div
                    x-data="{
                        visible: false
                    }"
                >
                    <label
                        for="newPasswordConfirmation"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Confirmar nueva contraseña

                        <span class="text-[var(--theme-danger)]">
                            *
                        </span>
                    </label>


                    <div class="relative">

                        <input
                            id="newPasswordConfirmation"

                            :type="visible ? 'text' : 'password'"

                            wire:model="newPasswordConfirmation"

                            autocomplete="new-password"

                            placeholder="Repite la nueva contraseña"

                            class="
                                w-full
                                h-10

                                pl-3
                                pr-10

                                rounded-md

                                bg-[var(--theme-surface)]

                                border
                                border-[var(--theme-border-strong)]

                                text-sm
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('newPasswordConfirmation')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        <button
                            type="button"

                            @click="visible = !visible"

                            class="
                                absolute
                                inset-y-0
                                right-0

                                w-10

                                flex
                                items-center
                                justify-center

                                text-[var(--theme-text-muted)]

                                hover:text-[var(--theme-text)]

                                transition-colors
                            "
                        >
                            <svg
                                x-show="!visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                />
                            </svg>


                            <svg
                                x-show="visible"
                                x-cloak

                                class="w-4 h-4"

                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <path d="M3 3l18 18"/>

                                <path
                                    d="M10.6 10.6a2 2 0 002.8 2.8"
                                />

                                <path
                                    d="M9.9 5.2A10.6 10.6 0 0112 5c6 0 9.5 7 9.5 7"
                                />

                                <path
                                    d="M6.6 6.6C4 8.3 2.5 12 2.5 12S6 19 12 19"
                                />
                            </svg>
                        </button>

                    </div>


                    @error('newPasswordConfirmation')
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
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />

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
                        Por seguridad, necesitas confirmar tu contraseña actual
                        antes de establecer una nueva.
                    </p>

                </div>

            </div>


            {{-- ========================================================
                ACCIONES
            ======================================================== --}}
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

                    @click="close()"

                    wire:loading.attr="disabled"
                    wire:target="updatePassword"

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
                        wire:target="updatePassword"

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
                            wire:target="updatePassword"
                        >
                            Actualizar contraseña
                        </span>

                        <span
                            wire:loading.flex
                            wire:target="updatePassword"

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
                                Actualizando contraseña...
                            </span>
                        </span>
                    </button>
            </div>

        </form>
    </div>
</div>