{{-- ============================================================
    SECCIÓN DE RESULTADOS
    ------------------------------------------------------------
    Usa el componente reutilizable <x-data-table>
    (resources/views/components/data-table).
============================================================ --}}
@php
    $etiquetaUnidad = app(\App\Services\PlantillaInstalacionService::class)
        ->etiquetas()['unidad_singular'];

    $equipoDadoDeBaja = fn ($equipo): bool =>
        (int) ($equipo->dado_de_baja ?? 0) === 1
        || strtoupper((string) ($equipo->estado_clave ?? '')) === 'BAJA';
@endphp

<x-data-table
    :rows="$equipos"
    row-key="id_equipo"
    key-prefix="equipo"
    selectable

    :sort-field="$sortField"
    :sort-direction="$sort"
    sort-action="sortBy"

    per-page-model="perPage"
    sort-model="sort"
    refresh-action="refreshTable"

    result-singular="Equipo encontrado"
    result-plural="Equipos encontrados"
    empty-message="No se encontraron equipos con los filtros seleccionados."
    min-width="1500px"

    :columns="[
        [
            'key' => 'codigo_inventario',
            'label' => 'ID Equipo',
            'width' => '140px',
            'sort' => 'codigo_inventario',
            'strong' => true,
            'mobile' => false,
        ],
        [
            'key' => 'nombre_equipo',
            'label' => 'Nombre de equipo',
            'width' => '210px',
            'sort' => 'nombre_equipo',
            'mobile' => false,
        ],
        [
            'key' => 'tipo_equipo_nombre',
            'label' => 'Tipo de equipo',
            'width' => '145px',
            'sort' => 'tipo_equipo',
            'mobile' => false,
        ],
        [
            'key' => 'marca_nombre',
            'label' => 'Marca',
            'width' => '110px',
            'sort' => 'marca',
            'mobile' => false,
        ],
        [
            'key' => 'modelo_nombre',
            'label' => 'Modelo',
            'width' => '165px',
            'sort' => 'modelo',
            'default' => 'N/A',
            'mobile' => false,
        ],
        [
            'key' => 'numero_serie',
            'label' => 'Número de serie',
            'width' => '175px',
            'sort' => 'numero_serie',
            'default' => 'NA',
            'mobileLabel' => 'SN',
        ],
        [
            'key' => 'service_tag',
            'label' => 'Service Tag',
            'default' => 'NA',
            'desktop' => false,
        ],
        [
            'key' => 'direccion_ip',
            'label' => 'Dirección IP',
            'width' => '125px',
            'sort' => 'direccion_ip',
            'default' => 'NA',
            'mobile' => 'filled',
            'mobileLabel' => 'IP',
        ],
        [
            'key' => 'estado_nombre',
            'label' => 'Estado',
            'width' => '125px',
            'sort' => 'estado',
            'type' => 'status',
            'default' => 'Sin estado',
        ],
        [
            'key' => 'ubicacion_organizacional',
            'label' => $etiquetaUnidad.' / Departamento',
            'width' => '225px',
            'sort' => 'ubicacion_organizacional',
        ],
    ]"

    :mobile-title="fn ($equipo) => trim(implode(' ', array_filter([
        $equipo->tipo_equipo_nombre ?? 'Equipo',
        $equipo->marca_nombre,
        $equipo->modelo_nombre,
    ])))"
    :mobile-subtitle="fn ($equipo) => $equipo->codigo_inventario"

    :actions="function ($equipo) use ($equipoDadoDeBaja) {
        $baja = $equipoDadoDeBaja($equipo);

        return [
            'detail' => route('equipos.show', $equipo->id_equipo),
            'edit' => $baja ? null : route('equipos.edit', $equipo->id_equipo),
            'menu' => $baja ? [] : [
                [
                    'label' => 'Editar',
                    'href' => route('equipos.edit', $equipo->id_equipo),
                ],
                [
                    'label' => 'Dar de baja',
                    'href' => route('equipos.baja', ['equipo' => $equipo->id_equipo]),
                    'danger' => true,
                ],
            ],
        ];
    }"
/>
