<div
    class="
        grid
        grid-cols-1
        xl:grid-cols-[280px_minmax(0,1fr)]
        gap-5
    "
>
    @include('livewire.settings.partials.profile-summary')

    <div class="space-y-5">
        {{-- ====================================================
            INFORMACIÓN PERSONAL
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-xl

                overflow-hidden
            "
        >

            <div
                class="
                    px-4
                    sm:px-5
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

                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Información personal
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Información básica asociada a tu cuenta.
                    </p>

                </div>


                {{-- Visual por ahora --}}
                <button
                x-data="{}"
                type="button"
                @click="$dispatch('abrir-edicion-perfil')"
                class="
                    shrink-0

                    h-8
                    px-3

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
                        class="w-3.5 h-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <path d="M4 20h4l10-10-4-4L4 16v4z"/>
                        <path d="M13 7l4 4"/>
                    </svg>

                    <span class="hidden sm:inline">
                        Editar
                    </span>
                </button>

            </div>


            <div
                class="
                    divide-y
                    divide-[var(--theme-border)]
                "
            >

                {{-- Nombre --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3

                        grid
                        grid-cols-1
                        sm:grid-cols-[180px_1fr]

                        gap-1
                        sm:gap-4
                    "
                >
                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Nombre completo
                    </span>

                    <span
                        class="
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $nombreCompleto }}
                    </span>
                </div>


                {{-- Correo --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3

                        grid
                        grid-cols-1
                        sm:grid-cols-[180px_1fr]

                        gap-1
                        sm:gap-4
                    "
                >
                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Correo electrónico
                    </span>

                    <span
                        class="
                            text-xs
                            text-[var(--theme-text)]
                            break-all
                        "
                    >
                        {{ $usuario->correo }}
                    </span>
                </div>


                {{-- Usuario --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3

                        grid
                        grid-cols-1
                        sm:grid-cols-[180px_1fr]

                        gap-1
                        sm:gap-4
                    "
                >
                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Nombre de usuario
                    </span>

                    <span
                        class="
                            text-xs
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $usuario->username }}
                    </span>
                </div>


                {{-- Teléfono --}}
                <div
                    class="
                        px-4
                        sm:px-5
                        py-3

                        grid
                        grid-cols-1
                        sm:grid-cols-[180px_1fr]

                        gap-1
                        sm:gap-4
                    "
                >
                    <span
                        class="
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Teléfono
                    </span>

                    <span
                        class="
                            text-xs
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $usuario->telefono ?: 'Sin especificar' }}
                    </span>
                </div>

            </div>

        </section>
        @include('livewire.settings.operation')
    </div>
</div>
