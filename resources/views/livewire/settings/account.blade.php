<div class="space-y-5">

    {{-- ====================================================
        INFORMACIÓN PERSONAL
    ==================================================== --}}
    <section
        class="
            bg-[var(--theme-surface)]
            border border-[var(--theme-border)]
            rounded-xl
            overflow-hidden
        "
    >

        <div
            class="
                px-4 sm:px-5 py-4
                border-b border-[var(--theme-border)]
                flex items-start justify-between gap-4
            "
        >
            <div>
                <h2
                    class="
                        text-sm font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >
                    Información personal
                </h2>

                <p
                    class="
                        mt-1 text-xs
                        text-[var(--theme-text-muted)]
                    "
                >
                    Información básica asociada a tu cuenta.
                </p>
            </div>

            <button
                x-data="{}"
                type="button"
                @click="$dispatch('abrir-edicion-perfil')"
                class="
                    shrink-0
                    h-8 px-3
                    inline-flex items-center justify-center
                    rounded-md
                    border border-[var(--theme-border-strong)]
                    bg-[var(--theme-surface)]
                    text-xs font-medium
                    text-[var(--theme-text)]
                    hover:bg-[var(--theme-surface-soft)]
                    hover:border-[var(--theme-primary-border)]
                    transition-colors
                "
            >
                <span>Editar</span>
            </button>
        </div>


        <div class="p-4 sm:p-5">
            <div
                class="
                    grid
                    grid-cols-1
                    xl:grid-cols-[220px_minmax(0,1fr)]
                    gap-5
                    items-start
                "
            >

                {{-- ====================================================
                    RESUMEN DE PERFIL
                ==================================================== --}}
                <div
                    class="
                        rounded-xl
                        bg-[var(--theme-surface-soft)]
                        p-5

                        flex
                        flex-col
                        items-center
                        justify-center

                        text-center
                    "
                >
                    <div
                        class="
                            w-20 h-20
                            rounded-full
                            flex items-center justify-center
                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]
                            text-2xl font-semibold uppercase
                        "
                    >
                        {{ mb_substr($usuario->nombres ?? '?', 0, 1) }}
                        {{ mb_substr($usuario->apellido_paterno ?? '', 0, 1) }}
                    </div>

                    <h3
                        class="
                            mt-4
                            text-base font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        {{ $nombreCompleto }}
                    </h3>

                    <p
                        class="
                            mt-1 text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ '@' . $usuario->username }}
                    </p>

                    @php
                        $estadoClave = strtoupper($usuario->estado_clave ?? '');

                        $estadoClasses = match ($estadoClave) {
                            'ACTIVO' =>
                                'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                            'PENDIENTE' =>
                                'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                            'INACTIVO', 'BAJA' =>
                                'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                            default =>
                                'bg-[var(--theme-surface)] text-[var(--theme-text-muted)]',
                        };
                    @endphp

                    <span
                        class="
                            mt-3
                            inline-flex items-center
                            px-2.5 py-1
                            rounded-full
                            text-[10px] font-medium
                            {{ $estadoClasses }}
                        "
                    >
                        {{ $usuario->estado_nombre ?? 'Sin estado' }}
                    </span>
                </div>


                {{-- ====================================================
                    DATOS PERSONALES LIMPIOS
                ==================================================== --}}
                <div
                    class="
                        grid
                        grid-cols-1
                        md:grid-cols-2
                        gap-x-10
                        gap-y-6
                        content-start
                    "
                >

                    <div>
                        <p
                            class="
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Nombre completo
                        </p>

                        <p
                            class="
                                mt-2
                                text-sm font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $nombreCompleto }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Correo electrónico
                        </p>

                        <p
                            class="
                                mt-2
                                text-sm font-medium
                                text-[var(--theme-text)]
                                break-all
                            "
                        >
                            {{ $usuario->correo }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Nombre de usuario
                        </p>

                        <p
                            class="
                                mt-2
                                text-sm font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $usuario->username }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            Teléfono
                        </p>

                        <p
                            class="
                                mt-2
                                text-sm font-medium
                                text-[var(--theme-text)]
                            "
                        >
                            {{ $usuario->telefono ?: 'Sin especificar' }}
                        </p>
                    </div>

                </div>

            </div>
        </div>

    </section>


    {{-- ====================================================
        INFORMACIÓN ORGANIZACIONAL
    ==================================================== --}}
    @include('livewire.settings.operation')

</div>