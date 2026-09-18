<div
    x-data="{
        open: false,
        step: 'overview',
        passwordVisible: false,
        copiedCodes: false,

        openModal() {
            this.step = 'overview';
            this.passwordVisible = false;
            this.copiedCodes = false;
            this.open = true;

            $wire.resetTwoFactorManagement();
        },

        close() {
            this.open = false;
            this.step = 'overview';
            this.passwordVisible = false;
            this.copiedCodes = false;

            $wire.resetTwoFactorManagement();
        },

        showRegenerate() {
            this.step = 'regenerate';
            this.passwordVisible = false;

            $wire.resetTwoFactorManagement();
        },

        showDisable() {
            this.step = 'disable';
            this.passwordVisible = false;

            $wire.resetTwoFactorManagement();
        },

        copyRecoveryCodes() {
            const codes = Array
                .from(
                    this.$refs.recoveryCodes
                        .querySelectorAll('[data-recovery-code]')
                )
                .map((element) => element.innerText.trim())
                .join('\n');

            navigator.clipboard
                .writeText(codes)
                .then(() => {
                    this.copiedCodes = true;

                    setTimeout(() => {
                        this.copiedCodes = false;
                    }, 2000);
                });
        }
    }"

    @abrir-administracion-two-factor.window="
        openModal()
    "

    @two-factor-recovery-codes-regenerated.window="
        step = 'recovery';
        passwordVisible = false;
        copiedCodes = false;
    "

    @two-factor-disabled.window="
        open = false;
        step = 'overview';
        passwordVisible = false;
    "

    @keydown.escape.window="
        if (open) {
            close();
        }
    "

    x-show="open"
    x-cloak

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
        @click="close()"

        x-show="open"

        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"

        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        class="
            absolute
            inset-0

            bg-[var(--theme-overlay)]
            backdrop-blur-[2px]
        "
    ></div>


    {{-- Modal --}}
    <div
        @click.stop

        x-show="open"

        x-transition:enter="
            transition
            ease-out
            duration-150
        "

        x-transition:enter-start="
            opacity-0
            translate-y-2
            scale-[0.98]
        "

        x-transition:enter-end="
            opacity-100
            translate-y-0
            scale-100
        "

        x-transition:leave="
            transition
            ease-in
            duration-100
        "

        x-transition:leave-start="
            opacity-100
            translate-y-0
            scale-100
        "

        x-transition:leave-end="
            opacity-0
            translate-y-2
            scale-[0.98]
        "

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
                    Administrar autenticación en dos pasos
                </h3>

                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Administra tus métodos de recuperación y la protección
                    adicional de tu cuenta.
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
            RESUMEN
        ======================================================== --}}
        <div
            x-show="step === 'overview'"
            x-cloak

            class="p-5 space-y-5"
        >
            {{-- Estado --}}
            <div
                class="
                    p-4

                    rounded-lg

                    bg-[var(--theme-success-soft)]

                    border
                    border-[var(--theme-success-border)]

                    flex
                    items-start
                    gap-3
                "
            >
                <div
                    class="
                        w-8
                        h-8
                        shrink-0

                        rounded-full

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface)]
                        text-[var(--theme-success)]
                    "
                >
                    <svg
                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M5 12l4 4L19 6"/>
                    </svg>
                </div>


                <div>

                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Autenticación en dos pasos activa
                    </p>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Tu cuenta está protegida mediante una aplicación
                        autenticadora.
                    </p>

                </div>
            </div>


            {{-- Recovery codes --}}
            <div
                class="
                    py-1

                    flex
                    flex-col
                    sm:flex-row
                    sm:items-center
                    sm:justify-between

                    gap-4
                "
            >
                <div>

                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Códigos de recuperación
                    </p>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Genera códigos nuevos si perdiste los anteriores
                        o alguno pudo quedar expuesto.
                    </p>

                </div>


                <button
                    type="button"

                    @click="showRegenerate()"

                    class="
                        shrink-0

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

                        transition-colors
                    "
                >
                    Regenerar códigos
                </button>
            </div>


            <div
                class="
                    border-t
                    border-[var(--theme-border)]
                "
            ></div>


            {{-- Desactivar --}}
            <div
                class="
                    py-1

                    flex
                    flex-col
                    sm:flex-row
                    sm:items-center
                    sm:justify-between

                    gap-4
                "
            >
                <div>

                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-danger)]
                        "
                    >
                        Desactivar autenticación en dos pasos
                    </p>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Tu cuenta volverá a requerir únicamente la contraseña
                        para iniciar sesión.
                    </p>

                </div>


                <button
                    type="button"

                    @click="showDisable()"

                    class="
                        shrink-0

                        h-9
                        px-4

                        rounded-md

                        border
                        border-[var(--theme-danger-border)]

                        bg-[var(--theme-surface)]

                        text-xs
                        font-medium
                        text-[var(--theme-danger)]

                        hover:bg-[var(--theme-danger-soft)]

                        transition-colors
                    "
                >
                    Desactivar
                </button>
            </div>
        </div>


        {{-- ========================================================
            REGENERAR CÓDIGOS
        ======================================================== --}}
        <form
            x-show="step === 'regenerate'"
            x-cloak

            wire:submit="regenerateTwoFactorRecoveryCodes"
        >
            <div class="p-5 space-y-4">

                <div>

                    <h4
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Regenerar códigos de recuperación
                    </h4>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Los códigos actuales dejarán de funcionar inmediatamente.
                        Confirma tu contraseña para continuar.
                    </p>

                </div>


                {{-- Contraseña --}}
                <div>

                    <label
                        for="twoFactorRegeneratePassword"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Contraseña actual
                    </label>


                    <div class="relative">

                        <input
                            id="twoFactorRegeneratePassword"

                            :type="
                                passwordVisible
                                    ? 'text'
                                    : 'password'
                            "

                            wire:model="twoFactorManagementPassword"

                            autocomplete="current-password"

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

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('twoFactorManagementPassword')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        <button
                            type="button"

                            @click="
                                passwordVisible =
                                    !passwordVisible
                            "

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
                        </button>

                    </div>


                    @error('twoFactorManagementPassword')

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


            <div
                class="
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
                        step = 'overview';
                        passwordVisible = false;
                        $wire.resetTwoFactorManagement();
                    "

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
                    "
                >
                    Volver
                </button>


                <button
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="regenerateTwoFactorRecoveryCodes"

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
                        disabled:cursor-wait

                        transition-colors
                    "
                >
                    <span
                        wire:loading.class="invisible"
                        wire:target="regenerateTwoFactorRecoveryCodes"
                    >
                        Regenerar códigos
                    </span>

                    <span
                        wire:loading.flex
                        wire:target="regenerateTwoFactorRecoveryCodes"

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
                            Generando códigos...
                        </span>
                    </span>
                </button>
            </div>
        </form>


        {{-- ========================================================
            CÓDIGOS NUEVOS
        ======================================================== --}}
        <div
            x-show="step === 'recovery'"
            x-cloak
        >
            <div class="p-5 space-y-4">

                <div>

                    <h4
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Nuevos códigos de recuperación
                    </h4>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Guarda estos códigos ahora. Los anteriores ya no
                        funcionan y estos se mostrarán una sola vez.
                    </p>

                </div>


                <div
                    x-ref="recoveryCodes"

                    class="
                        grid
                        grid-cols-1
                        sm:grid-cols-2

                        gap-2
                    "
                >
                    @foreach ($twoFactorRecoveryCodes as $code)

                        <code
                            data-recovery-code

                            wire:key="managed-recovery-code-{{ $loop->index }}"

                            class="
                                px-3
                                py-2.5

                                rounded-md

                                bg-[var(--theme-surface-soft)]

                                border
                                border-[var(--theme-border)]

                                text-center
                                text-xs
                                font-mono
                                tracking-wider
                                text-[var(--theme-text-strong)]
                            "
                        >
                            {{ $code }}
                        </code>

                    @endforeach
                </div>


                <button
                    type="button"

                    @click="copyRecoveryCodes()"

                    class="
                        w-full
                        h-10

                        inline-flex
                        items-center
                        justify-center
                        gap-2

                        rounded-md

                        border
                        border-[var(--theme-border-strong)]

                        bg-[var(--theme-surface)]

                        text-xs
                        font-medium
                        text-[var(--theme-text)]

                        hover:bg-[var(--theme-surface-soft)]

                        transition-colors
                    "
                >
                    <span
                        x-text="
                            copiedCodes
                                ? 'Códigos copiados'
                                : 'Copiar códigos'
                        "
                    ></span>
                </button>

            </div>


            <div
                class="
                    px-5
                    py-4

                    bg-[var(--theme-surface-soft)]

                    border-t
                    border-[var(--theme-border)]

                    flex
                    justify-end
                "
            >
                <button
                    type="button"

                    @click="close()"

                    class="
                        h-9
                        px-4

                        rounded-md

                        bg-[var(--theme-primary)]
                        text-white

                        text-xs
                        font-medium

                        hover:bg-[var(--theme-primary-hover)]

                        transition-colors
                    "
                >
                    Ya guardé mis códigos
                </button>
            </div>
        </div>


        {{-- ========================================================
            DESACTIVAR
        ======================================================== --}}
        <form
            x-show="step === 'disable'"
            x-cloak

            wire:submit="disableTwoFactor"
        >
            <div class="p-5 space-y-4">

                <div
                    class="
                        p-3

                        rounded-lg

                        bg-[var(--theme-danger-soft)]

                        border
                        border-[var(--theme-danger-border)]
                    "
                >
                    <p
                        class="
                            text-sm
                            font-medium
                            text-[var(--theme-danger)]
                        "
                    >
                        Desactivar autenticación en dos pasos
                    </p>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Tu secreto del autenticador y todos tus códigos
                        de recuperación serán eliminados.
                    </p>
                </div>


                <div>

                    <label
                        for="twoFactorDisablePassword"

                        class="
                            block
                            mb-1.5

                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        Contraseña actual
                    </label>


                    <div class="relative">

                        <input
                            id="twoFactorDisablePassword"

                            :type="
                                passwordVisible
                                    ? 'text'
                                    : 'password'
                            "

                            wire:model="twoFactorManagementPassword"

                            autocomplete="current-password"

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

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('twoFactorManagementPassword')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        <button
                            type="button"

                            @click="
                                passwordVisible =
                                    !passwordVisible
                            "

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
                        </button>

                    </div>


                    @error('twoFactorManagementPassword')

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


            <div
                class="
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
                        step = 'overview';
                        passwordVisible = false;
                        $wire.resetTwoFactorManagement();
                    "

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
                    "
                >
                    Cancelar
                </button>


                <button
                    type="submit"

                    wire:loading.attr="disabled"
                    wire:target="disableTwoFactor"

                    class="
                        relative

                        h-9
                        px-4

                        rounded-md

                        bg-[var(--theme-danger)]
                        text-white

                        text-xs
                        font-medium

                        disabled:opacity-60
                        disabled:cursor-wait

                        transition-colors
                    "
                >
                    <span
                        wire:loading.class="invisible"
                        wire:target="disableTwoFactor"
                    >
                        Desactivar 2FA
                    </span>

                    <span
                        wire:loading.flex
                        wire:target="disableTwoFactor"

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
                            Desactivando autenticación en dos pasos...
                        </span>
                    </span>
                </button>
            </div>
        </form>

    </div>
</div>