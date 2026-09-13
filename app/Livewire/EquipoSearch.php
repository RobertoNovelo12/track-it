<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EquipoSearch extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';


    /*
    |--------------------------------------------------------------------------
    | Búsqueda
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | /buscar-equipos?q=laptop
    |
    */

    #[Url(as: 'q')]
    public string $search = '';


    /*
    |--------------------------------------------------------------------------
    | Reiniciar paginación
    |--------------------------------------------------------------------------
    */

    public function updatingSearch(): void
    {
        $this->resetPage();
    }


    /*
    |--------------------------------------------------------------------------
    | Limpiar búsqueda
    |--------------------------------------------------------------------------
    */

    public function clearSearch(): void
    {
        $this->search = '';

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder()
    {
        return view('livewire.placeholders.equipo-search');
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    

    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Consulta base
        |--------------------------------------------------------------------------
        */

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
                'estados_equipo as ee',
                'ee.id_estado_equipo',
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
                'areas as area_dep',
                'area_dep.id_area',
                '=',
                'dep.id_area'
            )

            ->leftJoin(
                'areas as area_directa',
                'area_directa.id_area',
                '=',
                'e.id_area'
            )

            ->select([

                'e.id_equipo',

                'e.codigo_inventario',

                'e.nombre_equipo',

                'e.numero_serie',

                'e.service_tag',

                'e.host',

                'te.nombre as tipo_equipo',

                'ma.nombre as marca',

                'mo.nombre as modelo',

                'ee.nombre as estado',

            ])

            ->selectRaw("
                COALESCE(
                    CASE

                        WHEN dep.id_departamento IS NOT NULL

                            THEN CASE

                                WHEN area_dep.nombre IS NOT NULL

                                    THEN area_dep.nombre
                                        || ' / '
                                        || dep.nombre

                                ELSE dep.nombre

                            END

                        ELSE area_directa.nombre

                    END,

                    '—'

                ) as ubicacion
            ");


        /*
        |--------------------------------------------------------------------------
        | Aplicar búsqueda
        |--------------------------------------------------------------------------
        |
        | Cada palabra debe coincidir con por lo menos uno de los campos.
        |
        | Ejemplo:
        |
        |   Lenovo ThinkPad
        |
        | Lenovo   -> marca
        | ThinkPad -> modelo
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


                $query->where(
                    function ($sub) use ($like) {

                        $sub

                            /*
                            |--------------------------------------------------------------------------
                            | Nombre del equipo
                            |--------------------------------------------------------------------------
                            */

                            ->where(
                                'e.nombre_equipo',
                                'ilike',
                                $like
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | ID interno
                            |--------------------------------------------------------------------------
                            */

                            ->orWhereRaw(
                                'CAST(e.id_equipo AS TEXT) ILIKE ?',
                                [$like]
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | Código de inventario
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'e.codigo_inventario',
                                'ilike',
                                $like
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | Número de serie
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'e.numero_serie',
                                'ilike',
                                $like
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | Tipo
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'te.nombre',
                                'ilike',
                                $like
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | Marca
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'ma.nombre',
                                'ilike',
                                $like
                            )


                            /*
                            |--------------------------------------------------------------------------
                            | Modelo
                            |--------------------------------------------------------------------------
                            */

                            ->orWhere(
                                'mo.nombre',
                                'ilike',
                                $like
                            );

                    }
                );
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Si no hay texto, no mostramos los 60+ equipos automáticamente.
            |--------------------------------------------------------------------------
            */

            $query->whereRaw('1 = 0');
        }


        /*
        |--------------------------------------------------------------------------
        | Orden
        |--------------------------------------------------------------------------
        */

        $query
            ->orderBy('e.nombre_equipo')
            ->orderBy('e.codigo_inventario');


        /*
        |--------------------------------------------------------------------------
        | Resultados
        |--------------------------------------------------------------------------
        */

        $equipos = $query->paginate(12);


        return view(
            'livewire.equipo-search',
            [
                'equipos' => $equipos,
            ]
        );
    }
}