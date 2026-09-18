        {{-- ====================================================
            BITÁCORA
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

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
                        flex
                        items-center
                        gap-2

                        text-base
                        font-semibold
                        text-[var(--theme-text-strong)]
                    "
                >

                    <svg
                        class="
                            w-5
                            h-5
                            text-[var(--theme-primary)]
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M6 2h9l5 5v15H6z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>

                    Bitácora de auditoría
                </h2>

            </div>
{{-- ============================================================
    BITÁCORA - ESCRITORIO
============================================================ --}}
<div class="hidden lg:block overflow-x-auto">

    <table class="w-full text-xs">

        <thead
            class="
                bg-[var(--theme-surface-soft)]
                text-[var(--theme-text-muted)]
            "
        >
            <tr>
                <th class="px-4 py-3 text-left font-medium">
                    Fecha y hora
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Usuario
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Acción
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Módulo
                </th>

                <th class="px-4 py-3 text-left font-medium">
                    Descripción
                </th>
            </tr>
        </thead>

        <tbody>

            @forelse ($auditoria as $registro)

                <tr
                    class="
                        border-t
                        border-[var(--theme-border)]
                    "
                >

                    <td
                        class="
                            px-4
                            py-3
                            whitespace-nowrap
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ \Illuminate\Support\Carbon::parse($registro->fecha_hora)->format('d/m/Y H:i') }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->usuario_nombre
                            ?: $registro->username
                            ?: 'Sistema'
                        }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->accion }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->modulo }}
                    </td>

                    <td
                        class="
                            px-4
                            py-3
                            min-w-64
                            text-[var(--theme-text-muted)]
                        "
                    >
                        {{ $registro->descripcion ?: '—' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td
                        colspan="5"
                        class="
                            px-5
                            py-10
                            text-center
                            text-sm
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Todavía no hay eventos registrados en la bitácora.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    </div>


    {{-- ============================================================
        BITÁCORA - MÓVIL
    ============================================================ --}}
    <div
        class="
            lg:hidden
            divide-y
            divide-[var(--theme-border)]
        "
    >

        @forelse ($auditoria as $registro)

            <article class="p-4">

                {{-- Encabezado --}}
                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-3
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
                            {{ $registro->usuario_nombre
                                ?: $registro->username
                                ?: 'Sistema'
                            }}
                        </p>

                        <p
                            class="
                                mt-1
                                text-[11px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ \Illuminate\Support\Carbon::parse($registro->fecha_hora)->format('d/m/Y H:i') }}
                        </p>

                    </div>


                    <span
                        class="
                            shrink-0

                            px-2
                            py-1

                            rounded-full

                            bg-[var(--theme-primary-soft)]
                            text-[var(--theme-primary)]

                            text-[10px]
                            font-medium
                        "
                    >
                        {{ $registro->modulo }}
                    </span>

                </div>


                {{-- Acción --}}
                <div class="mt-4">

                    <p
                        class="
                            text-[10px]
                            uppercase
                            tracking-wide
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Acción
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            font-medium
                            text-[var(--theme-text)]
                        "
                    >
                        {{ ucfirst(
                            strtolower(
                                str_replace('_', ' ', $registro->accion)
                            )
                        ) }}
                    </p>

                </div>


                {{-- Descripción --}}
                <div class="mt-3">

                    <p
                        class="
                            text-[10px]
                            uppercase
                            tracking-wide
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Descripción
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            leading-relaxed
                            text-[var(--theme-text)]
                        "
                    >
                        {{ $registro->descripcion ?: 'Sin descripción.' }}
                    </p>

                </div>

            </article>

        @empty

            <div
                class="
                    px-5
                    py-10

                    text-center
                    text-sm
                    text-[var(--theme-text-muted)]
                "
            >
                Todavía no hay eventos registrados en la bitácora.
            </div>

        @endforelse

    </div>

        </section>
