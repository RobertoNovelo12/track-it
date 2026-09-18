{{-- ====================================================
    INFORMACIÓN ORGANIZACIONAL
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

    {{-- ====================================================
        CABECERA
    ==================================================== --}}
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
                Información organizacional
            </h2>

            <p
                class="
                    mt-1
                    text-xs
                    text-[var(--theme-text-muted)]
                "
            >
                Datos relacionados con tu operación dentro del sistema.
            </p>

        </div>


        <button
            type="button"
            @click="$dispatch('abrir-solicitud-cambio-organizacion')"
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
                <path d="M4 4h16v12H7l-3 3V4z"/>
                <path d="M8 8h8"/>
                <path d="M8 12h5"/>
            </svg>

            <span class="hidden sm:inline">
                Solicitar cambio
            </span>
        </button>

    </div>


    {{-- ====================================================
        AVISO
    ==================================================== --}}
    <div
        class="
            mx-4
            sm:mx-5
            my-4
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


        <div>

            <p
                class="
                    text-xs
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                Información administrada por tu organización
            </p>

            <p
                class="
                    mt-1
                    text-[11px]
                    leading-relaxed
                    text-[var(--theme-text-muted)]
                "
            >
                El número de colaborador, sede, puesto, rol, área y departamento
                solo pueden ser modificados por un administrador.
                Si alguno de estos datos es incorrecto, puedes solicitar su actualización.
            </p>

        </div>

    </div>


    {{-- ====================================================
        DATOS
    ==================================================== --}}
    <div
        class="
            border-t
            border-[var(--theme-border)]

            divide-y
            divide-[var(--theme-border)]
        "
    >

        {{-- Número de colaborador --}}
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
                Número de colaborador
            </span>

            <span
                class="
                    text-xs
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->numero_colaborador ?: 'Sin especificar' }}
            </span>
        </div>


        {{-- Sede --}}
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
                Sede
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->sede_nombre ?: 'Sin asignar' }}
            </span>
        </div>


        {{-- Área --}}
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
                Área
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->area_nombre ?: 'Sin asignar' }}
            </span>
        </div>


        {{-- Departamento --}}
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
                Departamento
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->departamento_nombre ?: 'Sin asignar' }}
            </span>
        </div>


        {{-- Puesto --}}
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
                Puesto
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->puesto ?: 'Sin puesto asignado' }}
            </span>
        </div>


        {{-- Rol --}}
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
                Rol
            </span>

            <span
                class="
                    text-xs
                    font-medium
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->rol_nombre ?? 'Sin rol asignado' }}
            </span>
        </div>


        {{-- Fecha de alta --}}
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
                Fecha de alta
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->fecha_registro
                    ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                    : '—'
                }}
            </span>
        </div>


        {{-- Último acceso --}}
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
                Último acceso
            </span>

            <span
                class="
                    text-xs
                    text-[var(--theme-text)]
                "
            >
                {{ $usuario->ultimo_acceso
                    ? \Illuminate\Support\Carbon::parse($usuario->ultimo_acceso)->format('d/m/Y H:i')
                    : 'Sin registro'
                }}
            </span>
        </div>

    </div>

</section>