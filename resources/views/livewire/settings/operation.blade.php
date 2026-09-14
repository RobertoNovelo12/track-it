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

    <div
        class="
            px-4
            sm:px-5
            py-4

            border-b
            border-[var(--theme-border)]
        "
    >

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


    <div
        class="
            divide-y
            divide-[var(--theme-border)]
        "
    >

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
                {{ $usuario->area_nombre
                    ?: 'Sin asignar'
                }}
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
                {{ $usuario->departamento_nombre
                    ?: 'Sin asignar'
                }}
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


        {{-- Alta --}}
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
