<div
    x-data="(() => {
        const base = brandsModelsCatalog(@js($catalogData));

        return Object.assign(base, {
            sortMarcaField: 'nombre',
            sortModeloField: 'marcaNombre',

            compareNumber(a, b, direction) {
                const result = Number(a ?? 0) - Number(b ?? 0);

                return direction === 'desc'
                    ? -result
                    : result;
            },

            compareDate(a, b, direction) {
                const aTime = a
                    ? new Date(a).getTime()
                    : 0;

                const bTime = b
                    ? new Date(b).getTime()
                    : 0;

                const safeA = Number.isFinite(aTime)
                    ? aTime
                    : 0;

                const safeB = Number.isFinite(bTime)
                    ? bTime
                    : 0;

                const result = safeA - safeB;

                return direction === 'desc'
                    ? -result
                    : result;
            },

            sortMarcasBy(field) {
                if (this.sortMarcaField === field) {
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
                if (this.sortModeloField === field) {
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
                                            Boolean(
                                                a.activo
                                            )
                                        ),
                                        Number(
                                            Boolean(
                                                b.activo
                                            )
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

                            case 'marcaNombre':
                                result =
                                    this.compareText(
                                        a.marcaNombre,
                                        b.marcaNombre,
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
                                            Boolean(
                                                a.activo
                                            )
                                        ),
                                        Number(
                                            Boolean(
                                                b.activo
                                            )
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
        });
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