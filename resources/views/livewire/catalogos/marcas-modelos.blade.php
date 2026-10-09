<div
    x-data="(() => {

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN INICIAL LIGERA
        |--------------------------------------------------------------------------
        |
        | Ya no enviamos el catálogo completo con todas
        | las marcas y modelos al navegador.
        |
        | Sólo iniciamos Alpine con la estructura mínima necesaria.
        |
        */

        const base =
            brandsModelsCatalog({
                marcas: [],
                modelos: [],
                tiposEquipo: [],

                metrics: {
                    totalMarcas: 0,
                    totalModelos: 0,
                    marcasInactivas: 0,
                    modelosEnUso: 0,
                },

                filters: {
                    estados: [
                        {
                            value: 'todos',
                            label: 'Todos los estados',
                        },
                        {
                            value: 'activos',
                            label: 'Activos',
                        },
                        {
                            value: 'inactivos',
                            label: 'Inactivos',
                        },
                    ],

                    perPage: [
                        {
                            value: '10',
                            label: '10',
                        },
                        {
                            value: '25',
                            label: '25',
                        },
                        {
                            value: '50',
                            label: '50',
                        },
                        {
                            value: '100',
                            label: '100',
                        },
                    ],

                    sort: [
                        {
                            value: 'asc',
                            label: 'ASC',
                        },
                        {
                            value: 'desc',
                            label: 'DESC',
                        },
                    ],
                },

                defaults: {
                    search: '',
                    estado: 'todos',
                    tipo: 'todos',

                    perPageMarcas: '10',
                    perPageModelos: '10',

                    sortMarcas: 'asc',
                    sortModelos: 'asc',
                },
            });


        /*
        |--------------------------------------------------------------------------
        | ESTADO DEL SERVIDOR
        |--------------------------------------------------------------------------
        */

        base.sortMarcaField =
            'nombre';

        base.sortModeloField =
            'marcaNombre';


        base.loadingMarcas =
            true;

        base.loadingModelos =
            true;

        base.loadingMetrics =
            true;

        base.loadingTipos =
            true;


        base._marcasItems =
            [];

        base._modelosItems =
            [];


        base._marcaTotal =
            0;

        base._modeloTotal =
            0;


        base._marcaLastPage =
            1;

        base._modeloLastPage =
            1;


        base._metrics = {
            totalMarcas: 0,
            totalModelos: 0,
            marcasInactivas: 0,
            modelosEnUso: 0,
        };


        /*
         * Se incrementan con cada petición.
         *
         * Si una búsqueda anterior responde después
         * que una búsqueda nueva, ignoramos esa
         * respuesta vieja.
         */
        base._marcasRequestId =
            0;

        base._modelosRequestId =
            0;


        base.searchTimer =
            null;


        /*
        |--------------------------------------------------------------------------
        | CARGAR MÉTRICAS
        |--------------------------------------------------------------------------
        */

        base.loadMetrics =
            async function () {

                this.loadingMetrics =
                    true;


                try {

                    const result =
                        await this.$wire
                            .loadCatalogMetrics();


                    this._metrics = {
                        totalMarcas:
                            Number(
                                result?.totalMarcas
                                ?? 0
                            ),

                        totalModelos:
                            Number(
                                result?.totalModelos
                                ?? 0
                            ),

                        marcasInactivas:
                            Number(
                                result?.marcasInactivas
                                ?? 0
                            ),

                        modelosEnUso:
                            Number(
                                result?.modelosEnUso
                                ?? 0
                            ),
                    };

                } catch (error) {

                    console.error(
                        'No fue posible cargar las métricas del catálogo.',
                        error
                    );

                } finally {

                    this.loadingMetrics =
                        false;
                }
            };


        /*
        |--------------------------------------------------------------------------
        | CARGAR TIPOS DE EQUIPO
        |--------------------------------------------------------------------------
        */

        base.loadTiposEquipo =
            async function () {

                this.loadingTipos =
                    true;


                try {

                    const result =
                        await this.$wire
                            .loadTiposEquipoOptions();


                    this.data.tiposEquipo =
                        Array.isArray(result)
                            ? result
                            : [];

                } catch (error) {

                    console.error(
                        'No fue posible cargar los tipos de equipo.',
                        error
                    );

                    this.data.tiposEquipo =
                        [];

                } finally {

                    this.loadingTipos =
                        false;
                }
            };


        /*
        |--------------------------------------------------------------------------
        | CARGAR PÁGINA DE MARCAS
        |--------------------------------------------------------------------------
        */

        base.loadMarcas =
            async function () {

                const requestId =
                    ++this._marcasRequestId;


                this.loadingMarcas =
                    true;


                try {

                    const result =
                        await this.$wire
                            .loadMarcasPage({
                                page:
                                    Number(
                                        this.marcaPage
                                        ?? 1
                                    ),

                                perPage:
                                    Number(
                                        this.perPageMarcas
                                        ?? 10
                                    ),

                                search:
                                    String(
                                        this.search
                                        ?? ''
                                    ),

                                estado:
                                    String(
                                        this.estado
                                        ?? 'todos'
                                    ),

                                sortField:
                                    String(
                                        this.sortMarcaField
                                        ?? 'nombre'
                                    ),

                                sortDirection:
                                    String(
                                        this.sortMarcas
                                        ?? 'asc'
                                    ),
                            });


                    /*
                     * Llegó una respuesta vieja.
                     */
                    if (
                        requestId
                        !== this._marcasRequestId
                    ) {
                        return;
                    }


                    const items =
                        Array.isArray(
                            result?.items
                        )
                            ? result.items
                            : [];


                    const total =
                        Number(
                            result?.total
                            ?? 0
                        );


                    const lastPage =
                        Math.max(
                            1,
                            Number(
                                result?.lastPage
                                ?? 1
                            )
                        );


                    /*
                     * Puede ocurrir si eliminamos o
                     * desactivamos el último registro
                     * de la última página.
                     */
                    if (
                        items.length === 0
                        &&
                        Number(
                            this.marcaPage
                        ) > 1
                    ) {

                        this.marcaPage =
                            Math.max(
                                1,
                                Number(
                                    this.marcaPage
                                ) - 1
                            );


                        return await this.loadMarcas();
                    }


                    this._marcasItems =
                        items;


                    this._marcaTotal =
                        total;


                    this._marcaLastPage =
                        lastPage;


                    /*
                     * Limpiar selecciones que ya no
                     * pertenecen a la página actual.
                     */
                    const visibleIds =
                        new Set(
                            items.map(
                                marca =>
                                    String(
                                        marca.id
                                    )
                            )
                        );


                    this.selectedMarcaIds =
                        this.selectedMarcaIds
                            .filter(
                                id =>
                                    visibleIds.has(
                                        String(id)
                                    )
                            );

                } catch (error) {

                    console.error(
                        'No fue posible cargar las marcas.',
                        error
                    );

                } finally {

                    if (
                        requestId
                        === this._marcasRequestId
                    ) {

                        this.loadingMarcas =
                            false;
                    }
                }
            };


        /*
        |--------------------------------------------------------------------------
        | CARGAR PÁGINA DE MODELOS
        |--------------------------------------------------------------------------
        */

        base.loadModelos =
            async function () {

                const requestId =
                    ++this._modelosRequestId;


                this.loadingModelos =
                    true;


                try {

                    const result =
                        await this.$wire
                            .loadModelosPage({
                                page:
                                    Number(
                                        this.modeloPage
                                        ?? 1
                                    ),

                                perPage:
                                    Number(
                                        this.perPageModelos
                                        ?? 10
                                    ),

                                search:
                                    String(
                                        this.search
                                        ?? ''
                                    ),

                                estado:
                                    String(
                                        this.estado
                                        ?? 'todos'
                                    ),

                                tipo:
                                    String(
                                        this.tipo
                                        ?? 'todos'
                                    ),

                                sortField:
                                    String(
                                        this.sortModeloField
                                        ?? 'marcaNombre'
                                    ),

                                sortDirection:
                                    String(
                                        this.sortModelos
                                        ?? 'asc'
                                    ),
                            });


                    /*
                     * Ignorar respuesta vieja.
                     */
                    if (
                        requestId
                        !== this._modelosRequestId
                    ) {
                        return;
                    }


                    const items =
                        Array.isArray(
                            result?.items
                        )
                            ? result.items
                            : [];


                    const total =
                        Number(
                            result?.total
                            ?? 0
                        );


                    const lastPage =
                        Math.max(
                            1,
                            Number(
                                result?.lastPage
                                ?? 1
                            )
                        );


                    if (
                        items.length === 0
                        &&
                        Number(
                            this.modeloPage
                        ) > 1
                    ) {

                        this.modeloPage =
                            Math.max(
                                1,
                                Number(
                                    this.modeloPage
                                ) - 1
                            );


                        return await this.loadModelos();
                    }


                    this._modelosItems =
                        items;


                    this._modeloTotal =
                        total;


                    this._modeloLastPage =
                        lastPage;


                    const visibleIds =
                        new Set(
                            items.map(
                                modelo =>
                                    String(
                                        modelo.id
                                    )
                            )
                        );


                    this.selectedModeloIds =
                        this.selectedModeloIds
                            .filter(
                                id =>
                                    visibleIds.has(
                                        String(id)
                                    )
                            );

                } catch (error) {

                    console.error(
                        'No fue posible cargar los modelos.',
                        error
                    );

                } finally {

                    if (
                        requestId
                        === this._modelosRequestId
                    ) {

                        this.loadingModelos =
                            false;
                    }
                }
            };


        /*
        |--------------------------------------------------------------------------
        | ALIAS DE COMPATIBILIDAD
        |--------------------------------------------------------------------------
        |
        | El controlador Alpine anterior y algunos parciales
        | utilizan refreshMarcas() / refreshModelos().
        |
        */

        base.refreshMarcas =
            function () {

                return this.loadMarcas();
            };


        base.refreshModelos =
            function () {

                return this.loadModelos();
            };


        /*
        |--------------------------------------------------------------------------
        | ORDENAR MARCAS
        |--------------------------------------------------------------------------
        */

        base.sortMarcasBy =
            function (
                field
            ) {

                if (
                    this.sortMarcaField
                    === field
                ) {

                    this.sortMarcas =
                        this.sortMarcas
                        === 'asc'
                            ? 'desc'
                            : 'asc';


                    /*
                     * El watcher de sortMarcas
                     * realizará la petición.
                     */
                    return;
                }


                this.sortMarcaField =
                    field;


                this.marcaPage =
                    1;


                /*
                 * Si ya era ASC, el watcher no se
                 * ejecutaría porque el valor no cambia.
                 */
                if (
                    this.sortMarcas
                    === 'asc'
                ) {

                    this.loadMarcas();

                    return;
                }


                this.sortMarcas =
                    'asc';
            };


        /*
        |--------------------------------------------------------------------------
        | ORDENAR MODELOS
        |--------------------------------------------------------------------------
        */

        base.sortModelosBy =
            function (
                field
            ) {

                if (
                    this.sortModeloField
                    === field
                ) {

                    this.sortModelos =
                        this.sortModelos
                        === 'asc'
                            ? 'desc'
                            : 'asc';


                    return;
                }


                this.sortModeloField =
                    field;


                this.modeloPage =
                    1;


                if (
                    this.sortModelos
                    === 'asc'
                ) {

                    this.loadModelos();

                    return;
                }


                this.sortModelos =
                    'asc';
            };


        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN MARCAS
        |--------------------------------------------------------------------------
        */

        base.goMarcaPage =
            function (
                page
            ) {

                const target =
                    Number(page);


                if (
                    !Number.isFinite(
                        target
                    )
                ) {
                    return;
                }


                const nextPage =
                    Math.min(
                        Math.max(
                            1,
                            target
                        ),
                        this.lastMarcaPage
                    );


                if (
                    nextPage
                    === this.marcaPage
                ) {
                    return;
                }


                this.marcaPage =
                    nextPage;


                this.loadMarcas();
            };


        base.previousMarcaPage =
            function () {

                this.goMarcaPage(
                    this.currentMarcaPage
                    - 1
                );
            };


        base.nextMarcaPage =
            function () {

                this.goMarcaPage(
                    this.currentMarcaPage
                    + 1
                );
            };


        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN MODELOS
        |--------------------------------------------------------------------------
        */

        base.goModeloPage =
            function (
                page
            ) {

                const target =
                    Number(page);


                if (
                    !Number.isFinite(
                        target
                    )
                ) {
                    return;
                }


                const nextPage =
                    Math.min(
                        Math.max(
                            1,
                            target
                        ),
                        this.lastModeloPage
                    );


                if (
                    nextPage
                    === this.modeloPage
                ) {
                    return;
                }


                this.modeloPage =
                    nextPage;


                this.loadModelos();
            };


        base.previousModeloPage =
            function () {

                this.goModeloPage(
                    this.currentModeloPage
                    - 1
                );
            };


        base.nextModeloPage =
            function () {

                this.goModeloPage(
                    this.currentModeloPage
                    + 1
                );
            };


        /*
        |--------------------------------------------------------------------------
        | ACTIVAR / DESACTIVAR MARCA
        |--------------------------------------------------------------------------
        */

        base.toggleMarcaStatus =
            async function (
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
                        await this.$wire
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


                    this.showToast(
                        result.message
                    );


                    /*
                     * El cambio puede modificar:
                     *
                     * - página actual
                     * - filtro Activos/Inactivos
                     * - métricas
                     */
                    await Promise.all([
                        this.loadMarcas(),
                        this.loadMetrics(),
                    ]);

                } catch (error) {

                    this.showToast(
                        error?.message
                        || 'No fue posible actualizar la marca.'
                    );

                } finally {

                    this.togglingMarcaId =
                        null;
                }
            };


        /*
        |--------------------------------------------------------------------------
        | ACTIVAR / DESACTIVAR MODELO
        |--------------------------------------------------------------------------
        */

        base.toggleModeloStatus =
            async function (
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
                        await this.$wire
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


                    this.showToast(
                        result.message
                    );


                    await Promise.all([
                        this.loadModelos(),
                        this.loadMetrics(),
                    ]);

                } catch (error) {

                    this.showToast(
                        error?.message
                        || 'No fue posible actualizar el modelo.'
                    );

                } finally {

                    this.togglingModeloId =
                        null;
                }
            };


        /*
        |--------------------------------------------------------------------------
        | GETTERS SERVER-SIDE
        |--------------------------------------------------------------------------
        |
        | Los parciales existentes siguen utilizando nombres como:
        |
        | filteredMarcas
        | paginatedMarcas
        | lastMarcaPage
        |
        | Los conservamos para no modificar todavía las tablas.
        |
        */

        Object.defineProperties(
            base,
            {

                /*
                |--------------------------------------------------------------------------
                | Métricas
                |--------------------------------------------------------------------------
                */

                totalMarcas: {
                    configurable: true,

                    get() {
                        return Number(
                            this._metrics
                                ?.totalMarcas
                            ?? 0
                        );
                    },
                },


                totalModelos: {
                    configurable: true,

                    get() {
                        return Number(
                            this._metrics
                                ?.totalModelos
                            ?? 0
                        );
                    },
                },


                marcasInactivas: {
                    configurable: true,

                    get() {
                        return Number(
                            this._metrics
                                ?.marcasInactivas
                            ?? 0
                        );
                    },
                },


                modelosEnUso: {
                    configurable: true,

                    get() {
                        return Number(
                            this._metrics
                                ?.modelosEnUso
                            ?? 0
                        );
                    },
                },


                /*
                |--------------------------------------------------------------------------
                | Resultados
                |--------------------------------------------------------------------------
                |
                | Los parciales actuales solamente necesitan .length
                | de filteredMarcas / filteredModelos para mostrar:
                |
                | Resultados: X marcas encontradas
                |
                */

                filteredMarcas: {
                    configurable: true,

                    get() {
                        return {
                            length:
                                Number(
                                    this._marcaTotal
                                    ?? 0
                                ),
                        };
                    },
                },


                filteredModelos: {
                    configurable: true,

                    get() {
                        return {
                            length:
                                Number(
                                    this._modeloTotal
                                    ?? 0
                                ),
                        };
                    },
                },


                /*
                |--------------------------------------------------------------------------
                | Filas visibles
                |--------------------------------------------------------------------------
                */

                paginatedMarcas: {
                    configurable: true,

                    get() {
                        return (
                            this._marcasItems
                            ?? []
                        );
                    },
                },


                paginatedModelos: {
                    configurable: true,

                    get() {
                        return (
                            this._modelosItems
                            ?? []
                        );
                    },
                },


                /*
                |--------------------------------------------------------------------------
                | Última página
                |--------------------------------------------------------------------------
                */

                lastMarcaPage: {
                    configurable: true,

                    get() {
                        return Math.max(
                            1,
                            Number(
                                this._marcaLastPage
                                ?? 1
                            )
                        );
                    },
                },


                lastModeloPage: {
                    configurable: true,

                    get() {
                        return Math.max(
                            1,
                            Number(
                                this._modeloLastPage
                                ?? 1
                            )
                        );
                    },
                },


                /*
                |--------------------------------------------------------------------------
                | Página actual
                |--------------------------------------------------------------------------
                */

                currentMarcaPage: {
                    configurable: true,

                    get() {
                        return Math.min(
                            Math.max(
                                1,
                                Number(
                                    this.marcaPage
                                    ?? 1
                                )
                            ),
                            this.lastMarcaPage
                        );
                    },
                },


                currentModeloPage: {
                    configurable: true,

                    get() {
                        return Math.min(
                            Math.max(
                                1,
                                Number(
                                    this.modeloPage
                                    ?? 1
                                )
                            ),
                            this.lastModeloPage
                        );
                    },
                },
            }
        );


        /*
        |--------------------------------------------------------------------------
        | INICIALIZACIÓN
        |--------------------------------------------------------------------------
        */

        base.init =
            function () {

                /*
                |--------------------------------------------------------------------------
                | BUSCADOR
                |--------------------------------------------------------------------------
                |
                | Esperamos 300 ms desde la última tecla.
                |
                | Así escribir:
                |
                | D E L L
                |
                | no genera cuatro consultas.
                |
                */

                this.$watch(
                    'search',
                    () => {

                        this.marcaPage =
                            1;

                        this.modeloPage =
                            1;


                        if (
                            this.searchTimer
                        ) {

                            clearTimeout(
                                this.searchTimer
                            );
                        }


                        this.searchTimer =
                            setTimeout(
                                () => {

                                    this.loadMarcas();

                                    this.loadModelos();

                                },
                                300
                            );
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | ESTADO
                |--------------------------------------------------------------------------
                */

                this.$watch(
                    'estado',
                    () => {

                        this.marcaPage =
                            1;

                        this.modeloPage =
                            1;


                        this.loadMarcas();

                        this.loadModelos();
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | TIPO DE EQUIPO
                |--------------------------------------------------------------------------
                */

                this.$watch(
                    'tipo',
                    () => {

                        this.modeloPage =
                            1;


                        this.loadModelos();
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | REGISTROS POR PÁGINA
                |--------------------------------------------------------------------------
                */

                this.$watch(
                    'perPageMarcas',
                    () => {

                        this.marcaPage =
                            1;


                        this.loadMarcas();
                    }
                );


                this.$watch(
                    'perPageModelos',
                    () => {

                        this.modeloPage =
                            1;


                        this.loadModelos();
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | ORDEN ASC / DESC
                |--------------------------------------------------------------------------
                */

                this.$watch(
                    'sortMarcas',
                    () => {

                        this.marcaPage =
                            1;


                        this.loadMarcas();
                    }
                );


                this.$watch(
                    'sortModelos',
                    () => {

                        this.modeloPage =
                            1;


                        this.loadModelos();
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | PRIMERA CARGA
                |--------------------------------------------------------------------------
                |
                | Estas operaciones son independientes.
                |
                */

                Promise.all([
                    this.loadMetrics(),
                    this.loadTiposEquipo(),
                    this.loadMarcas(),
                    this.loadModelos(),
                ]);
            };


        return base;

    })()"

    class="space-y-5"
>


    {{-- ============================================================
        TOAST
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.toast'
    )


    {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.header'
    )


    {{-- ============================================================
        FILTROS
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.filters'
    )


    {{-- ============================================================
        MÉTRICAS
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.metrics'
    )


    {{-- ============================================================
        MARCAS
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.marcas.section'
    )


    {{-- ============================================================
        MODELOS
    ============================================================ --}}
    @include(
        'livewire.catalogos.marcas-modelos.modelos.section'
    )

</div>