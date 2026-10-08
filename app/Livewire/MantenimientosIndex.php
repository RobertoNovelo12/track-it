<?php

namespace App\Livewire;

use App\Models\CatalogoValor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MantenimientosIndex extends Component
{
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
    public string $areaDepartamento = '';
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
    public string $filtroAreaDepartamento = '';
    public string $filtroNumeroSerie = '';

    public int $perPage = 10;

    public string $sort = 'desc';

    public function aplicarFiltros(): void
    {
        $this->filtroTipo = $this->tipo;
        $this->filtroEstado = $this->estado;
        $this->filtroTecnico = $this->tecnico;
        $this->filtroEquipo = trim($this->equipo);
        $this->filtroFechaDesde = $this->fechaDesde;
        $this->filtroFechaHasta = $this->fechaHasta;
        $this->filtroAreaDepartamento = $this->areaDepartamento;
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
            'areaDepartamento',
            'numeroSerie',

            'filtroTipo',
            'filtroEstado',
            'filtroTecnico',
            'filtroEquipo',
            'filtroFechaDesde',
            'filtroFechaHasta',
            'filtroAreaDepartamento',
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
        | Área / Departamento
        |--------------------------------------------------------------------------
        */

        if ($this->filtroAreaDepartamento !== '') {
            [$tipoFiltro, $id] = array_pad(
                explode(
                    ':',
                    $this->filtroAreaDepartamento,
                    2
                ),
                2,
                null
            );

            if ($tipoFiltro === 'area' && $id) {
                $query->where(
                    'e.id_area',
                    (int) $id
                );
            }

            if ($tipoFiltro === 'departamento' && $id) {
                $query->where(
                    'e.id_departamento',
                    (int) $id
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        $mantenimientos = $query
            ->orderBy(
                'm.fecha_intervencion',
                $this->sort
            )
            ->orderBy(
                'm.id_mantenimiento',
                $this->sort
            )
            ->paginate($this->perPage);

        /*
        |--------------------------------------------------------------------------
        | Opciones para filtros
        |--------------------------------------------------------------------------
        */

        $tiposMantenimiento = CatalogoValor::deCatalogo(
            'tipo_mantenimiento'
        )->get([
            'id_valor',
            'nombre',
        ]);

        $estadosMantenimiento = CatalogoValor::deCatalogo(
            'estado_mantenimiento'
        )->get([
            'id_valor',
            'nombre',
        ]);

        /*
         * La BD relaciona id_tecnico directamente con usuarios.
         * Por ahora no asumimos un rol "Técnico" específico.
         */
        $tecnicos = DB::table('usuarios')
            ->select([
                'id_usuario',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
            ])
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();

        $areas = DB::table('areas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_area',
                'nombre',
            ]);

        $departamentos = DB::table('departamentos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_departamento',
                'nombre',
            ]);

        return view('livewire.mantenimientos-index', [
            'mantenimientos' => $mantenimientos,
            'tiposMantenimiento' => $tiposMantenimiento,
            'estadosMantenimiento' => $estadosMantenimiento,
            'tecnicos' => $tecnicos,
            'areas' => $areas,
            'departamentos' => $departamentos,
        ]);
    }
}