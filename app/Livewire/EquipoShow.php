<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EquipoShow extends Component
{
    public int $equipoId;


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(int $equipoId): void
    {
        $this->equipoId = $equipoId;
    }


    /*
    |--------------------------------------------------------------------------
    | Configuración de tablas específicas
    |--------------------------------------------------------------------------
    */

    protected function childConfig(): array
    {
        $baseCompu = [
            'procesador' => 'text',
            'ram_gb' => 'number',
            'almacenamiento_gb' => 'number',
            'id_tipo_almacenamiento' => 'catalog',
            'sistema_operativo' => 'text',
        ];

        return [

            1 => [
                'table' => 'computadoras_escritorio',
                'fields' => $baseCompu,
            ],

            2 => [
                'table' => 'laptops',
                'fields' => $baseCompu + [
                    'incluye_cargador' => 'boolean',
                ],
            ],

            3 => [
                'table' => 'monitores',
                'fields' => [
                    'tamano_pulgadas' => 'number',
                    'id_tipo_conexion' => 'catalog',
                ],
            ],

            4 => [
                'table' => 'tablets',
                'fields' => [
                    'imei' => 'text',
                    'almacenamiento_gb' => 'number',
                    'sistema_operativo' => 'text',
                    'color' => 'text',
                    'incluye_cargador' => 'boolean',
                ],
            ],

            5 => [
                'table' => 'moviles',
                'fields' => [
                    'imei_1' => 'text',
                    'imei_2' => 'text',
                    'numero_telefonico' => 'text',
                    'almacenamiento_gb' => 'number',
                    'sistema_operativo' => 'text',
                    'color' => 'text',
                    'incluye_cargador' => 'boolean',
                ],
            ],

            6 => [
                'table' => 'impresoras',
                'fields' => [
                    'id_tipo_impresora' => 'catalog',
                    'id_tipo_conexion' => 'catalog',
                    'nombre_red' => 'text',
                ],
            ],

            7 => [
                'table' => 'escaneres',
                'fields' => [
                    'id_tipo_conexion' => 'catalog',
                ],
            ],

            8 => [
                'table' => 'grabadores_llaves',
                'fields' => [
                    'id_tipo_conexion' => 'catalog',
                ],
            ],

            9 => [
                'table' => 'tpv',
                'fields' => $baseCompu,
            ],

            10 => [
                'table' => 'switches',
                'fields' => [
                    'numero_puertos' => 'number',
                    'velocidad' => 'text',
                    'administrable' => 'boolean',
                ],
            ],

            11 => [
                'table' => 'routers',
                'fields' => [
                    'numero_puertos' => 'number',
                    'velocidad' => 'text',
                ],
            ],

            12 => [
                'table' => 'access_points',
                'fields' => [
                    'nombre_ap' => 'text',
                ],
            ],

            13 => [
                'table' => 'servidores',
                'fields' => [
                    'id_tipo_servidor' => 'catalog',
                ] + $baseCompu,
            ],

            14 => [
                'table' => 'ups',
                'fields' => [
                    'capacidad_va' => 'number',
                    'fecha_cambio_bateria' => 'date',
                    'id_estado_bateria' => 'catalog',
                ],
            ],

            15 => [
                'table' => 'telefonos',
                'fields' => [
                    'id_tipo_telefono' => 'catalog',
                    'extension' => 'text',
                ],
            ],

            16 => [
                'table' => 'teclados',
                'fields' => [
                    'id_tipo_conexion' => 'catalog',
                ],
            ],

            17 => [
                'table' => 'mouse',
                'fields' => [
                    'id_tipo_conexion' => 'catalog',
                ],
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Etiquetas
    |--------------------------------------------------------------------------
    */

    protected function fieldLabels(): array
    {
        return [

            'procesador' =>
                'Procesador',

            'ram_gb' =>
                'Memoria RAM',

            'almacenamiento_gb' =>
                'Almacenamiento',

            'id_tipo_almacenamiento' =>
                'Tipo de almacenamiento',

            'sistema_operativo' =>
                'Sistema operativo',

            'incluye_cargador' =>
                'Incluye cargador',

            'tamano_pulgadas' =>
                'Tamaño',

            'id_tipo_conexion' =>
                'Tipo de conexión',

            'imei' =>
                'IMEI',

            'imei_1' =>
                'IMEI 1',

            'imei_2' =>
                'IMEI 2',

            'numero_telefonico' =>
                'Número telefónico',

            'color' =>
                'Color',

            'id_tipo_impresora' =>
                'Tipo de impresora',

            'nombre_red' =>
                'Nombre en red',

            'numero_puertos' =>
                'Número de puertos',

            'velocidad' =>
                'Velocidad',

            'administrable' =>
                'Administrable',

            'nombre_ap' =>
                'Nombre del Access Point',

            'id_tipo_servidor' =>
                'Tipo de servidor',

            'capacidad_va' =>
                'Capacidad',

            'fecha_cambio_bateria' =>
                'Cambio de batería',

            'id_estado_bateria' =>
                'Estado de batería',

            'id_tipo_telefono' =>
                'Tipo de teléfono',

            'extension' =>
                'Extensión',

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar equipo
    |--------------------------------------------------------------------------
    |
    | Toda la información general se obtiene en UNA consulta.
    |
    */

    protected function cargarEquipo(): array
    {
        $equipo = DB::table('equipos as e')

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
                'proveedores as pr',
                'pr.id_proveedor',
                '=',
                'e.id_proveedor'
            )

            ->leftJoin(
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
            )

            ->leftJoin(
                'catalogo_valores as condicion',
                'condicion.id_valor',
                '=',
                'e.id_condicion_activo'
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

            ->where(
                'e.id_equipo',
                $this->equipoId
            )

            ->select([

                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.host',
                'e.numero_serie',
                'e.direccion_mac',
                'e.numero_factura',
                'e.fecha_compra',
                'e.fecha_fin_garantia',
                'e.comentarios',

                'e.id_estado_activo',
                'e.id_tipo_equipo',
                'e.id_condicion_activo',
                'e.id_area',
                'e.id_departamento',

                'te.nombre as tipo_equipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'pr.nombre as proveedor',

                'ee.nombre as estado',

                'condicion.nombre as condicion',

                'dep.nombre as departamento',

                'area_dep.nombre as area_departamento',

                'area_directa.nombre as area_directa',

            ])

            ->first();


        abort_unless($equipo, 404);


        $data = (array) $equipo;


        /*
        |--------------------------------------------------------------------------
        | Ubicación
        |--------------------------------------------------------------------------
        */

        if (!empty($data['departamento'])) {

            $data['ubicacion'] =
                !empty($data['area_departamento'])
                    ? $data['area_departamento']
                        . ' / '
                        . $data['departamento']
                    : $data['departamento'];

        } elseif (!empty($data['area_directa'])) {

            $data['ubicacion'] =
                $data['area_directa'];

        } else {

            $data['ubicacion'] = '—';
        }


        $data['estado'] =
            $data['estado']
            ?? 'Sin estado';


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Especificaciones técnicas
    |--------------------------------------------------------------------------
    */

    protected function cargarEspecificaciones(
        array $equipo
    ): array {

        $tipoId =
            (int) (
                $equipo['id_tipo_equipo']
                ?? 0
            );


        $config =
            $this->childConfig()[$tipoId]
            ?? null;


        if (!$config) {
            return [];
        }


        $tabla =
            $config['table'];


        /*
        |--------------------------------------------------------------------------
        | Consulta a la tabla específica
        |--------------------------------------------------------------------------
        */

        $row = DB::table($tabla)
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->first();


        if (!$row) {
            return [];
        }


        $row =
            (array) $row;


        /*
        |--------------------------------------------------------------------------
        | Obtenemos todos los IDs de catálogo de una sola vez
        |--------------------------------------------------------------------------
        */

        $catalogIds = [];


        foreach (
            $config['fields'] as $campo => $tipo
        ) {

            if (
                $tipo === 'catalog' &&
                !empty($row[$campo])
            ) {

                $catalogIds[] =
                    (int) $row[$campo];
            }
        }


        $catalogos = [];


        if (!empty($catalogIds)) {

            $catalogos =
                DB::table('catalogo_valores')

                    ->whereIn(
                        'id_valor',
                        array_unique($catalogIds)
                    )

                    ->pluck(
                        'nombre',
                        'id_valor'
                    )

                    ->all();
        }


        /*
        |--------------------------------------------------------------------------
        | Construir especificaciones
        |--------------------------------------------------------------------------
        */

        $labels =
            $this->fieldLabels();


        $resultado = [];


        foreach (
            $config['fields']
            as $campo => $tipo
        ) {

            $valor =
                $row[$campo]
                ?? null;


            $resultado[] = [

                'campo' =>
                    $campo,

                'label' =>
                    $labels[$campo]
                    ?? $campo,

                'value' =>
                    $this->formatearEspecificacion(
                        $campo,
                        $valor,
                        $tipo,
                        $catalogos
                    ),

            ];
        }


        return $resultado;
    }


    /*
    |--------------------------------------------------------------------------
    | Formatear especificaciones
    |--------------------------------------------------------------------------
    */

    protected function formatearEspecificacion(
        string $campo,
        mixed $valor,
        string $tipo,
        array $catalogos
    ): string {

        if (
            $valor === null ||
            $valor === ''
        ) {
            return '—';
        }


        /*
        |--------------------------------------------------------------------------
        | Catálogo
        |--------------------------------------------------------------------------
        */

        if ($tipo === 'catalog') {

            return
                $catalogos[(int) $valor]
                ?? '—';
        }


        /*
        |--------------------------------------------------------------------------
        | Booleano
        |--------------------------------------------------------------------------
        */

        if ($tipo === 'boolean') {

            return filter_var(
                $valor,
                FILTER_VALIDATE_BOOLEAN
            )
                ? 'Sí'
                : 'No';
        }


        /*
        |--------------------------------------------------------------------------
        | Fecha
        |--------------------------------------------------------------------------
        */

        if ($tipo === 'date') {

            try {

                return \Carbon\Carbon::parse(
                    $valor
                )->format('d/m/Y');

            } catch (\Throwable) {

                return (string) $valor;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Unidades
        |--------------------------------------------------------------------------
        */

        if ($campo === 'ram_gb') {

            return $valor . ' GB';
        }


        if ($campo === 'almacenamiento_gb') {

            return $valor . ' GB';
        }


        if ($campo === 'tamano_pulgadas') {

            return $valor . '"';
        }


        if ($campo === 'capacidad_va') {

            return $valor . ' VA';
        }


        return (string) $valor;
    }


    /*
    |--------------------------------------------------------------------------
    | Asignaciones / movimientos
    |--------------------------------------------------------------------------
    |
    | Antes se hacían consultas adicionales por cada movimiento.
    | Ahora todos los datos vienen mediante JOIN en una sola consulta.
    |
    */

    protected function cargarAsignaciones(): array
    {
        $rows = DB::table(
            'asignaciones as asi'
        )

            ->leftJoin(
                'areas as a',
                'a.id_area',
                '=',
                'asi.id_area'
            )

            ->leftJoin(
                'departamentos as dep',
                'dep.id_departamento',
                '=',
                'asi.id_departamento'
            )

            ->leftJoin(
                'catalogo_valores as tipo',
                'tipo.id_valor',
                '=',
                'asi.id_tipo_asignacion'
            )

            ->where(
                'asi.id_equipo',
                $this->equipoId
            )

            ->orderByDesc(
                'asi.fecha_asignacion'
            )

            ->limit(5)

            ->select([

                'asi.nombre_colaborador',
                'asi.fecha_asignacion',
                'asi.id_area',
                'asi.id_departamento',
                'asi.id_tipo_asignacion',

                'a.nombre as area_nombre',

                'dep.nombre as departamento_nombre',

                'tipo.nombre as tipo_asignacion',

            ])

            ->get();


        return $rows
            ->map(function ($row) {

                $row =
                    (array) $row;


                /*
                |--------------------------------------------------------------------------
                | Ubicación
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $row['departamento_nombre']
                    )
                ) {

                    $ubicacion =
                        !empty($row['area_nombre'])
                            ? $row['area_nombre']
                                . ' / '
                                . $row['departamento_nombre']
                            : $row['departamento_nombre'];

                } else {

                    $ubicacion =
                        $row['area_nombre']
                        ?? '—';
                }


                return [

                    'nombre_colaborador' =>
                        $row['nombre_colaborador']
                        ?? '—',

                    'fecha_asignacion' =>
                        $row['fecha_asignacion']
                        ?? null,

                    'tipo_asignacion' =>
                        $row['tipo_asignacion']
                        ?? 'Asignación',

                    'ubicacion' =>
                        $ubicacion,

                ];

            })

            ->values()

            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder()
    {
        return view(
            'livewire.placeholders.equipo-show'
        );
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
        | 1. Equipo
        |--------------------------------------------------------------------------
        */

        $equipo =
            $this->cargarEquipo();


        /*
        |--------------------------------------------------------------------------
        | 2. Especificaciones
        |--------------------------------------------------------------------------
        */

        $especificaciones =
            $this->cargarEspecificaciones(
                $equipo
            );


        /*
        |--------------------------------------------------------------------------
        | 3. Asignaciones
        |--------------------------------------------------------------------------
        */

        $asignaciones =
            $this->cargarAsignaciones();


        /*
        |--------------------------------------------------------------------------
        | Asignación actual
        |--------------------------------------------------------------------------
        */

        $asignacionActual =
            $asignaciones[0]
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Movimientos
        |--------------------------------------------------------------------------
        */

        $movimientos =
            collect($asignaciones)

                ->map(
                    fn ($asignacion) => [

                        'fecha' =>
                            $asignacion[
                                'fecha_asignacion'
                            ]
                            ?? null,

                        'operacion' =>
                            $asignacion[
                                'tipo_asignacion'
                            ]
                            ?? 'Asignación',

                        'responsable' =>
                            $asignacion[
                                'nombre_colaborador'
                            ]
                            ?? '—',

                        'ubicacion' =>
                            $asignacion[
                                'ubicacion'
                            ]
                            ?? '—',

                    ]
                )

                ->all();


        /*
        |--------------------------------------------------------------------------
        | Mantenimientos
        |--------------------------------------------------------------------------
        */

        $mantenimientos = [];


        return view(
            'livewire.equipo-show',
            compact(
                'equipo',
                'especificaciones',
                'asignacionActual',
                'movimientos',
                'mantenimientos'
            )
        );
    }
}