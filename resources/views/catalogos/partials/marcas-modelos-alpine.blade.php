<script>
window.brandsModelsCatalog = (data) => ({
                            data: data ?? {},
        
                            search:
                                String(
                                    data?.defaults?.search
                                    ?? ''
                                ),
        
                            estado:
                                String(
                                    data?.defaults?.estado
                                    ?? 'todos'
                                ),
        
                            tipo:
                                String(
                                    data?.defaults?.tipo
                                    ?? 'todos'
                                ),
        
                            perPageMarcas:
                                String(
                                    data?.defaults?.perPageMarcas
                                    ?? '10'
                                ),
        
                            perPageModelos:
                                String(
                                    data?.defaults?.perPageModelos
                                    ?? '10'
                                ),
        
                            sortMarcas:
                                String(
                                    data?.defaults?.sortMarcas
                                    ?? 'asc'
                                ),
        
                            sortModelos:
                                String(
                                    data?.defaults?.sortModelos
                                    ?? 'asc'
                                ),

                            sortMarcaField: 'nombre',
                            sortModeloField: 'marcaNombre',
        
                            marcaPage: 1,
                            modeloPage: 1,
        
                            selectedMarcaIds: [],
                            selectedModeloIds: [],
        
                            togglingMarcaId: null,
                            togglingModeloId: null,
        
                            toastOpen: false,
                            toastMessage: '',
                            toastTimer: null,
        
                            filteredMarcasCache: [],
                            filteredModelosCache: [],
        
                            searchTimer: null,
        
        
                            init() {
                                this.prepareSearchIndexes();
        
                                this.refreshMarcas();
                                this.refreshModelos();
        
                                this.$watch(
                                    'search',
                                    () => {
                                        this.marcaPage = 1;
                                        this.modeloPage = 1;
        
                                        if (this.searchTimer) {
                                            clearTimeout(
                                                this.searchTimer
                                            );
                                        }
        
                                        this.searchTimer =
                                            setTimeout(
                                                () => {
                                                    this.refreshMarcas();
                                                    this.refreshModelos();
                                                },
                                                120
                                            );
                                    }
                                );
        
                                this.$watch(
                                    'estado',
                                    () => {
                                        this.marcaPage = 1;
                                        this.modeloPage = 1;
        
                                        this.refreshMarcas();
                                        this.refreshModelos();
                                    }
                                );
        
                                this.$watch(
                                    'tipo',
                                    () => {
                                        this.modeloPage = 1;
        
                                        this.refreshModelos();
                                    }
                                );
        
                                this.$watch(
                                    'perPageMarcas',
                                    () => {
                                        this.marcaPage = 1;
                                    }
                                );
        
                                this.$watch(
                                    'perPageModelos',
                                    () => {
                                        this.modeloPage = 1;
                                    }
                                );
        
                                this.$watch(
                                    'sortMarcas',
                                    () => {
                                        this.marcaPage = 1;
        
                                        this.refreshMarcas();
                                    }
                                );
        
                                this.$watch(
                                    'sortModelos',
                                    () => {
                                        this.modeloPage = 1;
        
                                        this.refreshModelos();
                                    }
                                );
                            },
        
        
                            get estadoOptions() {
                                return (
                                    this.data?.filters?.estados
                                    ?? []
                                );
                            },
        
        
                            get tipoOptions() {
                                return [
                                    {
                                        value: 'todos',
                                        label: 'Todos los tipos',
                                    },
        
                                    ...(
                                        this.data?.tiposEquipo
                                        ?? []
                                    ),
                                ];
                            },
        
        
                            get perPageOptions() {
                                return (
                                    this.data?.filters?.perPage
                                    ?? []
                                );
                            },
        
        
                            get sortOptions() {
                                return (
                                    this.data?.filters?.sort
                                    ?? []
                                );
                            },
        
        
                            get totalMarcas() {
                                return Number(
                                    this.data?.metrics?.totalMarcas
                                    ?? (
                                        this.data?.marcas
                                        ?? []
                                    ).length
                                );
                            },
        
        
                            get totalModelos() {
                                return Number(
                                    this.data?.metrics?.totalModelos
                                    ?? (
                                        this.data?.modelos
                                        ?? []
                                    ).length
                                );
                            },
        
        
                            get marcasInactivas() {
                                return Number(
                                    this.data?.metrics?.marcasInactivas
                                    ?? 0
                                );
                            },
        
        
                            get modelosEnUso() {
                                return Number(
                                    this.data?.metrics?.modelosEnUso
                                    ?? 0
                                );
                            },
        
        
                            normalize(value) {
                                return String(
                                    value ?? ''
                                )
                                    .normalize('NFD')
                                    .replace(
                                        /[\u0300-\u036f]/g,
                                        ''
                                    )
                                    .toLowerCase()
                                    .trim();
                            },
        
        
                            prepareSearchIndexes() {
                                const marcas =
                                    this.data?.marcas
                                    ?? [];
        
                                marcas.forEach(
                                    marca => {
                                        marca._search =
                                            this.normalize(
                                                [
                                                    marca.nombre,
                                                    marca.descripcion,
                                                ].join(' ')
                                            );
                                    }
                                );
        
                                const modelos =
                                    this.data?.modelos
                                    ?? [];
        
                                modelos.forEach(
                                    modelo => {
                                        modelo._search =
                                            this.normalize(
                                                [
                                                    modelo.nombre,
                                                    modelo.descripcion,
                                                    modelo.marcaNombre,
                                                    modelo.tipoEquipoNombre,
                                                ].join(' ')
                                            );
                                    }
                                );
                            },
        
        
                            matchesEstado(active) {
                                if (
                                    this.estado
                                    === 'activos'
                                ) {
                                    return Boolean(
                                        active
                                    );
                                }
        
                                if (
                                    this.estado
                                    === 'inactivos'
                                ) {
                                    return !Boolean(
                                        active
                                    );
                                }
        
                                return true;
                            },
        
        
                            compareText(a, b, direction) {
                                const result =
                                    String(
                                        a ?? ''
                                    ).localeCompare(
                                        String(
                                            b ?? ''
                                        ),
                                        'es',
                                        {
                                            sensitivity:
                                                'base',
                                        }
                                    );
        
                                return direction
                                    === 'desc'
                                        ? -result
                                        : result;
                            },

                            compareNumber(a, b, direction) {
                                const result =
                                    Number(a ?? 0)
                                    -
                                    Number(b ?? 0);

                                return direction === 'desc'
                                    ? -result
                                    : result;
                            },


                            compareDate(a, b, direction) {
                                const aTime =
                                    a
                                        ? new Date(a).getTime()
                                        : 0;

                                const bTime =
                                    b
                                        ? new Date(b).getTime()
                                        : 0;

                                const result =
                                    aTime - bTime;

                                return direction === 'desc'
                                    ? -result
                                    : result;
                            },


                            sortMarcasBy(field) {
                                if (
                                    this.sortMarcaField
                                    === field
                                ) {
                                    this.sortMarcas =
                                        this.sortMarcas === 'asc'
                                            ? 'desc'
                                            : 'asc';

                                    return;
                                }

                                this.sortMarcaField = field;

                                this.sortMarcas = 'asc';

                                this.marcaPage = 1;

                                this.refreshMarcas();
                            },


                            sortModelosBy(field) {
                                if (
                                    this.sortModeloField
                                    === field
                                ) {
                                    this.sortModelos =
                                        this.sortModelos === 'asc'
                                            ? 'desc'
                                            : 'asc';

                                    return;
                                }

                                this.sortModeloField = field;

                                this.sortModelos = 'asc';

                                this.modeloPage = 1;

                                this.refreshModelos();
                            },
        
        
                            refreshMarcas() {
                                const term =
                                    this.normalize(
                                        this.search
                                    );
        
                                const items =
                                    (
                                        this.data?.marcas
                                        ?? []
                                    )
                                        .filter(
                                            marca => {
                                                if (
                                                    !this.matchesEstado(
                                                        marca.activo
                                                    )
                                                ) {
                                                    return false;
                                                }
        
                                                if (
                                                    term !== ''
                                                    &&
                                                    !String(
                                                        marca._search
                                                        ?? ''
                                                    ).includes(
                                                        term
                                                    )
                                                ) {
                                                    return false;
                                                }
        
                                                return true;
                                            }
                                        )
                                        .slice();
        
                                        items.sort(
                                            (a, b) => {
                                                let result = 0;

                                                switch (
                                                    this.sortMarcaField
                                                ) {
                                                    case 'id':
                                                        result =
                                                            this.compareNumber(
                                                                a.id,
                                                                b.id,
                                                                this.sortMarcas
                                                            );
                                                        break;

                                                    case 'activo':
                                                        result =
                                                            this.compareNumber(
                                                                Number(
                                                                    Boolean(a.activo)
                                                                ),
                                                                Number(
                                                                    Boolean(b.activo)
                                                                ),
                                                                this.sortMarcas
                                                            );
                                                        break;

                                                    case 'totalModelos':
                                                        result =
                                                            this.compareNumber(
                                                                a.totalModelos,
                                                                b.totalModelos,
                                                                this.sortMarcas
                                                            );
                                                        break;

                                                    case 'totalEquipos':
                                                        result =
                                                            this.compareNumber(
                                                                a.totalEquipos,
                                                                b.totalEquipos,
                                                                this.sortMarcas
                                                            );
                                                        break;

                                                    case 'fechaCreacion':
                                                        result =
                                                            this.compareDate(
                                                                a.fechaCreacion,
                                                                b.fechaCreacion,
                                                                this.sortMarcas
                                                            );
                                                        break;

                                                    case 'nombre':
                                                    default:
                                                        result =
                                                            this.compareText(
                                                                a.nombre,
                                                                b.nombre,
                                                                this.sortMarcas
                                                            );
                                                        break;
                                                }

                                                if (result !== 0) {
                                                    return result;
                                                }

                                                return this.compareNumber(
                                                    a.id,
                                                    b.id,
                                                    'asc'
                                                );
                                            }
                                        );
        
                                this.filteredMarcasCache =
                                    items;
        
                                if (
                                    this.marcaPage
                                    >
                                    this.lastMarcaPage
                                ) {
                                    this.marcaPage =
                                        this.lastMarcaPage;
                                }
                            },
        
        
                            refreshModelos() {
                                const term =
                                    this.normalize(
                                        this.search
                                    );
        
                                const items =
                                    (
                                        this.data?.modelos
                                        ?? []
                                    )
                                        .filter(
                                            modelo => {
                                                if (
                                                    !this.matchesEstado(
                                                        modelo.activo
                                                    )
                                                ) {
                                                    return false;
                                                }
        
                                                if (
                                                    this.tipo
                                                    !== 'todos'
                                                    &&
                                                    String(
                                                        modelo.idTipoEquipo
                                                        ?? ''
                                                    )
                                                    !==
                                                    String(
                                                        this.tipo
                                                    )
                                                ) {
                                                    return false;
                                                }
        
                                                if (
                                                    term !== ''
                                                    &&
                                                    !String(
                                                        modelo._search
                                                        ?? ''
                                                    ).includes(
                                                        term
                                                    )
                                                ) {
                                                    return false;
                                                }
        
                                                return true;
                                            }
                                        )
                                        .slice();
        
                                    items.sort(
                                        (a, b) => {
                                            let result = 0;

                                            switch (
                                                this.sortModeloField
                                            ) {
                                                case 'id':
                                                    result =
                                                        this.compareNumber(
                                                            a.id,
                                                            b.id,
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'nombre':
                                                    result =
                                                        this.compareText(
                                                            a.nombre,
                                                            b.nombre,
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'tipoEquipoNombre':
                                                    result =
                                                        this.compareText(
                                                            a.tipoEquipoNombre,
                                                            b.tipoEquipoNombre,
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'activo':
                                                    result =
                                                        this.compareNumber(
                                                            Number(
                                                                Boolean(a.activo)
                                                            ),
                                                            Number(
                                                                Boolean(b.activo)
                                                            ),
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'totalEquipos':
                                                    result =
                                                        this.compareNumber(
                                                            a.totalEquipos,
                                                            b.totalEquipos,
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'fechaCreacion':
                                                    result =
                                                        this.compareDate(
                                                            a.fechaCreacion,
                                                            b.fechaCreacion,
                                                            this.sortModelos
                                                        );
                                                    break;

                                                case 'marcaNombre':
                                                default:
                                                    result =
                                                        this.compareText(
                                                            a.marcaNombre,
                                                            b.marcaNombre,
                                                            this.sortModelos
                                                        );
                                                    break;
                                            }

                                            if (result !== 0) {
                                                return result;
                                            }

                                            return this.compareNumber(
                                                a.id,
                                                b.id,
                                                'asc'
                                            );
                                        }
                                    );
        
                                this.filteredModelosCache =
                                    items;
        
                                if (
                                    this.modeloPage
                                    >
                                    this.lastModeloPage
                                ) {
                                    this.modeloPage =
                                        this.lastModeloPage;
                                }
                            },
        
        
                            get filteredMarcas() {
                                return this.filteredMarcasCache;
                            },
        
        
                            get filteredModelos() {
                                return this.filteredModelosCache;
                            },
        
        
                            get marcaPageSize() {
                                const value =
                                    Number(
                                        this.perPageMarcas
                                    );
        
                                return (
                                    Number.isFinite(value)
                                    && value > 0
                                )
                                    ? value
                                    : 10;
                            },
        
        
                            get modeloPageSize() {
                                const value =
                                    Number(
                                        this.perPageModelos
                                    );
        
                                return (
                                    Number.isFinite(value)
                                    && value > 0
                                )
                                    ? value
                                    : 10;
                            },
        
        
                            get lastMarcaPage() {
                                return Math.max(
                                    1,
                                    Math.ceil(
                                        this.filteredMarcasCache.length
                                        /
                                        this.marcaPageSize
                                    )
                                );
                            },
        
        
                            get lastModeloPage() {
                                return Math.max(
                                    1,
                                    Math.ceil(
                                        this.filteredModelosCache.length
                                        /
                                        this.modeloPageSize
                                    )
                                );
                            },
        
        
                            get currentMarcaPage() {
                                return Math.min(
                                    Math.max(
                                        1,
                                        this.marcaPage
                                    ),
                                    this.lastMarcaPage
                                );
                            },
        
        
                            get currentModeloPage() {
                                return Math.min(
                                    Math.max(
                                        1,
                                        this.modeloPage
                                    ),
                                    this.lastModeloPage
                                );
                            },
        
        
                            get paginatedMarcas() {
                                const start =
                                    (
                                        this.currentMarcaPage
                                        - 1
                                    )
                                    *
                                    this.marcaPageSize;
        
                                return this.filteredMarcasCache
                                    .slice(
                                        start,
                                        start
                                        +
                                        this.marcaPageSize
                                    );
                            },
        
        
                            get paginatedModelos() {
                                const start =
                                    (
                                        this.currentModeloPage
                                        - 1
                                    )
                                    *
                                    this.modeloPageSize;
        
                                return this.filteredModelosCache
                                    .slice(
                                        start,
                                        start
                                        +
                                        this.modeloPageSize
                                    );
                            },
        
        
                            paginationItems(
                                current,
                                last
                            ) {
                                if (last <= 7) {
                                    return Array.from(
                                        {
                                            length: last,
                                        },
                                        (_, index) => ({
                                            key:
                                                'page-'
                                                +
                                                (
                                                    index
                                                    + 1
                                                ),
        
                                            page:
                                                index
                                                + 1,
                                        })
                                    );
                                }
        
                                const pages =
                                    new Set([
                                        1,
                                        2,
                                        last - 1,
                                        last,
                                        current - 2,
                                        current - 1,
                                        current,
                                        current + 1,
                                        current + 2,
                                    ]);
        
                                const valid =
                                    [...pages]
                                        .filter(
                                            page =>
                                                page >= 1
                                                &&
                                                page <= last
                                        )
                                        .sort(
                                            (a, b) =>
                                                a - b
                                        );
        
                                const result = [];
        
                                valid.forEach(
                                    (page, index) => {
                                        if (
                                            index > 0
                                            &&
                                            page
                                            -
                                            valid[
                                                index - 1
                                            ]
                                            > 1
                                        ) {
                                            result.push({
                                                key:
                                                    'ellipsis-'
                                                    + page,
        
                                                page:
                                                    null,
                                            });
                                        }
        
                                        result.push({
                                            key:
                                                'page-'
                                                + page,
        
                                            page,
                                        });
                                    }
                                );
        
                                return result;
                            },
        
        
                            get marcaPaginationItems() {
                                return this.paginationItems(
                                    this.currentMarcaPage,
                                    this.lastMarcaPage
                                );
                            },
        
        
                            get modeloPaginationItems() {
                                return this.paginationItems(
                                    this.currentModeloPage,
                                    this.lastModeloPage
                                );
                            },
        
        
                            goMarcaPage(page) {
                                const target =
                                    Number(page);
        
                                if (
                                    !Number.isFinite(
                                        target
                                    )
                                ) {
                                    return;
                                }
        
                                this.marcaPage =
                                    Math.min(
                                        Math.max(
                                            1,
                                            target
                                        ),
                                        this.lastMarcaPage
                                    );
                            },
        
        
                            previousMarcaPage() {
                                this.goMarcaPage(
                                    this.currentMarcaPage
                                    - 1
                                );
                            },
        
        
                            nextMarcaPage() {
                                this.goMarcaPage(
                                    this.currentMarcaPage
                                    + 1
                                );
                            },
        
        
                            goModeloPage(page) {
                                const target =
                                    Number(page);
        
                                if (
                                    !Number.isFinite(
                                        target
                                    )
                                ) {
                                    return;
                                }
        
                                this.modeloPage =
                                    Math.min(
                                        Math.max(
                                            1,
                                            target
                                        ),
                                        this.lastModeloPage
                                    );
                            },
        
        
                            previousModeloPage() {
                                this.goModeloPage(
                                    this.currentModeloPage
                                    - 1
                                );
                            },
        
        
                            nextModeloPage() {
                                this.goModeloPage(
                                    this.currentModeloPage
                                    + 1
                                );
                            },
        
        
                            toggleCurrentMarcaSelection(
                                checked
                            ) {
                                const ids =
                                    this.paginatedMarcas
                                        .map(
                                            marca =>
                                                String(
                                                    marca.id
                                                )
                                        );
        
                                if (checked) {
                                    this.selectedMarcaIds =
                                        [
                                            ...new Set([
                                                ...this
                                                    .selectedMarcaIds,
        
                                                ...ids,
                                            ]),
                                        ];
        
                                    return;
                                }
        
                                this.selectedMarcaIds =
                                    this.selectedMarcaIds
                                        .filter(
                                            id =>
                                                !ids.includes(
                                                    id
                                                )
                                        );
                            },
        
        
                            toggleCurrentModeloSelection(
                                checked
                            ) {
                                const ids =
                                    this.paginatedModelos
                                        .map(
                                            modelo =>
                                                String(
                                                    modelo.id
                                                )
                                        );
        
                                if (checked) {
                                    this.selectedModeloIds =
                                        [
                                            ...new Set([
                                                ...this
                                                    .selectedModeloIds,
        
                                                ...ids,
                                            ]),
                                        ];
        
                                    return;
                                }
        
                                this.selectedModeloIds =
                                    this.selectedModeloIds
                                        .filter(
                                            id =>
                                                !ids.includes(
                                                    id
                                                )
                                        );
                            },
        
        
                            showToast(message) {
                                this.toastMessage =
                                    message
                                    || 'Cambios guardados correctamente.';
        
                                this.toastOpen =
                                    true;
        
                                if (
                                    this.toastTimer
                                ) {
                                    clearTimeout(
                                        this.toastTimer
                                    );
                                }
        
                                this.toastTimer =
                                    setTimeout(
                                        () => {
                                            this.toastOpen =
                                                false;
                                        },
                                        3200
                                    );
                            },
        
        
                            async toggleMarcaStatus(
                                marca
                            ) {
                                if (
                                    !marca
                                    ||
                                    this.togglingMarcaId
                                    !== null
                                ) {
                                    return;
                                }
        
                                if (
                                    marca.activo
                                    &&
                                    !window.confirm(
                                        `¿Desactivar la marca ${marca.nombre}? Los registros existentes conservarán su relación.`
                                    )
                                ) {
                                    return;
                                }
        
                                this.togglingMarcaId =
                                    Number(
                                        marca.id
                                    );
        
                                try {
                                    const result =
                                        await $wire
                                            .toggleMarcaStatus(
                                                Number(
                                                    marca.id
                                                )
                                            );
        
                                    if (
                                        !result
                                        ||
                                        result.ok
                                        !== true
                                    ) {
                                        throw new Error(
                                            result?.message
                                            || 'No fue posible actualizar la marca.'
                                        );
                                    }
        
                                    marca.activo =
                                        Boolean(
                                            result.active
                                        );
        
                                    this.refreshMarcas();
        
                                    this.showToast(
                                        result.message
                                    );
        
                                } catch (error) {
                                    this.showToast(
                                        error?.message
                                        || 'No fue posible actualizar la marca.'
                                    );
        
                                } finally {
                                    this.togglingMarcaId =
                                        null;
                                }
                            },
        
        
                            async toggleModeloStatus(
                                modelo
                            ) {
                                if (
                                    !modelo
                                    ||
                                    this.togglingModeloId
                                    !== null
                                ) {
                                    return;
                                }
        
                                if (
                                    modelo.activo
                                    &&
                                    !window.confirm(
                                        `¿Desactivar el modelo ${modelo.nombre}? Los equipos existentes conservarán su relación.`
                                    )
                                ) {
                                    return;
                                }
        
                                this.togglingModeloId =
                                    Number(
                                        modelo.id
                                    );
        
                                try {
                                    const result =
                                        await $wire
                                            .toggleModeloStatus(
                                                Number(
                                                    modelo.id
                                                )
                                            );
        
                                    if (
                                        !result
                                        ||
                                        result.ok
                                        !== true
                                    ) {
                                        throw new Error(
                                            result?.message
                                            || 'No fue posible actualizar el modelo.'
                                        );
                                    }
        
                                    modelo.activo =
                                        Boolean(
                                            result.active
                                        );
        
                                    this.refreshModelos();
        
                                    this.showToast(
                                        result.message
                                    );
        
                                } catch (error) {
                                    this.showToast(
                                        error?.message
                                        || 'No fue posible actualizar el modelo.'
                                    );
        
                                } finally {
                                    this.togglingModeloId =
                                        null;
                                }
                            },
        
        
                            formatNumber(value) {
                                return new Intl
                                    .NumberFormat(
                                        'es-MX'
                                    )
                                    .format(
                                        Number(
                                            value
                                            ?? 0
                                        )
                                    );
                            },
        
        
                            formatDate(value) {
                                const text =
                                    String(
                                        value
                                        ?? ''
                                    );
        
                                if (text === '') {
                                    return '—';
                                }
        
                                const match =
                                    text.match(
                                        /^(\d{4})-(\d{2})-(\d{2})/
                                    );
        
                                if (!match) {
                                    return text;
                                }
        
                                return (
                                    match[3]
                                    + '/'
                                    + match[2]
                                    + '/'
                                    + match[1]
                                );
                            },
                        });
</script>
