{{-- ============================================================
    TABLA - MODELOS
============================================================ --}}
<div class="hidden md:block overflow-x-auto">

    <table
        class="
            w-full
            min-w-[1360px]

            table-fixed

            text-sm
        "
    >

        <thead>

            <tr
                class="
                    border-b
                    border-[var(--theme-border)]

                    text-left
                "
            >

                {{-- CHECKBOX --}}
                <th class="w-12 px-5 py-3">

                    <input
                        type="checkbox"

                        :checked="
                            paginatedModelos.length > 0
                            &&
                            paginatedModelos.every(
                                modelo =>
                                    selectedModeloIds.includes(
                                        String(
                                            modelo.id
                                        )
                                    )
                            )
                        "

                        @change="
                            toggleCurrentModeloSelection(
                                $event.target.checked
                            )
                        "

                        class="
                            rounded
                            border-[var(--theme-border-strong)]
                        "
                    >

                </th>


                {{-- ID MODELO --}}
                <th
                    class="
                        w-24

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]

                        whitespace-nowrap
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'id'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            ID Modelo
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'id'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'id'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- MODELO --}}
                <th
                    class="
                        w-44

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'nombre'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Modelo
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'nombre'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'nombre'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- MARCA --}}
                <th
                    class="
                        w-44

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'marcaNombre'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Marca
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'marcaNombre'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'marcaNombre'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- TIPO --}}
                <th
                    class="
                        w-40

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'tipoEquipoNombre'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Tipo
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'tipoEquipoNombre'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'tipoEquipoNombre'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- DESCRIPCIÓN - SIN ORDENAMIENTO --}}
                <th
                    class="
                        w-[280px]

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    Descripción
                </th>


                {{-- ESTADO --}}
                <th
                    class="
                        w-28

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'activo'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Estado
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'activo'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'activo'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- EQUIPOS --}}
                <th
                    class="
                        w-24

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]

                        whitespace-nowrap
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'totalEquipos'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Equipos
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'totalEquipos'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'totalEquipos'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- REGISTRO --}}
                <th
                    class="
                        w-32

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]

                        whitespace-nowrap
                    "
                >
                    <button
                        type="button"

                        @click="
                            sortModelosBy(
                                'fechaCreacion'
                            )
                        "

                        class="
                            inline-flex
                            items-center
                            gap-1

                            hover:text-[var(--theme-text-strong)]

                            transition-colors
                        "
                    >
                        <span>
                            Registro
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortModeloField === 'fechaCreacion'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortModeloField === 'fechaCreacion'
                                && sortModelos === 'desc'
                                    ? 'rotate-180'
                                    : ''
                            ]"

                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M6 15l6-6 6 6"/>
                        </svg>
                    </button>
                </th>


                {{-- ACCIONES --}}
                <th
                    class="
                        w-24

                        px-3
                        py-3

                        font-medium
                        text-[var(--theme-text-muted)]
                        text-right
                    "
                >
                    Acciones
                </th>

            </tr>

        </thead>


        <tbody>

            <template
                x-for="
                    modelo
                    in paginatedModelos
                "

                :key="
                    'modelo-'
                    + modelo.id
                "
            >
                <tr
                    class="
                        h-14

                        border-b
                        border-[var(--theme-border)]

                        align-middle

                        hover:bg-[var(--theme-primary-soft-subtle)]

                        transition-colors
                    "
                >

                    {{-- CHECKBOX --}}
                    <td class="px-5 py-3">

                        <input
                            type="checkbox"

                            x-model="
                                selectedModeloIds
                            "

                            :value="
                                String(
                                    modelo.id
                                )
                            "

                            class="
                                rounded
                                border-[var(--theme-border-strong)]
                            "
                        >

                    </td>


                    {{-- ID --}}
                    <td
                        class="
                            px-3
                            py-3

                            font-medium
                            text-[var(--theme-text)]

                            whitespace-nowrap
                        "

                        x-text="
                            modelo.id
                        "
                    ></td>


                    {{-- MODELO --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "
                    >
                        <span
                            class="
                                block
                                truncate
                            "

                            :title="
                                modelo.nombre
                            "

                            x-text="
                                modelo.nombre
                            "
                        ></span>
                    </td>


                    {{-- MARCA --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "
                    >
                        <span
                            class="
                                block
                                truncate
                            "

                            :title="
                                modelo.marcaNombre
                            "

                            x-text="
                                modelo.marcaNombre
                            "
                        ></span>
                    </td>


                    {{-- TIPO --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "
                    >
                        <span
                            class="
                                block
                                truncate
                            "

                            :title="
                                modelo.tipoEquipoNombre
                            "

                            x-text="
                                modelo.tipoEquipoNombre
                            "
                        ></span>
                    </td>


                    {{-- DESCRIPCIÓN --}}
                    <td
                        class="
                            px-3
                            py-3

                            max-w-[300px]

                            text-[var(--theme-text)]
                        "
                    >
                        <span
                            class="
                                block
                                truncate
                            "

                            :title="
                                modelo.descripcion
                                || 'Sin descripción'
                            "

                            x-text="
                                modelo.descripcion
                                || 'Sin descripción'
                            "
                        ></span>
                    </td>


                    {{-- ESTADO --}}
                    <td class="px-3 py-3">

                        <span
                            class="
                                inline-flex
                                items-center

                                px-2
                                py-1

                                rounded-full

                                text-[10px]
                                font-medium
                            "

                            :class="
                                modelo.activo
                                    ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                    : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                            "

                            x-text="
                                modelo.activo
                                    ? 'Activo'
                                    : 'Inactivo'
                            "
                        ></span>

                    </td>


                    {{-- EQUIPOS --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "

                        x-text="
                            modelo.totalEquipos
                        "
                    ></td>


                    {{-- REGISTRO --}}
                    <td
                        class="
                            px-3
                            py-3

                            whitespace-nowrap

                            text-[var(--theme-text-muted)]
                        "

                        x-text="
                            formatDate(
                                modelo.fechaCreacion
                            )
                        "
                    ></td>


                    {{-- ACCIONES --}}
                    <td class="px-3 py-3">

                        <div
                            class="
                                flex
                                items-center
                                justify-end

                                gap-2
                            "
                        >

                            {{-- EDITAR --}}
                            <a
                                :href="
                                    modelo.editUrl
                                "

                                class="
                                    text-[var(--theme-primary)]

                                    hover:text-[var(--theme-primary-hover)]

                                    transition-colors
                                "

                                title="Editar modelo"
                            >
                                <svg
                                    class="w-4 h-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path d="M4 20h4l11-11-4-4L4 16v4z"/>
                                    <path d="M13.5 6.5l4 4"/>
                                </svg>
                            </a>


                            {{-- MÁS ACCIONES --}}
                            <div
                                x-data="
                                    {
                                        open: false
                                    }
                                "

                                @click.outside="
                                    open = false
                                "

                                class="relative"
                            >
                                <button
                                    type="button"

                                    @click="
                                        open = !open
                                    "

                                    class="
                                        text-[var(--theme-text-muted)]

                                        hover:text-[var(--theme-text-strong)]

                                        transition-colors
                                    "

                                    title="Más acciones"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <circle cx="12" cy="5" r="1.5"/>
                                        <circle cx="12" cy="12" r="1.5"/>
                                        <circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>


                                <div
                                    x-show="
                                        open
                                    "

                                    x-cloak

                                    x-transition.opacity.duration.100ms

                                    class="
                                        absolute

                                        right-0
                                        top-6

                                        w-40

                                        bg-[var(--theme-surface)]

                                        border
                                        border-[var(--theme-border)]

                                        rounded-md

                                        shadow-md

                                        z-20

                                        text-left

                                        overflow-hidden
                                    "
                                >
                                    <button
                                        type="button"

                                        :disabled="
                                            togglingModeloId
                                            === modelo.id
                                        "

                                        @click="
                                            open = false;

                                            toggleModeloStatus(
                                                modelo
                                            );
                                        "

                                        class="
                                            w-full

                                            block

                                            text-left

                                            px-4
                                            py-2

                                            text-xs

                                            disabled:opacity-50
                                        "

                                        :class="
                                            modelo.activo
                                                ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                                : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                        "
                                    >
                                        <span
                                            x-text="
                                                togglingModeloId
                                                === modelo.id
                                                    ? 'Actualizando...'
                                                    : (
                                                        modelo.activo
                                                            ? 'Desactivar'
                                                            : 'Activar'
                                                    )
                                            "
                                        ></span>
                                    </button>

                                </div>
                            </div>

                        </div>

                    </td>

                </tr>
            </template>


            {{-- SIN RESULTADOS --}}
            <tr
                x-show="
                    filteredModelos.length
                    === 0
                "
            >
                <td
                    colspan="10"

                    class="
                        px-5
                        py-12

                        text-center

                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >
                    No se encontraron modelos con los filtros seleccionados.
                </td>
            </tr>

        </tbody>

    </table>

</div>