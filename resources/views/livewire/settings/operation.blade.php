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

        <div class="min-w-0">

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


        {{-- Solicitar cambio --}}
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
            <span class="hidden sm:inline">
                Solicitar cambio
            </span>

            <span class="sm:hidden">
                Solicitar
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

            px-4
            py-3

            rounded-lg

            bg-[var(--theme-primary-soft-subtle)]

            border
            border-[var(--theme-primary-border)]
        "
    >

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


    {{-- ====================================================
        DATOS ORGANIZACIONALES
    ==================================================== --}}
    <div class="border-t border-[var(--theme-border)]">
        <div
            class="
                px-4
                sm:px-5
                py-5

                grid
                grid-cols-1
                sm:grid-cols-2
                xl:grid-cols-3

                gap-x-10
                gap-y-6
            "
        >

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Número de colaborador
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->numero_colaborador ?: 'Sin especificar' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Sede
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->sede_nombre ?: 'Sin asignar' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Área
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->area_nombre ?: 'Sin asignar' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Departamento
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->departamento_nombre ?: 'Sin asignar' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Puesto
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->puesto ?: 'Sin puesto asignado' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Rol
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->rol_nombre ?? 'Sin rol asignado' }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Fecha de alta
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->fecha_registro
                        ? \Illuminate\Support\Carbon::parse($usuario->fecha_registro)->format('d/m/Y')
                        : '—'
                    }}
                </p>
            </div>

            <div>
                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Último acceso
                </p>
                <p class="mt-2 text-sm font-medium text-[var(--theme-text)]">
                    {{ $usuario->ultimo_acceso
                        ? \Illuminate\Support\Carbon::parse($usuario->ultimo_acceso)->format('d/m/Y H:i')
                        : 'Sin registro'
                    }}
                </p>
            </div>

        </div>
    </div>


            {{-- ====================================================
                ESPACIO FINAL EN ESCRITORIO
            ==================================================== --}}
            <div
                class="
                    hidden
                    xl:block
                "
            ></div>

        </div>

    </div>

</section>