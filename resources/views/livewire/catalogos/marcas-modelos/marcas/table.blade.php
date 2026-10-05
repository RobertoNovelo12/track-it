{{-- ============================================================
    TABLA - MARCAS
============================================================ --}}
<div class="hidden md:block overflow-x-auto">

    <table
        class="
            w-full
            min-w-[1160px]

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
                            paginatedMarcas.length > 0
                            &&
                            paginatedMarcas.every(
                                marca =>
                                    selectedMarcaIds.includes(
                                        String(
                                            marca.id
                                        )
                                    )
                            )
                        "

                        @change="
                            toggleCurrentMarcaSelection(
                                $event.target.checked
                            )
                        "

                        class="
                            rounded
                            border-[var(--theme-border-strong)]
                        "
                    >

                </th>


                {{-- ID MARCA --}}
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
                            sortMarcasBy(
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
                            ID Marca
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortMarcaField === 'id'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'id'
                                && sortMarcas === 'desc'
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
                            sortMarcasBy(
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
                            Marca
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortMarcaField === 'nombre'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'nombre'
                                && sortMarcas === 'desc'
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
                        w-[320px]

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
                            sortMarcasBy(
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
                                sortMarcaField === 'activo'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'activo'
                                && sortMarcas === 'desc'
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


                {{-- MODELOS --}}
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
                            sortMarcasBy(
                                'totalModelos'
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
                            Modelos
                        </span>

                        <svg
                            class="
                                w-3
                                h-3

                                transition-all
                            "

                            :class="[
                                sortMarcaField === 'totalModelos'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'totalModelos'
                                && sortMarcas === 'desc'
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
                            sortMarcasBy(
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
                                sortMarcaField === 'totalEquipos'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'totalEquipos'
                                && sortMarcas === 'desc'
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
                            sortMarcasBy(
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
                                sortMarcaField === 'fechaCreacion'
                                    ? 'opacity-100 text-[var(--theme-primary)]'
                                    : 'opacity-25',

                                sortMarcaField === 'fechaCreacion'
                                && sortMarcas === 'desc'
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
                    marca
                    in paginatedMarcas
                "

                :key="
                    'marca-'
                    + marca.id
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
                                selectedMarcaIds
                            "

                            :value="
                                String(
                                    marca.id
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
                            marca.id
                        "
                    ></td>


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
                                marca.nombre
                            "

                            x-text="
                                marca.nombre
                            "
                        ></span>
                    </td>


                    {{-- DESCRIPCIÓN --}}
                    <td
                        class="
                            px-3
                            py-3

                            max-w-[340px]

                            text-[var(--theme-text)]
                        "
                    >
                        <span
                            class="
                                block
                                truncate
                            "

                            :title="
                                marca.descripcion
                                || 'Sin descripción'
                            "

                            x-text="
                                marca.descripcion
                                || 'Sin descripción'
                            "
                        ></span>
                    </td>


                    {{-- ESTADO --}}
                    <td
                        class="
                            px-3
                            py-3
                        "
                    >
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
                                marca.activo
                                    ? 'bg-[var(--theme-success-soft)] text-[var(--theme-success)]'
                                    : 'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]'
                            "

                            x-text="
                                marca.activo
                                    ? 'Activa'
                                    : 'Inactiva'
                            "
                        ></span>

                    </td>


                    {{-- MODELOS --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "

                        x-text="
                            marca.totalModelos
                        "
                    ></td>


                    {{-- EQUIPOS --}}
                    <td
                        class="
                            px-3
                            py-3

                            text-[var(--theme-text)]
                        "

                        x-text="
                            marca.totalEquipos
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
                                marca.fechaCreacion
                            )
                        "
                    ></td>


                    {{-- ACCIONES --}}
                    <td
                        class="
                            px-3
                            py-3
                        "
                    >
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
                                    marca.editUrl
                                "

                                class="
                                    text-[var(--theme-primary)]

                                    hover:text-[var(--theme-primary-hover)]

                                    transition-colors
                                "

                                title="Editar marca"
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
                                            togglingMarcaId
                                            === marca.id
                                        "

                                        @click="
                                            open = false;

                                            toggleMarcaStatus(
                                                marca
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
                                            marca.activo
                                                ? 'text-[var(--theme-danger)] hover:bg-[var(--theme-danger-soft)]'
                                                : 'text-[var(--theme-success)] hover:bg-[var(--theme-success-soft)]'
                                        "
                                    >
                                        <span
                                            x-text="
                                                togglingMarcaId
                                                === marca.id
                                                    ? 'Actualizando...'
                                                    : (
                                                        marca.activo
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
                    filteredMarcas.length
                    === 0
                "
            >
                <td
                    colspan="9"

                    class="
                        px-5
                        py-12

                        text-center

                        text-sm
                        text-[var(--theme-text-muted)]
                    "
                >
                    No se encontraron marcas con los filtros seleccionados.
                </td>
            </tr>

        </tbody>

    </table>

</div>