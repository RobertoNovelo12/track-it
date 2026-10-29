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
                'unidades_organizativas as hotel_directo',
                'hotel_directo.id_unidad',
                '=',
                'e.id_unidad'
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

                                WHEN hotel_directo.nombre IS NOT NULL

                                    THEN hotel_directo.nombre
                                        || ' / '
                                        || dep.nombre

                                ELSE dep.nombre

                            END

                        ELSE hotel_directo.nombre

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

        /*
         * Búsqueda tolerante: sin acentos, ignora signos tecleados por
         * accidente y compara códigos y series sin guiones. Si no queda
         * ningún término útil, no se muestran los equipos automáticamente.
         */
        $columnas = [
            'e.nombre_equipo',
            'e.id_equipo',
            'e.codigo_inventario',
            'e.numero_serie',
            'te.nombre',
            'ma.nombre',
            'mo.nombre',
        ];

        /*
         * Software instalado (Administración › Programas): nombre y
         * empresa de los programas del equipo, p. ej. «office», «adobe».
         */
        if (app(\App\Services\ProgramasService::class)->disponible()) {
            $columnas[] = "(SELECT string_agg(p_busqueda.nombre || ' ' || COALESCE(p_busqueda.empresa, ''), ' ')
                FROM equipo_programa AS ep_busqueda
                JOIN programas AS p_busqueda ON p_busqueda.id_programa = ep_busqueda.id_programa
                WHERE ep_busqueda.id_equipo = e.id_equipo)";
        }

        $hayTerminos = \App\Support\BusquedaTolerante::aplicar(
            $query,
            (string) $this->search,
            $columnas,
            [
                'e.codigo_inventario',
                'e.numero_serie',
            ]
        );

        if (! $hayTerminos) {
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