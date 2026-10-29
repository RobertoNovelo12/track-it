<?php

namespace App\Livewire;

use App\Models\CatalogoValor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MantenimientosIndex extends Component
{
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;

    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filtros visibles
    |--------------------------------------------------------------------------
    */

    public string $tipo = '';
    public string $estado = '';
    public string $tecnico = '';
    public string $equipo = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $hotelDepartamento = '';
    public string $numeroSerie = '';

    /*
    |--------------------------------------------------------------------------
    | Filtros aplicados
    |--------------------------------------------------------------------------
    |
    | Evitamos consultar la BD mientras el usuario escribe.
    | La consulta se actualiza al presionar "Aplicar filtros".
    |
    */

    public string $filtroTipo = '';
    public string $filtroEstado = '';
    public string $filtroTecnico = '';
    public string $filtroEquipo = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';
    public string $filtroHotelDepartamento = '';
    public string $filtroNumeroSerie = '';

    public int $perPage = 10;

    public string $sort = 'desc';

    /*
    |--------------------------------------------------------------------------
    | Ordenamiento por columna
    |--------------------------------------------------------------------------
    |
    | Clave pública => expresión SQL segura.
    |
    */

    private const SORTABLE_COLUMNS = [
        'folio' => 'm.folio',
        'tipo' => 'tipo_mantenimiento.nombre',
        'marca' => 'marca.nombre',
        'modelo' => 'modelo.nombre',
        'numero_serie' => 'e.numero_serie',
        'intervencion' => 'm.intervencion_realizada',
        'equipo' => 'e.nombre_equipo',
        'fecha' => 'm.fecha_intervencion',
        'tecnico' => 'tecnico.nombres',
        'estado' => 'estado_mantenimiento.nombre',
    ];

    public string $sortField = 'fecha';

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, self::SORTABLE_COLUMNS)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sort = 'asc';
        }

        $this->resetPage();
    }

    public function aplicarFiltros(): void
    {
        $this->filtroTipo = $this->tipo;
        $this->filtroEstado = $this->estado;
        $this->filtroTecnico = $this->tecnico;
        $this->filtroEquipo = trim($this->equipo);
        $this->filtroFechaDesde = $this->fechaDesde;
        $this->filtroFechaHasta = $this->fechaHasta;
        $this->filtroHotelDepartamento = $this->hotelDepartamento;
        $this->filtroNumeroSerie = trim($this->numeroSerie);

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset([
            'tipo',
            'estado',
            'tecnico',
            'equipo',
            'fechaDesde',
            'fechaHasta',
            'hotelDepartamento',
            'numeroSerie',

            'filtroTipo',
            'filtroEstado',
            'filtroTecnico',
            'filtroEquipo',
            'filtroFechaDesde',
            'filtroFechaHasta',
            'filtroHotelDepartamento',
            'filtroNumeroSerie',
        ]);

        $this->resetPage();

        $this->dispatch(
            'mantenimientos-filtros-limpiados'
        );
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function updatedSort(): void
    {
        if (! in_array($this->sort, ['asc', 'desc'], true)) {
            $this->sort = 'desc';
        }

        $this->resetPage();
    }

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Consulta principal
        |--------------------------------------------------------------------------
        */

        $query = DB::table('mantenimientos as m')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'm.id_equipo'
            )
            ->leftJoin(
                'catalogo_valores as tipo_mantenimiento',
                'tipo_mantenimiento.id_valor',
                '=',
                'm.id_tipo_mantenimiento'
            )
            ->leftJoin(
                'catalogo_valores as estado_mantenimiento',
                'estado_mantenimiento.id_valor',
                '=',
                'm.id_estado_mantenimiento'
            )
            ->leftJoin(
                'catalogo_valores as tipo_intervencion',
                'tipo_intervencion.id_valor',
                '=',
                'm.id_tipo_intervencion'
            )
            ->leftJoin(
                'usuarios as tecnico',
                'tecnico.id_usuario',
                '=',
                'm.id_tecnico'
            )
            ->leftJoin(
                'tipos_equipo as tipo_equipo',
                'tipo_equipo.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as marca',
                'marca.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as modelo',
                'modelo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->select([
                'm.id_mantenimiento',
                'm.folio',
                'm.fecha_intervencion',
                'm.intervencion_realizada',

                'tipo_mantenimiento.nombre as tipo_mantenimiento',
                'tipo_intervencion.nombre as tipo_intervencion',
                'estado_mantenimiento.nombre as estado_mantenimiento',

                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',

                'tipo_equipo.nombre as tipo_equipo',
                'marca.nombre as marca',
                'modelo.nombre as modelo',

                DB::raw("
                    TRIM(
                        CONCAT_WS(
                            ' ',
                            tecnico.nombres,
                            tecnico.apellido_paterno,
                            tecnico.apellido_materno
                        )
                    ) AS tecnico_responsable
                "),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Filtro: tipo
        |--------------------------------------------------------------------------
        */

        if ($this->filtroTipo !== '') {
            $query->where(
                'm.id_tipo_mantenimiento',
                (int) $this->filtroTipo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro: estado
        |--------------------------------------------------------------------------
        */

        if ($this->filtroEstado !== '') {
            $query->where(
                'm.id_estado_mantenimiento',
                (int) $this->filtroEstado
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro: técnico
        |--------------------------------------------------------------------------
        */

        if ($this->filtroTecnico !== '') {
            $query->where(
                'm.id_tecnico',
                (int) $this->filtroTecnico
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro: equipo
        |--------------------------------------------------------------------------
        */

        if ($this->filtroEquipo !== '') {
            $buscarEquipo = '%' . $this->filtroEquipo . '%';

            $query->where(function ($q) use ($buscarEquipo) {
                $q
                    ->where(
                        'e.codigo_inventario',
                        'ilike',
                        $buscarEquipo
                    )
                    ->orWhere(
                        'e.nombre_equipo',
                        'ilike',
                        $buscarEquipo
                    )
                    ->orWhere(
                        'e.service_tag',
                        'ilike',
                        $buscarEquipo
                    )
                    ->orWhere(
                        'e.host',
                        'ilike',
                        $buscarEquipo
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filtro: número de serie
        |--------------------------------------------------------------------------
        */

        if ($this->filtroNumeroSerie !== '') {
            $query->where(
                'e.numero_serie',
                'ilike',
                '%' . $this->filtroNumeroSerie . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filtros de fecha
        |--------------------------------------------------------------------------
        */

        if ($this->filtroFechaDesde !== '') {
            $query->whereDate(
                'm.fecha_intervencion',
                '>=',
                $this->filtroFechaDesde
            );
        }

        if ($this->filtroFechaHasta !== '') {
            $query->whereDate(
                'm.fecha_intervencion',
                '<=',
                $this->filtroFechaHasta
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Hotel / Departamento
        |--------------------------------------------------------------------------
        */

        if ($this->filtroHotelDepartamento !== '') {
            [$tipoFiltro, $id] = array_pad(
                explode(
                    ':',
                    $this->filtroHotelDepartamento,
                    2
                ),
                2,
                null
            );

            if ($tipoFiltro === 'hotel' && $id) {
                $query->where(
                    'e.id_unidad',
                    (int) $id
                );
            }

            if ($tipoFiltro === 'departamento' && $id) {
                $query->whereIn('e.id_departamento', function ($departamentos) use ($id) {
                    $departamentos->select('id_departamento')->from('departamentos')
                        ->where('id_departamento', (int) $id)
                        ->orWhere('id_departamento_principal', (int) $id);
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        $columnaOrden = self::SORTABLE_COLUMNS[$this->sortField]
            ?? self::SORTABLE_COLUMNS['fecha'];

        $direccion = $this->sort === 'asc' ? 'asc' : 'desc';

        /*
         * Listado en caché (módulo «mantenimientos»): se renueva al crear o
         * editar un mantenimiento, y también si cambian equipos o catálogos
         * (nombres que muestra la tabla). Antes: 2 consultas por carga.
         */
        $perPage = max(1, (int) $this->perPage);
        $page = max(1, (int) $this->getPage());
        $cache = app(\App\Services\SystemCacheService::class);

        $datos = $cache->remember(
            'mantenimientos',
            'index.listado.v2',
            [
                'tipo' => $this->filtroTipo,
                'estado' => $this->filtroEstado,
                'tecnico' => $this->filtroTecnico,
                'equipo' => $this->filtroEquipo,
                'serie' => $this->filtroNumeroSerie,
                'desde' => $this->filtroFechaDesde,
                'hasta' => $this->filtroFechaHasta,
                'hotel_departamento' => $this->filtroHotelDepartamento,
                'orden' => $columnaOrden,
                'direccion' => $direccion,
                'per_page' => $perPage,
                'page' => $page,
                'equipos_version' => $cache->moduleVersion('equipos'),
                'catalogos_version' => $cache->moduleVersion('catalogos'),
            ],
            function () use ($query, $columnaOrden, $direccion, $perPage, $page): array {
                $total = (clone $query)->count();

                $items = $query
                    ->orderByRaw($columnaOrden.' '.$direccion.' NULLS LAST')
                    ->orderBy('m.id_mantenimiento', $direccion)
                    ->forPage($page, $perPage)
                    ->get()
                    ->map(fn ($fila): array => (array) $fila)
                    ->all();

                return [
                    'items' => $items,
                    'total' => (int) $total,
                ];
            },
            12
        );

        $mantenimientos = new \Illuminate\Pagination\LengthAwarePaginator(
            array_map(fn (array $fila): object => (object) $fila, $datos['items']),
            $datos['total'],
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Opciones para filtros
        |--------------------------------------------------------------------------
        */

        /*
         * Opciones de los filtros en caché. Catálogos, áreas y departamentos
         * se renuevan al editarlos en Administración (módulo «catalogos»).
         * Los técnicos (usuarios) se guardan 10 minutos.
         */
        $aObjetos = fn (array $filas) => collect($filas)->map(fn (array $fila): object => (object) $fila);

        $tiposMantenimiento = $aObjetos($cache->rememberSimple(
            'catalogos',
            'mantenimientos.filtros.tipos.v1',
            fn (): array => CatalogoValor::deCatalogo('tipo_mantenimiento')
                ->get(['id_valor', 'nombre'])
                ->map(fn ($item): array => ['id_valor' => (int) $item->id_valor, 'nombre' => (string) $item->nombre])
                ->all(),
            24
        ));

        $estadosMantenimiento = $aObjetos($cache->rememberSimple(
            'catalogos',
            'mantenimientos.filtros.estados.v1',
            fn (): array => CatalogoValor::deCatalogo('estado_mantenimiento')
                ->get(['id_valor', 'nombre'])
                ->map(fn ($item): array => ['id_valor' => (int) $item->id_valor, 'nombre' => (string) $item->nombre])
                ->all(),
            24
        ));

        /*
         * La BD relaciona id_tecnico directamente con usuarios.
         * Por ahora no asumimos un rol "Técnico" específico.
         */
        $tecnicos = $aObjetos(\Illuminate\Support\Facades\Cache::remember(
            'trackit.mantenimientos.filtros.tecnicos.v1',
            now()->addMinutes(10),
            fn (): array => DB::table('usuarios')
                ->select(['id_usuario', 'nombres', 'apellido_paterno', 'apellido_materno'])
                ->orderBy('nombres')
                ->orderBy('apellido_paterno')
                ->get()
                ->map(fn ($fila): array => (array) $fila)
                ->all()
        ));


        $departamentos = $aObjetos($cache->rememberSimple(
            'catalogos',
            'mantenimientos.filtros.departamentos.v2',
            fn (): array => \App\Models\Departamento::principales()->activos()
                ->orderBy('nombre')
                ->get(['id_departamento', 'nombre'])
                ->map(fn ($fila): array => $fila->toArray())
                ->all(),
            24
        ));

        return view('livewire.mantenimientos-index', [
            'mantenimientos' => $mantenimientos,
            'tiposMantenimiento' => $tiposMantenimiento,
            'estadosMantenimiento' => $estadosMantenimiento,
            'tecnicos' => $tecnicos,
            'departamentos' => $departamentos,
        ]);
    }
}
