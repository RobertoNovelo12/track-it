<?php

namespace App\Livewire;

use App\Models\Area;
use App\Models\CatalogoValor;
use App\Models\Equipo;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\TipoEquipo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EquiposIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';


    /*
    |--------------------------------------------------------------------------
    | BÚSQUEDA GENERAL
    |--------------------------------------------------------------------------
    |
    | Busca simultáneamente por:
    | - Tipo / nombre del equipo
    | - Número de serie
    | - Marca
    | - Modelo
    |
    */

    #[Url(as: 'buscar')]
    public string $search = '';


    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    #[Url(as: 'tipo_activo')]
    public string $tipoActivo = '';

    #[Url]
    public string $marca = '';

    #[Url]
    public string $modelo = '';

    #[Url(as: 'numero_serie')]
    public string $numeroSerie = '';

    #[Url(as: 'service_tag')]
    public string $serviceTag = '';

    #[Url(as: 'direccion_ip')]
    public string $direccionIp = '';

    #[Url]
    public string $estado = '';

    #[Url]
    public string $area = '';

    #[Url(as: 'per_page')]
    public int $perPage = 10;

    #[Url]
    public string $sort = 'asc';


    /*
    |--------------------------------------------------------------------------
    | RESETEAR PAGINACIÓN AL CAMBIAR BÚSQUEDA / FILTROS
    |--------------------------------------------------------------------------
    */

    public function updating(string $property): void
    {
        if (in_array($property, [
            'search',
            'tipoActivo',
            'marca',
            'modelo',
            'numeroSerie',
            'serviceTag',
            'direccionIp',
            'estado',
            'area',
            'perPage',
        ])) {
            $this->resetPage();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR FILTROS
    |--------------------------------------------------------------------------
    |
    | Importante:
    | No limpiamos "search" aquí porque la búsqueda principal es independiente
    | de los filtros del bottom sheet.
    |
    */

    public function resetFilters(): void
    {
        $this->reset([
            'tipoActivo',
            'marca',
            'modelo',
            'numeroSerie',
            'serviceTag',
            'direccionIp',
            'estado',
            'area',
        ]);

        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | PLACEHOLDER
    |--------------------------------------------------------------------------
    */

    public function placeholder()
    {
        return view('livewire.placeholders.equipos-index');
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $query = DB::table('equipos as e')

            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )

            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )

            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )

            ->leftJoin(
                'catalogo_valores as cv',
                'cv.id_valor',
                '=',
                'e.id_estado_activo'
            )

            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'e.id_departamento'
            )

            ->leftJoin(
                'areas as a_dep',
                'a_dep.id_area',
                '=',
                'dep.id_area'
            )

            ->leftJoin(
                'areas as a_dir',
                'a_dir.id_area',
                '=',
                'e.id_area'
            )

            ->select([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.numero_serie',
                'e.service_tag',
                'e.direccion_ip',

                'te.nombre as tipo_equipo_nombre',
                'ma.nombre as marca_nombre',
                'mo.nombre as modelo_nombre',
                'cv.nombre as estado_nombre',
            ])

            ->selectRaw("
                COALESCE(
                    CASE
                        WHEN dep.id_departamento IS NOT NULL
                            THEN CASE
                                WHEN a_dep.nombre IS NOT NULL
                                    THEN a_dep.nombre || ' / ' || dep.nombre
                                ELSE dep.nombre
                            END
                        ELSE a_dir.nombre
                    END,
                    '—'
                ) as ubicacion_organizacional
            ");


        /*
        |--------------------------------------------------------------------------
        | BÚSQUEDA GENERAL
        |--------------------------------------------------------------------------
        |
        | Separamos el texto por palabras.
        |
        | Ejemplo:
        |
        |   "Laptop Dell Latitude"
        |
        | puede coincidir así:
        |
        |   Laptop   -> tipos_equipo.nombre
        |   Dell     -> marcas.nombre
        |   Latitude -> modelos.nombre
        |
        | Cada palabra debe existir en por lo menos uno de los cuatro campos.
        |
        */

        $search = trim($this->search);

        if ($search !== '') {

            $terms = preg_split(
                '/\s+/',
                $search,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            foreach ($terms as $term) {

                $like = '%' . $term . '%';

                $query->where(function ($sub) use ($like) {

                    $sub
                        ->where(
                            'te.nombre',
                            'ilike',
                            $like
                        )

                        ->orWhere(
                            'e.numero_serie',
                            'ilike',
                            $like
                        )

                        ->orWhere(
                            'ma.nombre',
                            'ilike',
                            $like
                        )

                        ->orWhere(
                            'mo.nombre',
                            'ilike',
                            $like
                        );

                });
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTROS ESPECÍFICOS
        |--------------------------------------------------------------------------
        */

        $query->when(
            $this->tipoActivo !== '',
            fn ($q) =>
                $q->where(
                    'e.id_tipo_equipo',
                    (int) $this->tipoActivo
                )
        );


        $query->when(
            $this->marca !== '',
            fn ($q) =>
                $q->where(
                    'e.id_marca',
                    (int) $this->marca
                )
        );


        $query->when(
            $this->modelo !== '',
            fn ($q) =>
                $q->where(
                    'e.id_modelo',
                    (int) $this->modelo
                )
        );


        /*
         * Número de serie sigue existiendo como filtro específico.
         * Es independiente del buscador general.
         */
        $query->when(
            $this->numeroSerie !== '',
            fn ($q) =>
                $q->where(
                    'e.numero_serie',
                    'ilike',
                    '%' . $this->numeroSerie . '%'
                )
        );


        $query->when(
            $this->serviceTag !== '',
            fn ($q) =>
                $q->where(
                    'e.service_tag',
                    'ilike',
                    '%' . $this->serviceTag . '%'
                )
        );


        $query->when(
            $this->direccionIp !== '',
            fn ($q) =>
                $q->where(
                    'e.direccion_ip',
                    'ilike',
                    '%' . $this->direccionIp . '%'
                )
        );


        $query->when(
            $this->estado !== '',
            fn ($q) =>
                $q->where(
                    'e.id_estado_activo',
                    (int) $this->estado
                )
        );


        /*
         * El equipo puede estar relacionado con:
         *
         * - Área directamente
         * - Departamento perteneciente a un área
         */
        $query->when(
            $this->area !== '',
            function ($q) {

                $areaId = (int) $this->area;

                $q->where(function ($sub) use ($areaId) {

                    $sub
                        ->where(
                            'a_dir.id_area',
                            $areaId
                        )

                        ->orWhere(
                            'dep.id_area',
                            $areaId
                        );

                });
            }
        );


        /*
        |--------------------------------------------------------------------------
        | ORDENAMIENTO
        |--------------------------------------------------------------------------
        */

        $sortDirection =
            $this->sort === 'desc'
                ? 'desc'
                : 'asc';

        $query->orderBy(
            'e.codigo_inventario',
            $sortDirection
        );


        /*
        |--------------------------------------------------------------------------
        | PAGINACIÓN
        |--------------------------------------------------------------------------
        */

        $perPage = in_array(
            $this->perPage,
            [10, 25, 50, 100]
        )
            ? $this->perPage
            : 10;


        /*
        |--------------------------------------------------------------------------
        | EJECUTAR CONSULTA
        |--------------------------------------------------------------------------
        */

        $inicio = microtime(true);

        $equipos = $query->paginate($perPage);

        \Illuminate\Support\Facades\Log::info(
            'Tiempo consulta equipos: '
            . round(
                (microtime(true) - $inicio) * 1000
            )
            . ' ms'
        );


        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view('livewire.equipos-index', [

            'equipos' => $equipos,


            /*
             * Se cachean como arrays planos para evitar problemas
             * de serialización entre peticiones Livewire.
             */

            'tiposActivo' => Cache::remember(
                'filtros.tipos_equipo',
                now()->addMinutes(30),
                fn () =>
                    TipoEquipo::where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_tipo_equipo',
                            'nombre',
                        ])
                        ->toArray()
            ),


            'marcas' => Cache::remember(
                'filtros.marcas',
                now()->addMinutes(30),
                fn () =>
                    Marca::where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_marca',
                            'nombre',
                        ])
                        ->toArray()
            ),


            'modelos' => Cache::remember(
                'filtros.modelos',
                now()->addMinutes(30),
                fn () =>
                    Modelo::where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_modelo',
                            'nombre',
                        ])
                        ->toArray()
            ),


            'serviceTags' => Cache::remember(
                'filtros.service_tags',
                now()->addMinutes(10),
                fn () =>
                    Equipo::whereNotNull('service_tag')
                        ->distinct()
                        ->orderBy('service_tag')
                        ->pluck('service_tag')
                        ->toArray()
            ),


            'direccionesIp' => Cache::remember(
                'filtros.direcciones_ip',
                now()->addMinutes(10),
                fn () =>
                    Equipo::whereNotNull('direccion_ip')
                        ->distinct()
                        ->orderBy('direccion_ip')
                        ->pluck('direccion_ip')
                        ->toArray()
            ),


            'estados' => Cache::remember(
                'filtros.estados_activo',
                now()->addMinutes(30),
                fn () =>
                    CatalogoValor::deCatalogo('estado_activo')
                        ->get([
                            'id_valor',
                            'nombre',
                        ])
                        ->toArray()
            ),


            'areas' => Cache::remember(
                'filtros.areas',
                now()->addMinutes(30),
                fn () =>
                    Area::where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_area',
                            'nombre',
                        ])
                        ->toArray()
            ),

        ]);
    }
}