        {{-- ====================================================
            SOLICITUDES DE ACTUALIZACIÓN
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

            {{-- Cabecera --}}
            <div
                class="
                    px-4
                    sm:px-5
                    py-4

                    border-b
                    border-[var(--theme-border)]

                    flex
                    items-center
                    justify-between
                    gap-4
                "
            >

                <div>

                    <h2
                        class="
                            text-base
                            font-semibold
                            text-[var(--theme-text-strong)]
                        "
                    >
                        Solicitudes de actualización
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-[var(--theme-text-muted)]
                        "
                    >
                        Revisa los cambios organizacionales solicitados por los usuarios.
                    </p>

                </div>


                @if ($solicitudesCambioPendientes > 0)

                    <span
                        class="
                            shrink-0

                            px-2.5
                            py-1

                            rounded-full

                            bg-[var(--theme-warning-soft)]
                            text-[var(--theme-warning)]

                            text-[10px]
                            font-medium
                        "
                    >
                        {{ $solicitudesCambioPendientes }}
                        {{ $solicitudesCambioPendientes === 1 ? 'pendiente' : 'pendientes' }}
                    </span>

                @endif

            </div>


            {{-- =================================================
                TABLA ESCRITORIO
            ================================================= --}}
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
                                Usuario
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Información
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Valor actual
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Solicitado
                            </th>

                            <th class="px-4 py-3 text-left font-medium">
                                Fecha
                            </th>

                            <th class="px-4 py-3 text-right font-medium">
                                Acción
                            </th>

                        </tr>
                    </thead>


                    <tbody>

                        @forelse ($solicitudesCambio as $solicitud)

                            @php
                                $campoLabel = match ($solicitud->campo) {
                                    'numero_colaborador' => 'Número de colaborador',
                                    'puesto' => 'Puesto',
                                    'rol' => 'Rol',
                                    'area' => 'Área',
                                    'departamento' => 'Departamento',
                                    default => ucfirst(
                                        str_replace('_', ' ', $solicitud->campo)
                                    ),
                                };
                            @endphp


                            <tr
                                wire:key="solicitud-{{ $solicitud->id_solicitud }}"
                                class="
                                    border-t
                                    border-[var(--theme-border)]

                                    hover:bg-[var(--theme-primary-soft-subtle)]

                                    transition-colors
                                "
                            >

                                {{-- Usuario --}}
                                <td class="px-4 py-3">

                                    <p
                                        class="
                                            font-medium
                                            text-[var(--theme-text-strong)]
                                        "
                                    >
                                        {{ $solicitud->usuario_nombre }}
                                    </p>

                                    <p
                                        class="
                                            mt-0.5
                                            text-[10px]
                                            text-[var(--theme-text-muted)]
                                        "
                                    >
                                        {{ '@' . $solicitud->username }}
                                    </p>

                                </td>


                                {{-- Campo --}}
                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $campoLabel }}
                                </td>


                                {{-- Actual --}}
                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ $solicitud->valor_actual ?: 'Sin asignar' }}
                                </td>


                                {{-- Solicitado --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ $solicitud->valor_solicitado }}
                                </td>


                                {{-- Fecha --}}
                                <td
                                    class="
                                        px-4
                                        py-3

                                        whitespace-nowrap
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ \Illuminate\Support\Carbon::parse(
                                        $solicitud->fecha_solicitud
                                    )->format('d/m/Y H:i') }}
                                </td>


                                {{-- Acción --}}
                                <td class="px-4 py-3 text-right">

                                    <button
                                        type="button"

                                        data-org-request-id="{{ $solicitud->id_solicitud }}"
                                        data-org-user="{{ $solicitud->usuario_nombre }}"
                                        data-org-field="{{ $solicitud->campo }}"
                                        data-org-label="{{ $campoLabel }}"
                                        data-org-current="{{ $solicitud->valor_actual ?? '' }}"
                                        data-org-requested="{{ $solicitud->valor_solicitado ?? '' }}"
                                        data-org-reason="{{ $solicitud->motivo ?? '' }}"

                                        onclick="window.openOrganizationRequestModalFromButton(this)"

                                        class="
                                            h-7
                                            px-2.5

                                            rounded-md

                                            bg-[var(--theme-primary-soft)]
                                            text-[var(--theme-primary)]

                                            text-[10px]
                                            font-medium

                                            hover:opacity-80

                                            disabled:opacity-50
                                            disabled:cursor-not-allowed

                                            transition
                                        "
                                    >
                                        Revisar
                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="
                                        px-5
                                        py-10

                                        text-center
                                        text-sm
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    No hay solicitudes de actualización pendientes.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =================================================
                TARJETAS MÓVIL
            ================================================= --}}
            <div
                class="
                    lg:hidden

                    divide-y
                    divide-[var(--theme-border)]
                "
            >

                @forelse ($solicitudesCambio as $solicitud)

                    @php
                        $campoLabel = match ($solicitud->campo) {
                            'numero_colaborador' => 'Número de colaborador',
                            'puesto' => 'Puesto',
                            'rol' => 'Rol',
                            'area' => 'Área',
                            'departamento' => 'Departamento',
                            default => ucfirst(
                                str_replace('_', ' ', $solicitud->campo)
                            ),
                        };
                    @endphp


                    <article
                        wire:key="solicitud-mobile-{{ $solicitud->id_solicitud }}"
                        class="p-4"
                    >

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
                                    {{ $solicitud->usuario_nombre }}
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    {{ $campoLabel }}
                                </p>

                            </div>


                            <span
                                class="
                                    shrink-0

                                    px-2
                                    py-1

                                    rounded-full

                                    bg-[var(--theme-warning-soft)]
                                    text-[var(--theme-warning)]

                                    text-[10px]
                                    font-medium
                                "
                            >
                                Pendiente
                            </span>

                        </div>


                        <div
                            class="
                                grid
                                grid-cols-2

                                gap-3

                                mt-4
                            "
                        >

                            <div>

                                <p
                                    class="
                                        text-[10px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Actual
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        text-[var(--theme-text)]
                                    "
                                >
                                    {{ $solicitud->valor_actual ?: 'Sin asignar' }}
                                </p>

                            </div>


                            <div>

                                <p
                                    class="
                                        text-[10px]
                                        text-[var(--theme-text-muted)]
                                    "
                                >
                                    Solicitado
                                </p>

                                <p
                                    class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        text-[var(--theme-text-strong)]
                                    "
                                >
                                    {{ $solicitud->valor_solicitado }}
                                </p>

                            </div>

                        </div>


                        <div
                            class="
                                mt-4
                                pt-3

                                border-t
                                border-[var(--theme-border)]

                                flex
                                items-center
                                justify-between
                                gap-3
                            "
                        >

                            <span
                                class="
                                    text-[10px]
                                    text-[var(--theme-text-muted)]
                                "
                            >
                                {{ \Illuminate\Support\Carbon::parse(
                                    $solicitud->fecha_solicitud
                                )->format('d/m/Y H:i') }}
                            </span>


                            <button
                                type="button"

                                data-org-request-id="{{ $solicitud->id_solicitud }}"
                                data-org-user="{{ $solicitud->usuario_nombre }}"
                                data-org-field="{{ $solicitud->campo }}"
                                data-org-label="{{ $campoLabel }}"
                                data-org-current="{{ $solicitud->valor_actual ?? '' }}"
                                data-org-requested="{{ $solicitud->valor_solicitado ?? '' }}"
                                data-org-reason="{{ $solicitud->motivo ?? '' }}"

                                onclick="window.openOrganizationRequestModalFromButton(this)"

                                class="
                                    h-8
                                    px-3

                                    rounded-md

                                    bg-[var(--theme-primary-soft)]
                                    text-[var(--theme-primary)]

                                    text-xs
                                    font-medium

                                    hover:opacity-80

                                    transition
                                "
                            >
                                Revisar
                            </button>

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
                        No hay solicitudes de actualización pendientes.
                    </div>

                @endforelse

            </div>

        </section>
