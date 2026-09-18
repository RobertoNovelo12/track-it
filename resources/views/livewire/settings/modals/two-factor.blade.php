<div
    x-data="{
        open: false,
        step: 'setup',
        copiedKey: false,
        copiedCodes: false,

        close() {
            this.open = false;

            if (this.step === 'recovery') {
                $wire.finishTwoFactorSetup();
            } else {
                $wire.cancelTwoFactorSetup();
            }

            this.step = 'setup';
            this.copiedKey = false;
            this.copiedCodes = false;
        },

        copyManualKey() {
            const value = this.$refs.manualKey
                .innerText
                .replace(/\s+/g, '');

            navigator.clipboard
                .writeText(value)
                .then(() => {
                    this.copiedKey = true;

                    setTimeout(() => {
                        this.copiedKey = false;
                    }, 2000);
                });
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

    @two-factor-setup-started.window="
        step = 'setup';
        copiedKey = false;
        copiedCodes = false;
        open = true;
    "

    @two-factor-enabled.window="
        step = 'recovery';
        copiedCodes = false;
        open = true;
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
    {{-- ============================================================
        FONDO
    ============================================================ --}}
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


    {{-- ============================================================
        MODAL
    ============================================================ --}}
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
            CONFIGURACIÓN
        ======================================================== --}}
        <div
            x-show="step === 'setup'"
            x-cloak
        >
            {{-- Cabecera --}}
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
                        Activar autenticación en dos pasos
                    </h3>

                    <p
                        class="
                            mt-1

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Configura una aplicación autenticadora para proteger
                        tu cuenta.
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


            <form wire:submit="confirmTwoFactorSetup">

                <div class="p-5 space-y-5">

                    {{-- ====================================================
                        PASO 1
                    ==================================================== --}}
                    <div
                        class="
                            flex
                            items-start
                            gap-3
                        "
                    >
                        <div
                            class="
                                w-7
                                h-7
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-primary-soft)]
                                text-[var(--theme-primary)]

                                text-xs
                                font-semibold
                            "
                        >
                            1
                        </div>


                        <div class="min-w-0">

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Escanea el código QR
                            </p>

                            <p
                                class="
                                    mt-1

                                    text-xs
                                    leading-relaxed
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Abre tu aplicación autenticadora y escanea
                                este código.
                            </p>

                        </div>
                    </div>


                    {{-- QR --}}
                    <div
                        class="
                            flex
                            justify-center
                        "
                    >
                        <div
                            class="
                                p-3

                                rounded-xl

                                bg-white

                                border
                                border-[var(--theme-border)]
                            "
                        >
                            @if ($twoFactorQrCode)

                                <img
                                    src="{{ $twoFactorQrCode }}"
                                    alt="Código QR para configurar autenticación en dos pasos"

                                    class="
                                        w-48
                                        h-48

                                        sm:w-52
                                        sm:h-52
                                    "
                                >

                            @endif
                        </div>
                    </div>


                    {{-- ====================================================
                        CLAVE MANUAL
                    ==================================================== --}}
                    <div
                        class="
                            p-3

                            rounded-lg

                            bg-[var(--theme-surface-soft)]

                            border
                            border-[var(--theme-border)]
                        "
                    >
                        <p
                            class="
                                text-[11px]
                                font-medium
                                text-[var(--theme-text-muted)]
                            "
                        >
                            ¿No puedes escanear el QR?
                        </p>


                        <p
                            class="
                                mt-1

                                text-[11px]
                                leading-relaxed
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Introduce esta clave manualmente en tu aplicación.
                        </p>


                        <div
                            class="
                                mt-3

                                flex
                                items-center
                                gap-2
                            "
                        >
                            <code
                                x-ref="manualKey"

                                class="
                                    min-w-0
                                    flex-1

                                    px-3
                                    py-2

                                    rounded-md

                                    bg-[var(--theme-surface)]

                                    border
                                    border-[var(--theme-border)]

                                    text-xs
                                    font-mono
                                    tracking-wider
                                    text-[var(--theme-text-strong)]

                                    break-all
                                "
                            >
                                {{ $twoFactorManualKey }}
                            </code>


                            <button
                                type="button"

                                @click="copyManualKey()"

                                class="
                                    w-9
                                    h-9
                                    shrink-0

                                    rounded-md

                                    flex
                                    items-center
                                    justify-center

                                    border
                                    border-[var(--theme-border-strong)]

                                    text-[var(--theme-text-muted)]

                                    hover:bg-[var(--theme-surface)]
                                    hover:text-[var(--theme-text)]

                                    transition-colors
                                "

                                aria-label="Copiar clave"
                            >
                                <svg
                                    x-show="!copiedKey"

                                    class="w-4 h-4"

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                >
                                    <rect
                                        x="8"
                                        y="8"
                                        width="11"
                                        height="11"
                                        rx="2"
                                    />

                                    <path
                                        d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"
                                    />
                                </svg>


                                <svg
                                    x-show="copiedKey"
                                    x-cloak

                                    class="
                                        w-4
                                        h-4

                                        text-[var(--theme-success)]
                                    "

                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M5 12l4 4L19 6"/>
                                </svg>
                            </button>
                        </div>
                    </div>


                    {{-- ====================================================
                        PASO 2
                    ==================================================== --}}
                    <div
                        class="
                            pt-1

                            flex
                            items-start
                            gap-3
                        "
                    >
                        <div
                            class="
                                w-7
                                h-7
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-primary-soft)]
                                text-[var(--theme-primary)]

                                text-xs
                                font-semibold
                            "
                        >
                            2
                        </div>


                        <div class="min-w-0">

                            <p
                                class="
                                    text-sm
                                    font-medium
                                    text-[var(--theme-text-strong)]
                                "
                            >
                                Verifica la configuración
                            </p>

                            <p
                                class="
                                    mt-1

                                    text-xs
                                    leading-relaxed
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                Introduce el código de 6 dígitos que aparece
                                en tu aplicación.
                            </p>

                        </div>
                    </div>


                    {{-- Código --}}
                    <div>

                        <label
                            for="twoFactorCode"

                            class="
                                block
                                mb-1.5

                                text-xs
                                font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            Código de verificación
                        </label>


                        <input
                            id="twoFactorCode"

                            type="text"

                            wire:model="twoFactorCode"

                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"

                            autocomplete="one-time-code"

                            placeholder="000000"

                            class="
                                w-full
                                h-11

                                px-3

                                rounded-md

                                bg-[var(--theme-surface)]

                                border
                                border-[var(--theme-border-strong)]

                                text-center
                                text-lg
                                font-medium
                                tracking-[0.35em]
                                text-[var(--theme-text)]

                                placeholder:text-[var(--theme-text-muted)]
                                placeholder:tracking-[0.35em]

                                focus:outline-none
                                focus:ring-1
                                focus:ring-[var(--theme-primary)]
                                focus:border-[var(--theme-primary)]

                                @error('twoFactorCode')
                                    border-[var(--theme-danger)]
                                @enderror
                            "
                        >


                        @error('twoFactorCode')

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


                    {{-- Aviso --}}
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
                            La autenticación en dos pasos no se activará hasta
                            que confirmes correctamente este código.
                        </p>
                    </div>

                </div>


                {{-- Acciones --}}
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
                        wire:target="confirmTwoFactorSetup"

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
                        wire:target="confirmTwoFactorSetup"

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
                            wire:target="confirmTwoFactorSetup"
                        >
                            Confirmar
                        </span>

                        <span
                            wire:loading.flex
                            wire:target="confirmTwoFactorSetup"

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
                                Verificando código...
                            </span>
                        </span>
                    </button>
                </div>

            </form>
        </div>


        {{-- ========================================================
            CÓDIGOS DE RECUPERACIÓN
        ======================================================== --}}
        <div
            x-show="step === 'recovery'"
            x-cloak
        >
            {{-- Cabecera --}}
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

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >
                        <div
                            class="
                                w-7
                                h-7

                                rounded-full

                                flex
                                items-center
                                justify-center

                                bg-[var(--theme-success-soft)]
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


                        <h3
                            class="
                                text-base
                                font-semibold
                                text-[var(--theme-text-strong)]
                            "
                        >
                            2FA activado
                        </h3>
                    </div>


                    <p
                        class="
                            mt-2

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Guarda tus códigos de recuperación antes de continuar.
                    </p>

                </div>
            </div>


            <div class="p-5 space-y-4">

                {{-- Advertencia --}}
                <div
                    class="
                        p-3

                        rounded-lg

                        bg-[var(--theme-warning-soft)]

                        border
                        border-[var(--theme-warning-border)]

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

                            text-[var(--theme-warning)]
                        "

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <path
                            d="M12 3L2.5 20h19L12 3z"
                        />

                        <path d="M12 9v5"/>
                        <path d="M12 17h.01"/>
                    </svg>


                    <div
                        class="
                            text-[11px]
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        <p class="font-medium text-[var(--theme-text)]">
                            Estos códigos se mostrarán una sola vez.
                        </p>

                        <p class="mt-1">
                            Podrás usar uno si pierdes acceso a tu aplicación
                            autenticadora. Cada código solamente puede utilizarse
                            una vez.
                        </p>
                    </div>
                </div>


                {{-- Códigos --}}
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

                            wire:key="recovery-code-{{ $loop->index }}"

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


                {{-- Copiar --}}
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
                    <svg
                        x-show="!copiedCodes"

                        class="w-4 h-4"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <rect
                            x="8"
                            y="8"
                            width="11"
                            height="11"
                            rx="2"
                        />

                        <path
                            d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"
                        />
                    </svg>


                    <svg
                        x-show="copiedCodes"
                        x-cloak

                        class="
                            w-4
                            h-4

                            text-[var(--theme-success)]
                        "

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M5 12l4 4L19 6"/>
                    </svg>


                    <span
                        x-text="
                            copiedCodes
                                ? 'Códigos copiados'
                                : 'Copiar códigos'
                        "
                    ></span>
                </button>

            </div>


            {{-- Acción final --}}
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

    </div>
</div>