<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class EquipoShow extends Component
{
    public int $equipoId;


    /*
    |--------------------------------------------------------------------------
    | Recibir ID del equipo
    |--------------------------------------------------------------------------
    */

    public function mount(int $equipoId): void
    {
        $this->equipoId = $equipoId;

        /*
        |--------------------------------------------------------------------------
        | Verificamos desde el inicio que el equipo exista
        |--------------------------------------------------------------------------
        */

        $existe = DB::table('equipos')
            ->where('id_equipo', $this->equipoId)
            ->exists();

        abort_unless($existe, 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Configuración de tablas específicas por tipo de equipo
    |--------------------------------------------------------------------------
    |
    | Debe mantenerse alineada con EquipoCreate.php.
    |
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
    | Etiquetas para especificaciones técnicas
    |--------------------------------------------------------------------------
    */

    protected function fieldLabels(): array
    {
        return [

            'procesador' => 'Procesador',

            'ram_gb' => 'Memoria RAM',

            'almacenamiento_gb' => 'Almacenamiento',

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
    | Información general del equipo
    |--------------------------------------------------------------------------
    */

    public function getEquipoProperty(): array
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

                'e.registrado_por',

                'te.nombre as tipo_equipo',

                'ma.nombre as marca',

                'mo.nombre as modelo',

                'pr.nombre as proveedor',

                'dep.nombre as departamento',

                'area_dep.nombre as area_departamento',

                'area_directa.nombre as area_directa',

            ])

            ->first();


        abort_unless($equipo, 404);


        $data = (array) $equipo;


        /*
        |--------------------------------------------------------------------------
        | Estado
        |--------------------------------------------------------------------------
        |
        | En tu proyecto existe estados_equipo. Lo consultamos aparte para
        | no mezclar esta vista con la estructura antigua del listado.
        |
        */

        $data['estado'] =
            $this->estadoNombre(
                $data['id_estado_activo'] ?? null
            );


        /*
        |--------------------------------------------------------------------------
        | Condición del activo
        |--------------------------------------------------------------------------
        */

        $data['condicion'] =
            $this->catalogoValorNombre(
                $data['id_condicion_activo'] ?? null
            );


        /*
        |--------------------------------------------------------------------------
        | Ubicación organizacional
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


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Estado del equipo
    |--------------------------------------------------------------------------
    */

    protected function estadoNombre(
        int|string|null $id
    ): string {

        if (!$id) {
            return 'Sin estado';
        }


        if (Schema::hasTable('estados_equipo')) {

            $nombre = DB::table('estados_equipo')
                ->where(
                    'id_estado_equipo',
                    (int) $id
                )
                ->value('nombre');

            if ($nombre) {
                return $nombre;
            }
        }


        if (Schema::hasTable('catalogo_valores')) {

            $nombre = DB::table('catalogo_valores')
                ->where(
                    'id_valor',
                    (int) $id
                )
                ->value('nombre');

            if ($nombre) {
                return $nombre;
            }
        }


        return 'Sin estado';
    }


    /*
    |--------------------------------------------------------------------------
    | Especificaciones técnicas
    |--------------------------------------------------------------------------
    */

    public function getEspecificacionesProperty(): array
    {
        $equipo = $this->equipo;

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


        $tabla = $config['table'];


        if (!Schema::hasTable($tabla)) {
            return [];
        }


        $row = DB::table($tabla)
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->first();


        if (!$row) {
            return [];
        }


        $row = (array) $row;

        $labels = $this->fieldLabels();

        $resultado = [];


        foreach (
            $config['fields'] as $campo => $tipo
        ) {

            $valor =
                $row[$campo]
                ?? null;


            $resultado[] = [

                'campo' => $campo,

                'label' =>
                    $labels[$campo]
                    ?? $campo,

                'value' =>
                    $this->formatearValorEspecificacion(
                        $campo,
                        $valor,
                        $tipo
                    ),

            ];
        }


        return $resultado;
    }


    /*
    |--------------------------------------------------------------------------
    | Dar formato a especificaciones
    |--------------------------------------------------------------------------
    */

    protected function formatearValorEspecificacion(
        string $campo,
        mixed $valor,
        string $tipo
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
                $this->catalogoValorNombre($valor)
                ?: '—';
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

                return \Carbon\Carbon::parse($valor)
                    ->format('d/m/Y');

            } catch (\Throwable) {

                return (string) $valor;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Unidades conocidas
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
    | Nombre de un valor de catálogo
    |--------------------------------------------------------------------------
    */

    protected function catalogoValorNombre(
        int|string|null $id
    ): ?string {

        if (!$id) {
            return null;
        }


        return Cache::remember(
            'catalogo.valor.' . $id,
            now()->addMinutes(30),
            fn () =>
                DB::table('catalogo_valores')
                    ->where(
                        'id_valor',
                        (int) $id
                    )
                    ->value('nombre')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Asignación actual
    |--------------------------------------------------------------------------
    */

    public function getAsignacionActualProperty(): ?array
    {
        if (!Schema::hasTable('asignaciones')) {
            return null;
        }


        $asignacion =
            DB::table('asignaciones')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->orderByDesc(
                    'fecha_asignacion'
                )
                ->first();


        if (!$asignacion) {
            return null;
        }


        $data = (array) $asignacion;


        /*
        |--------------------------------------------------------------------------
        | Área
        |--------------------------------------------------------------------------
        */

        $areaNombre = null;

        if (
            !empty($data['id_area']) &&
            Schema::hasTable('areas')
        ) {

            $areaNombre =
                DB::table('areas')
                    ->where(
                        'id_area',
                        (int) $data['id_area']
                    )
                    ->value('nombre');
        }


        /*
        |--------------------------------------------------------------------------
        | Departamento
        |--------------------------------------------------------------------------
        */

        $departamentoNombre = null;

        if (
            !empty($data['id_departamento']) &&
            Schema::hasTable('departamentos')
        ) {

            $departamentoNombre =
                DB::table('departamentos')
                    ->where(
                        'id_departamento',
                        (int) $data['id_departamento']
                    )
                    ->value('nombre');
        }


        if ($departamentoNombre) {

            $data['ubicacion'] =
                $areaNombre
                    ? $areaNombre
                        . ' / '
                        . $departamentoNombre
                    : $departamentoNombre;

        } else {

            $data['ubicacion'] =
                $areaNombre
                ?? '—';
        }


        /*
        |--------------------------------------------------------------------------
        | Tipo de asignación
        |--------------------------------------------------------------------------
        */

        $data['tipo_asignacion'] =
            $this->catalogoValorNombre(
                $data['id_tipo_asignacion']
                ?? null
            );


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Historial de movimientos
    |--------------------------------------------------------------------------
    |
    | Por ahora usamos las asignaciones conocidas como historial.
    | Más adelante, si tienes una tabla específica de movimientos,
    | podemos sustituir esta consulta.
    |
    */

    public function getMovimientosProperty(): array
    {
        if (!Schema::hasTable('asignaciones')) {
            return [];
        }


        $rows =
            DB::table('asignaciones')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->orderByDesc(
                    'fecha_asignacion'
                )
                ->limit(5)
                ->get();


        return $rows
            ->map(function ($row) {

                $row = (array) $row;


                $area = null;

                if (!empty($row['id_area'])) {

                    $area =
                        DB::table('areas')
                            ->where(
                                'id_area',
                                (int) $row['id_area']
                            )
                            ->value('nombre');
                }


                $departamento = null;

                if (!empty($row['id_departamento'])) {

                    $departamento =
                        DB::table('departamentos')
                            ->where(
                                'id_departamento',
                                (int) $row['id_departamento']
                            )
                            ->value('nombre');
                }


                $ubicacion =
                    $departamento
                        ? (
                            $area
                                ? $area
                                    . ' / '
                                    . $departamento
                                : $departamento
                        )
                        : (
                            $area
                            ?? '—'
                        );


                return [

                    'fecha' =>
                        $row['fecha_asignacion']
                        ?? null,

                    'operacion' =>
                        $this->catalogoValorNombre(
                            $row['id_tipo_asignacion']
                            ?? null
                        )
                        ?? 'Asignación',

                    'responsable' =>
                        $row['nombre_colaborador']
                        ?? '—',

                    'ubicacion' =>
                        $ubicacion,

                ];

            })
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Mantenimientos
    |--------------------------------------------------------------------------
    |
    | Todavía no conocemos la estructura exacta de tu tabla de
    | mantenimientos. Por ahora dejamos el arreglo vacío para que la
    | vista pueda mostrar "Sin mantenimientos registrados" sin fallar.
    |
    */

    public function getMantenimientosProperty(): array
    {
        return [];
    }

    /**
     * Placeholder mostrado mientras el detalle
     * del equipo se carga de manera lazy.
     */
    public function placeholder()
    {
        return view('livewire.placeholders.equipo-show');
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.equipo-show',
            [
                'equipo' =>
                    $this->equipo,

                'especificaciones' =>
                    $this->especificaciones,

                'asignacionActual' =>
                    $this->asignacionActual,

                'movimientos' =>
                    $this->movimientos,

                'mantenimientos' =>
                    $this->mantenimientos,
            ]
        );
    }
}