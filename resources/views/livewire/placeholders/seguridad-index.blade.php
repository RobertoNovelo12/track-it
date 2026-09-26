<div>

    {{-- ============================================================
        INDICADORES
        La estructura, títulos, colores e iconos ya son conocidos.
        Solo los valores numéricos usan skeleton.
    ============================================================ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        {{-- Nuevos usuarios --}}
        <div class="bg-[var(--theme-surface)] border border-[var(--theme-primary-border)] rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-lg flex items-center justify-center bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                    <path d="M18 7v6"/>
                    <path d="M15 10h6"/>
                </svg>
            </div>

            <div class="min-w-0">
                <p class="text-xs text-[var(--theme-primary)]">
                    Nuevos usuarios
                </p>

                <div class="my-1 animate-pulse">
                    <div class="h-7 w-12 rounded bg-[var(--theme-primary-soft)]"></div>
                </div>

                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Registrados este mes
                </p>
            </div>
        </div>


        {{-- Usuarios activos --}}
        <div class="bg-[var(--theme-surface)] border border-[var(--theme-success-border)] rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-lg flex items-center justify-center bg-[var(--theme-success-soft)] text-[var(--theme-success)]">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                    <path d="M16 11l2 2 4-5"/>
                </svg>
            </div>

            <div class="min-w-0">
                <p class="text-xs text-[var(--theme-success)]">
                    Usuarios activos
                </p>

                <div class="my-1 animate-pulse">
                    <div class="h-7 w-12 rounded bg-[var(--theme-success-soft)]"></div>
                </div>

                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Cuentas habilitadas
                </p>
            </div>
        </div>


        {{-- Pendientes --}}
        <div class="bg-[var(--theme-surface)] border border-[var(--theme-warning-border)] rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-lg flex items-center justify-center bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                    <circle cx="18" cy="10" r="3"/>
                    <path d="M18 8.5V10l1 1"/>
                </svg>
            </div>

            <div class="min-w-0">
                <p class="text-xs text-[var(--theme-warning)]">
                    Pendientes
                </p>

                <div class="my-1 animate-pulse">
                    <div class="h-7 w-12 rounded bg-[var(--theme-warning-soft)]"></div>
                </div>

                <p class="text-[11px] text-[var(--theme-text-muted)]">
                    Requieren aprobación
                </p>
            </div>
        </div>


        {{-- Roles --}}
        <div class="bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] rounded-lg p-4 flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-lg flex items-center justify-center bg-[var(--theme-surface-soft)] text-[var(--theme-text-strong)]">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                    <circle cx="12" cy="10" r="2"/>
                    <path d="M9 16c.5-2 1.5-3 3-3s2.5 1 3 3"/>
                </svg>
            </div>

            <div class="min-w-0">
                <p class="text-xs text-[var(--theme-text)]">
                    Roles configurados
                </p>

                <div class="my-1 animate-pulse">
                    <div class="h-7 w-12 rounded bg-[var(--theme-surface-soft)]"></div>
                </div>

                <div class="flex items-center gap-1.5 text-[11px] text-[var(--theme-text-muted)]">
                    <div class="animate-pulse">
                        <div class="h-2.5 w-6 rounded bg-[var(--theme-surface-soft)]"></div>
                    </div>

                    <span>usuarios totales</span>
                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
        CONTENIDO PRINCIPAL
    ============================================================ --}}
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-5">

        {{-- ========================================================
            COLUMNA IZQUIERDA
        ======================================================== --}}
        <div class="space-y-5">

            {{-- ====================================================
                GESTIÓN DE USUARIOS
            ==================================================== --}}
            <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-lg overflow-hidden">

                {{-- Cabecera --}}
                <div class="px-4 sm:px-5 py-4 border-b border-[var(--theme-border)] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                            Gestión de usuarios
                        </h2>

                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                            Consulta y administra las cuentas registradas.
                        </p>
                    </div>


                    {{-- Filtros conocidos --}}
                    <div class="flex flex-col sm:flex-row gap-2">

                        {{-- Buscar --}}
                        <div class="relative w-full sm:w-64">
                            <svg
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--theme-text-muted)] pointer-events-none"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <circle cx="11" cy="11" r="7"/>
                                <path d="M20 20l-4-4"/>
                            </svg>

                            <input
                                type="search"
                                disabled
                                placeholder="Buscar usuario..."
                                class="w-full h-9 pl-9 pr-3 rounded-md bg-[var(--theme-surface-soft)] border border-[var(--theme-border)] text-xs text-[var(--theme-text)] placeholder:text-[var(--theme-text-muted)] disabled:cursor-default disabled:opacity-100"
                            >
                        </div>


                        {{-- Estado --}}
                        <div class="relative">
                            <select
                                disabled
                                class="w-full sm:w-auto h-9 px-3 pr-8 rounded-md bg-[var(--theme-surface)] border border-[var(--theme-border-strong)] text-xs text-[var(--theme-text)] disabled:cursor-default disabled:opacity-100"
                            >
                                <option>
                                    Todos los estados
                                </option>
                            </select>
                        </div>

                    </div>
                </div>


                {{-- =================================================
                    TABLA USUARIOS - ESCRITORIO
                ================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-xs">

                        <thead class="bg-[var(--theme-surface-soft)]">
                            <tr class="text-left text-[var(--theme-text-muted)]">
                                <th class="px-4 py-3 font-medium">
                                    Usuario
                                </th>

                                <th class="px-4 py-3 font-medium">
                                    Correo
                                </th>

                                <th class="px-4 py-3 font-medium">
                                    Área / Departamento
                                </th>

                                <th class="px-4 py-3 font-medium">
                                    Rol
                                </th>

                                <th class="px-4 py-3 font-medium">
                                    Estado
                                </th>

                                <th class="px-4 py-3 font-medium">
                                    Registro
                                </th>

                                <th class="px-4 py-3 font-medium text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>


                        <tbody class="animate-pulse">
                            @for ($fila = 0; $fila < 6; $fila++)
                                <tr class="border-t border-[var(--theme-border)]">

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 shrink-0 rounded-full bg-[var(--theme-surface-soft)]"></div>

                                            <div>
                                                <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                                                <div class="mt-1.5 h-2 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-5 w-16 rounded-full bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="h-7 w-14 rounded-md bg-[var(--theme-surface-soft)]"></div>
                                            <div class="w-7 h-7 rounded-md bg-[var(--theme-surface-soft)]"></div>
                                        </div>
                                    </td>

                                </tr>
                            @endfor
                        </tbody>

                    </table>
                </div>


                {{-- =================================================
                    USUARIOS - MÓVIL
                ================================================= --}}
                <div class="lg:hidden divide-y divide-[var(--theme-border)] animate-pulse">

                    @for ($i = 0; $i < 4; $i++)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 shrink-0 rounded-full bg-[var(--theme-surface-soft)]"></div>

                                <div class="min-w-0 flex-1">
                                    <div class="h-3.5 w-32 max-w-full rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="mt-2 h-2.5 w-40 max-w-full rounded bg-[var(--theme-surface-soft)]"></div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <div class="h-5 w-16 rounded-full bg-[var(--theme-surface-soft)]"></div>
                                        <div class="h-2.5 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </div>
                                </div>

                                <div class="w-7 h-7 shrink-0 rounded-md bg-[var(--theme-surface-soft)]"></div>
                            </div>
                        </div>
                    @endfor

                </div>

            </section>


            {{-- ====================================================
                SOLICITUDES DE ACTUALIZACIÓN
            ==================================================== --}}
            <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-lg overflow-hidden">

                {{-- Cabecera conocida --}}
                <div class="px-4 sm:px-5 py-4 border-b border-[var(--theme-border)] flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-[var(--theme-text-strong)]">
                            Solicitudes de actualización
                        </h2>

                        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                            Revisa los cambios organizacionales solicitados por los usuarios.
                        </p>
                    </div>

                    {{-- La cantidad sí es desconocida --}}
                    <div class="shrink-0 animate-pulse">
                        <div class="h-6 w-20 rounded-full bg-[var(--theme-warning-soft)]"></div>
                    </div>
                </div>


                {{-- =================================================
                    TABLA SOLICITUDES - ESCRITORIO
                ================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-xs">

                        <thead class="bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
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


                        <tbody class="animate-pulse">
                            @for ($i = 0; $i < 3; $i++)
                                <tr class="border-t border-[var(--theme-border)]">

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                                        <div class="mt-1.5 h-2 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="ml-auto h-7 w-14 rounded-md bg-[var(--theme-primary-soft)]"></div>
                                    </td>

                                </tr>
                            @endfor
                        </tbody>

                    </table>
                </div>


                {{-- =================================================
                    SOLICITUDES - MÓVIL
                ================================================= --}}
                <div class="lg:hidden divide-y divide-[var(--theme-border)] animate-pulse">

                    @for ($i = 0; $i < 2; $i++)
                        <article class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="h-3.5 w-32 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="mt-2 h-2.5 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>

                                <div class="h-7 w-14 rounded-md bg-[var(--theme-primary-soft)]"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-4">
                                <div>
                                    <div class="h-2 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="mt-2 h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>

                                <div>
                                    <div class="h-2 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                    <div class="mt-2 h-3 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                                </div>
                            </div>
                        </article>
                    @endfor

                </div>

            </section>


            {{-- ====================================================
                BITÁCORA DE AUDITORÍA
            ==================================================== --}}
            <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-lg overflow-hidden">

                {{-- Título conocido --}}
                <div class="px-5 py-4 border-b border-[var(--theme-border)]">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-[var(--theme-text-strong)]">
                        <svg
                            class="w-5 h-5 text-[var(--theme-primary)]"
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


                {{-- =================================================
                    BITÁCORA ESCRITORIO
                ================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-xs">

                        <thead class="bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]">
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


                        <tbody class="animate-pulse">
                            @for ($i = 0; $i < 4; $i++)
                                <tr class="border-t border-[var(--theme-border)]">
                                    <td class="px-4 py-3">
                                        <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-24 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-5 w-24 rounded-md bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-16 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="h-3 w-full max-w-56 rounded bg-[var(--theme-surface-soft)]"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>

                    </table>
                </div>


                {{-- =================================================
                    BITÁCORA MÓVIL
                ================================================= --}}
                <div class="lg:hidden divide-y divide-[var(--theme-border)] animate-pulse">

                    @for ($i = 0; $i < 4; $i++)
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="h-5 w-24 rounded-md bg-[var(--theme-surface-soft)]"></div>
                                <div class="h-2.5 w-20 rounded bg-[var(--theme-surface-soft)]"></div>
                            </div>

                            <div class="mt-3 h-3 w-28 rounded bg-[var(--theme-surface-soft)]"></div>
                            <div class="mt-2 h-2.5 w-full max-w-72 rounded bg-[var(--theme-surface-soft)]"></div>
                        </div>
                    @endfor

                </div>

            </section>

        </div>


        {{-- ========================================================
            COLUMNA DERECHA
        ======================================================== --}}
        <aside class="space-y-5">

            {{-- ====================================================
                RESUMEN DE LA SEMANA
            ==================================================== --}}
            <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-lg p-4">

                {{-- Título conocido --}}
                <h2 class="flex items-center gap-2 text-sm font-semibold text-[var(--theme-text-strong)]">
                    <svg
                        class="w-5 h-5 text-[var(--theme-primary)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.6"
                    >
                        <path d="M4 20V10"/>
                        <path d="M10 20V4"/>
                        <path d="M16 20v-7"/>
                        <path d="M22 20V7"/>
                    </svg>

                    Resumen de la semana
                </h2>


                {{-- Solo las alturas de las barras dependen de los datos --}}
                <div class="mt-6 h-44 flex items-end justify-between gap-2 animate-pulse">

                    @foreach ([45, 70, 35, 85, 60, 50, 75] as $altura)
                        <div class="flex-1 h-full flex flex-col justify-end items-center">

                            <div class="flex items-end justify-center gap-1 w-full h-full">
                                <div
                                    class="w-2.5 max-w-full rounded-t bg-[var(--theme-primary-soft)]"
                                    style="height: {{ $altura }}%"
                                ></div>

                                <div
                                    class="w-2.5 max-w-full rounded-t bg-[var(--theme-success-soft)]"
                                    style="height: {{ max(20, $altura - 20) }}%"
                                ></div>
                            </div>

                            <div class="mt-2 h-2 w-5 rounded bg-[var(--theme-surface-soft)]"></div>

                        </div>
                    @endforeach

                </div>


                {{-- Leyenda conocida --}}
                <div class="mt-4 flex flex-wrap items-center gap-4 text-[10px] text-[var(--theme-text-muted)]">

                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[var(--theme-primary)]"></span>
                        Actividad
                    </span>

                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[var(--theme-success)]"></span>
                        Nuevos usuarios
                    </span>

                </div>

            </section>


            {{-- ====================================================
                ALERTAS Y SEGURIDAD
            ==================================================== --}}
            <section class="bg-[var(--theme-surface)] border border-[var(--theme-border)] rounded-lg p-4">

                {{-- Título conocido --}}
                <h2 class="flex items-center gap-2 text-sm font-semibold text-[var(--theme-text-strong)]">
                    <svg
                        class="w-5 h-5 text-[var(--theme-primary)]"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                        <path d="M10 21h4"/>
                    </svg>

                    Alertas y seguridad
                </h2>


                {{-- El contenido sí depende del estado actual --}}
                <div class="mt-4 space-y-2 animate-pulse">

                    @for ($i = 0; $i < 3; $i++)
                        <div
                            class="
                                flex
                                items-center
                                gap-3

                                p-3

                                rounded-lg

                                border
                                border-[var(--theme-border)]

                                bg-[var(--theme-surface)]
                            "
                        >
                            <div class="w-8 h-8 shrink-0 rounded-full bg-[var(--theme-surface-soft)]"></div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="
                                        h-3
                                        rounded
                                        bg-[var(--theme-surface-soft)]

                                        {{ $i === 0
                                            ? 'w-full'
                                            : ($i === 1 ? 'w-4/5' : 'w-3/4')
                                        }}
                                    "
                                ></div>
                            </div>
                        </div>
                    @endfor

                </div>

            </section>

        </aside>

    </div>

</div>