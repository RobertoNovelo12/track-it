<div class="space-y-5">

    {{-- ============================================================
        ACCESO
    ============================================================ --}}
    <section
        class="
            bg-[var(--theme-surface)]

            border
            border-[var(--theme-border)]

            rounded-xl

            overflow-hidden
        "
    >
        {{-- Cabecera --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                border-b
                border-[var(--theme-border)]

                flex
                items-start
                gap-3
            "
        >
            <div
                class="
                    w-9
                    h-9
                    shrink-0

                    rounded-lg

                    flex
                    items-center
                    justify-center

                    bg-[var(--theme-primary-soft)]
                    text-[var(--theme-primary)]
                "
            >
                <svg
                    class="w-5 h-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <rect
                        x="5"
                        y="10"
                        width="14"
                        height="10"
                        rx="2"
                    />

                    <path d="M8 10V7a4 4 0 018 0v3"/>
                </svg>
            </div>


            <div class="min-w-0">

                <h2
                    class="
                        text-sm
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Acceso
                </h2>

                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Protege tu cuenta y controla cómo accedes al sistema.
                </p>

            </div>
        </div>


        {{-- ========================================================
            CAMBIAR CONTRASEÑA
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-4
            "
        >
            <div class="min-w-0">

                <p
                    class="
                        text-sm
                        font-medium
                        text-[var(--theme-text-strong)]
                    "
                >
                    Cambiar contraseña
                </p>

                <p
                    class="
                        mt-1

                        text-xs
                        leading-relaxed
                        text-[var(--theme-text-muted)]
                    "
                >
                    Actualiza tu contraseña de acceso de forma segura.
                </p>

            </div>


            <button
                type="button"

                @click="
                    $dispatch('abrir-cambio-password')
                "

                class="
                    shrink-0

                    h-9
                    px-4

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
                    hover:border-[var(--theme-primary-border)]

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
                    <circle
                        cx="8"
                        cy="15"
                        r="3"
                    />

                    <path
                        d="M10.2 12.8L18 5l1 1 1.5-1.5 2 2L21 8l1 1-7.8 7.8"
                    />
                </svg>

                Cambiar contraseña
            </button>
        </div>


        {{-- Separador --}}
        <div
            class="
                mx-4
                sm:mx-5

                border-t
                border-[var(--theme-border)]
            "
        ></div>


        {{-- ========================================================
            AUTENTICACIÓN EN DOS PASOS
        ======================================================== --}}
        <div
            class="
                px-4
                sm:px-5
                py-4

                flex
                flex-col

                sm:flex-row
                sm:items-center
                sm:justify-between

                gap-4
            "
        >
            <div
                class="
                    min-w-0

                    flex
                    items-start
                    gap-3
                "
            >
                <div
                    class="
                        w-9
                        h-9
                        shrink-0

                        rounded-lg

                        flex
                        items-center
                        justify-center

                        bg-[var(--theme-surface-soft)]
                        text-[var(--theme-text-muted)]
                    "
                >
                    <svg
                        class="w-4.5 h-4.5"

                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <path
                            d="M12 3l7 3v5c0 4.8-2.8 8-7 10-4.2-2-7-5.2-7-10V6l7-3z"
                        />

                        <path
                            d="M9.5 12l1.7 1.7L15 10"
                        />
                    </svg>
                </div>


                <div class="min-w-0">

                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            gap-2
                        "
                    >
                        <p
                            class="
                                text-sm
                                font-medium
                                text-[var(--theme-text-strong)]
                            "
                        >
                            Autenticación en dos pasos
                        </p>


                        @if ($twoFactorEnabled)

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    gap-1.5

                                    px-2
                                    py-0.5

                                    rounded-full

                                    bg-[var(--theme-success-soft)]
                                    text-[var(--theme-success)]

                                    text-[10px]
                                    font-medium
                                "
                            >
                                <span
                                    class="
                                        w-1.5
                                        h-1.5

                                        rounded-full

                                        bg-current
                                    "
                                ></span>

                                Activa
                            </span>

                        @else

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    gap-1.5

                                    px-2
                                    py-0.5

                                    rounded-full

                                    bg-[var(--theme-surface-soft)]
                                    text-[var(--theme-text-muted)]

                                    text-[10px]
                                    font-medium
                                "
                            >
                                Desactivada
                            </span>

                        @endif
                    </div>


                    <p
                        class="
                            mt-1

                            max-w-xl

                            text-xs
                            leading-relaxed
                            text-[var(--theme-text-muted)]
                        "
                    >
                        @if ($twoFactorEnabled)

                            Tu cuenta requiere un código generado por tu aplicación
                            autenticadora al iniciar sesión.

                        @else

                            Añade una capa adicional de seguridad utilizando una
                            aplicación como Google Authenticator o Microsoft Authenticator.

                        @endif
                    </p>

                </div>
            </div>


            @if (! $twoFactorEnabled)

                <button
                    type="button"

                    wire:click="startTwoFactorSetup"

                    wire:loading.attr="disabled"
                    wire:target="startTwoFactorSetup"

                    class="
                        shrink-0

                        h-9
                        px-4

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
                        hover:border-[var(--theme-primary-border)]

                        disabled:opacity-60
                        disabled:cursor-wait

                        transition-colors
                    "
                >
                    <span
                        wire:loading.remove
                        wire:target="startTwoFactorSetup"

                        class="
                            inline-flex
                            items-center
                            gap-2
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
                                d="M12 3l7 3v5c0 4.8-2.8 8-7 10-4.2-2-7-5.2-7-10V6l7-3z"
                            />

                            <path d="M12 8v6"/>
                            <path d="M9 11h6"/>
                        </svg>

                        Activar
                    </span>


                    <span
                        wire:loading
                        wire:target="startTwoFactorSetup"

                        class="
                            inline-flex
                            items-center
                            gap-2
                        "
                    >
                        <svg
                            class="
                                w-4
                                h-4
                                animate-spin
                            "

                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            />
                        </svg>

                        Preparando...
                    </span>
                </button>

            @else

               <button
                    type="button"

                    @click="
                        $dispatch('abrir-administracion-two-factor')
                    "

                    class="
                        shrink-0

                        h-9
                        px-4

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
                        hover:border-[var(--theme-primary-border)]

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
                        <circle
                            cx="12"
                            cy="12"
                            r="3"
                        />

                        <path
                            d="M4 12h2M18 12h2M12 4v2M12 18v2"
                        />
                    </svg>

                    Administrar
                </button>

            @endif
        </div>

    </section>

</div>