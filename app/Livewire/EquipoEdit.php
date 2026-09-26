<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Json;
use Livewire\Component;

class EquipoEdit extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Identificación
    |--------------------------------------------------------------------------
    */

    public int $equipoId;

    public string $codigoInventario = '';

    public int $tipoEquipoOriginal = 0;


    /*
    |--------------------------------------------------------------------------
    | Información general
    |--------------------------------------------------------------------------
    */

    public string $nombreEquipo = '';

    public string $host = '';

    public string $idEstadoActivo = '';

    public string $idTipoEquipo = '';

    public string $idModelo = '';

    public string $fechaCompra = '';

    public string $idMarca = '';

    public string $direccionMac = '';

    public string $numeroFactura = '';

    public string $numeroSerie = '';

    public string $idProveedor = '';


    /*
    |--------------------------------------------------------------------------
    | Información adicional
    |--------------------------------------------------------------------------
    */

    public string $propietario = '';

    public string $idCondicionActivo = '';

    public string $idArea = '';

    public string $idDepartamento = '';

    public string $fechaFinGarantia = '';

    public string $comentarios = '';


    /*
    |--------------------------------------------------------------------------
    | Campos específicos por tipo
    |--------------------------------------------------------------------------
    */

    public array $childData = [];


    /*
    |--------------------------------------------------------------------------
    | Control visual
    |--------------------------------------------------------------------------
    */

    public int $formKey = 0;


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(int $equipoId): void
    {
        $this->equipoId = $equipoId;

        $this->cargarEquipo();
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar equipo existente
    |--------------------------------------------------------------------------
    */

    protected function cargarEquipo(): void
    {
        $equipo = DB::table('equipos')
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->first();

        abort_unless(
            $equipo,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Información general
        |--------------------------------------------------------------------------
        */

        $this->codigoInventario =
            (string) (
                $equipo->codigo_inventario
                ?? ''
            );

        $this->nombreEquipo =
            (string) (
                $equipo->nombre_equipo
                ?? ''
            );

        $this->host =
            (string) (
                $equipo->host
                ?? ''
            );

        $this->idEstadoActivo =
            $equipo->id_estado_activo !== null
                ? (string) $equipo->id_estado_activo
                : '';

        $this->idTipoEquipo =
            $equipo->id_tipo_equipo !== null
                ? (string) $equipo->id_tipo_equipo
                : '';

        $this->tipoEquipoOriginal =
            (int) (
                $equipo->id_tipo_equipo
                ?? 0
            );

        $this->idModelo =
            $equipo->id_modelo !== null
                ? (string) $equipo->id_modelo
                : '';

        $this->fechaCompra =
            (string) (
                $equipo->fecha_compra
                ?? ''
            );

        $this->idMarca =
            $equipo->id_marca !== null
                ? (string) $equipo->id_marca
                : '';

        $this->direccionMac =
            (string) (
                $equipo->direccion_mac
                ?? ''
            );

        $this->numeroFactura =
            (string) (
                $equipo->numero_factura
                ?? ''
            );

        $this->numeroSerie =
            (string) (
                $equipo->numero_serie
                ?? ''
            );

        $this->idProveedor =
            $equipo->id_proveedor !== null
                ? (string) $equipo->id_proveedor
                : '';


        /*
        |--------------------------------------------------------------------------
        | Información adicional
        |--------------------------------------------------------------------------
        */

        $this->idCondicionActivo =
            $equipo->id_condicion_activo !== null
                ? (string) $equipo->id_condicion_activo
                : '';

        $this->fechaFinGarantia =
            (string) (
                $equipo->fecha_fin_garantia
                ?? ''
            );

        $this->comentarios =
            (string) (
                $equipo->comentarios
                ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | Área / Departamento
        |--------------------------------------------------------------------------
        |
        | En equipos solamente se guarda:
        |
        | - id_area
        | o
        | - id_departamento
        |
        | Cuando existe departamento obtenemos el área desde departamentos.
        |
        */

        if ($equipo->id_departamento !== null) {

            $departamento = DB::table('departamentos')
                ->where(
                    'id_departamento',
                    (int) $equipo->id_departamento
                )
                ->first([
                    'id_departamento',
                    'id_area',
                ]);

            if ($departamento) {

                $this->idArea =
                    $departamento->id_area !== null
                        ? (string) $departamento->id_area
                        : '';

                $this->idDepartamento =
                    (string) $departamento->id_departamento;
            }

        } else {

            $this->idArea =
                $equipo->id_area !== null
                    ? (string) $equipo->id_area
                    : '';

            $this->idDepartamento = '';
        }


        /*
        |--------------------------------------------------------------------------
        | Responsable actual
        |--------------------------------------------------------------------------
        |
        | Solo se muestra como referencia.
        |
        | No modificaremos asignaciones desde Editar equipo.
        |
        */

        $asignacion = DB::table('asignaciones')
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->orderByDesc(
                'fecha_asignacion'
            )
            ->first([
                'nombre_colaborador',
            ]);

        $this->propietario =
            (string) (
                $asignacion->nombre_colaborador
                ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | Cargar información específica
        |--------------------------------------------------------------------------
        */

        $this->cargarChildData();
    }


    /*
    |--------------------------------------------------------------------------
    | Conversión segura para cache
    |--------------------------------------------------------------------------
    */

    private function toPlainArray(
        $collection
    ): array {

        return $collection
            ->map(
                fn ($row) =>
                    (array) $row
            )
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Configuración de tablas específicas
    |--------------------------------------------------------------------------
    */

    protected function childConfig(): array
    {
        $baseCompu = [

            'procesador' =>
                'text',

            'ram_gb' =>
                'number',

            'almacenamiento_gb' =>
                'number',

            'id_tipo_almacenamiento' =>
                'catalog:tipo_almacenamiento',

            'sistema_operativo' =>
                'text',
        ];


        return [

            1 => [

                'table' =>
                    'computadoras_escritorio',

                'fields' =>
                    $baseCompu,

            ],


            2 => [

                'table' =>
                    'laptops',

                'fields' =>
                    $baseCompu + [

                        'incluye_cargador' =>
                            'boolean',

                    ],

            ],


            3 => [

                'table' =>
                    'monitores',

                'fields' => [

                    'tamano_pulgadas' =>
                        'number',

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

                ],

            ],


            4 => [

                'table' =>
                    'tablets',

                'fields' => [

                    'imei' =>
                        'text',

                    'almacenamiento_gb' =>
                        'number',

                    'sistema_operativo' =>
                        'text',

                    'color' =>
                        'text',

                    'incluye_cargador' =>
                        'boolean',

                ],

            ],


            5 => [

                'table' =>
                    'moviles',

                'fields' => [

                    'imei_1' =>
                        'text',

                    'imei_2' =>
                        'text',

                    'numero_telefonico' =>
                        'text',

                    'almacenamiento_gb' =>
                        'number',

                    'sistema_operativo' =>
                        'text',

                    'color' =>
                        'text',

                    'incluye_cargador' =>
                        'boolean',

                ],

            ],


            6 => [

                'table' =>
                    'impresoras',

                'fields' => [

                    'id_tipo_impresora' =>
                        'catalog:tipo_impresora',

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

                    'nombre_red' =>
                        'text',

                ],

            ],


            7 => [

                'table' =>
                    'escaneres',

                'fields' => [

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

                ],

            ],


            8 => [

                'table' =>
                    'grabadores_llaves',

                'fields' => [

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

                ],

            ],


            9 => [

                'table' =>
                    'tpv',

                'fields' =>
                    $baseCompu,

            ],


            10 => [

                'table' =>
                    'switches',

                'fields' => [

                    'numero_puertos' =>
                        'number',

                    'velocidad' =>
                        'text',

                    'administrable' =>
                        'boolean',

                ],

            ],


            11 => [

                'table' =>
                    'routers',

                'fields' => [

                    'numero_puertos' =>
                        'number',

                    'velocidad' =>
                        'text',

                ],

            ],


            12 => [

                'table' =>
                    'access_points',

                'fields' => [

                    'nombre_ap' =>
                        'text',

                ],

            ],


            13 => [

                'table' =>
                    'servidores',

                'fields' => [

                    'id_tipo_servidor' =>
                        'catalog:tipo_servidor',

                ] + $baseCompu,

            ],


            14 => [

                'table' =>
                    'ups',

                'fields' => [

                    'capacidad_va' =>
                        'number',

                    'fecha_cambio_bateria' =>
                        'date',

                    'id_estado_bateria' =>
                        'catalog:estado_bateria',

                ],

            ],


            15 => [

                'table' =>
                    'telefonos',

                'fields' => [

                    'id_tipo_telefono' =>
                        'catalog:tipo_telefono',

                    'extension' =>
                        'text',

                ],

            ],


            16 => [

                'table' =>
                    'teclados',

                'fields' => [

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

                ],

            ],


            17 => [

                'table' =>
                    'mouse',

                'fields' => [

                    'id_tipo_conexion' =>
                        'catalog:tipo_conexion',

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
                'RAM (GB)',

            'almacenamiento_gb' =>
                'Almacenamiento (GB)',

            'id_tipo_almacenamiento' =>
                'Tipo de almacenamiento',

            'sistema_operativo' =>
                'Sistema operativo',

            'incluye_cargador' =>
                'Incluye cargador',

            'tamano_pulgadas' =>
                'Tamaño (pulgadas)',

            'id_tipo_conexion' =>
                'Tipo de conexión',

            'imei' =>
                'IMEI',

            'color' =>
                'Color',

            'imei_1' =>
                'IMEI 1',

            'imei_2' =>
                'IMEI 2',

            'numero_telefonico' =>
                'Número telefónico',

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
                'Capacidad (VA)',

            'fecha_cambio_bateria' =>
                'Fecha de cambio de batería',

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
    | Campos específicos
    |--------------------------------------------------------------------------
    */

    public function getChildFieldsProperty(): array
    {
        $config =
            $this->childConfig();

        $tipo =
            (int) $this->idTipoEquipo;

        return
            $config[$tipo]['fields']
            ?? [];
    }


    public function getFieldLabelsProperty(): array
    {
        return $this->fieldLabels();
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar tabla específica
    |--------------------------------------------------------------------------
    */

    protected function cargarChildData(): void
    {
        $this->childData = [];


        $tipo =
            (int) $this->idTipoEquipo;


        $config =
            $this->childConfig()[$tipo]
            ?? null;


        if (!$config) {
            return;
        }


        $row = DB::table(
            $config['table']
        )
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->first();


        if (!$row) {
            return;
        }


        $row =
            (array) $row;


        foreach (
            $config['fields']
            as $field => $type
        ) {

            $value =
                $row[$field]
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | Checkbox
            |--------------------------------------------------------------------------
            */

            if ($type === 'boolean') {

                $this->childData[$field] =
                    (bool) $value;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | El resto se mantiene como string para Livewire
            |--------------------------------------------------------------------------
            */

            $this->childData[$field] =
                $value !== null
                    ? (string) $value
                    : '';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Cambios dependientes
    |--------------------------------------------------------------------------
    */

    public function updatedIdTipoEquipo(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Al cambiar manualmente de tipo no debemos cargar los datos antiguos.
        |--------------------------------------------------------------------------
        */

        $this->childData = [];

        $this->idModelo = '';
    }


    public function updatedIdMarca(): void
    {
        $this->idModelo = '';
    }


    public function updatedIdArea(): void
    {
        $this->idDepartamento = '';
    }


    /*
    |--------------------------------------------------------------------------
    | Catálogos
    |--------------------------------------------------------------------------
    */

    public function getEstadosProperty(): array
    {
        return Cache::remember(
            'form.estados_equipo.v2',
            now()->addMinutes(30),
            function () {

                return $this->toPlainArray(

                    DB::table('estados_equipo')

                        ->where(
                            'activo',
                            true
                        )

                        ->orderBy(
                            'orden'
                        )

                        ->get([
                            'id_estado_equipo',
                            'nombre',
                        ])
                );
            }
        );
    }


    public function getTiposEquipoProperty(): array
    {
        return Cache::remember(
            'form.tipos_equipo.v2',
            now()->addMinutes(30),
            function () {

                return $this->toPlainArray(

                    DB::table('tipos_equipo')

                        ->where(
                            'activo',
                            true
                        )

                        ->orderBy(
                            'nombre'
                        )

                        ->get([
                            'id_tipo_equipo',
                            'nombre',
                        ])
                );
            }
        );
    }


    public function getMarcasProperty(): array
    {
        return Cache::remember(
            'form.marcas.v2',
            now()->addMinutes(30),
            function () {

                return $this->toPlainArray(

                    DB::table('marcas')

                        ->where(
                            'activo',
                            true
                        )

                        ->orderBy(
                            'nombre'
                        )

                        ->get([
                            'id_marca',
                            'nombre',
                        ])
                );
            }
        );
    }


    public function getModelosProperty(): array
    {
        if (
            $this->idMarca === '' ||
            $this->idTipoEquipo === ''
        ) {
            return [];
        }


        return $this->toPlainArray(

            DB::table('modelos')

                ->where(
                    'activo',
                    true
                )

                ->where(
                    'id_marca',
                    (int) $this->idMarca
                )

                ->where(
                    'id_tipo_equipo',
                    (int) $this->idTipoEquipo
                )

                ->orderBy(
                    'nombre'
                )

                ->get([
                    'id_modelo',
                    'nombre',
                ])
        );
    }


    public function getProveedoresProperty(): array
    {
        return Cache::remember(
            'form.proveedores.v2',
            now()->addMinutes(30),
            function () {

                return $this->toPlainArray(

                    DB::table('proveedores')

                        ->where(
                            'activo',
                            true
                        )

                        ->orderBy(
                            'nombre'
                        )

                        ->get([
                            'id_proveedor',
                            'nombre',
                        ])
                );
            }
        );
    }


    public function getCondicionesProperty(): array
    {
        return Cache::remember(
            'form.condicion_activo.v2',
            now()->addMinutes(30),
            function () {

                return $this->catalogoValores(
                    'condicion_activo'
                );
            }
        );
    }


    public function getAreasProperty(): array
    {
        return Cache::remember(
            'form.areas.v2',
            now()->addMinutes(30),
            function () {

                return $this->toPlainArray(

                    DB::table('areas')

                        ->where(
                            'activo',
                            true
                        )

                        ->orderBy(
                            'nombre'
                        )

                        ->get([
                            'id_area',
                            'nombre',
                        ])
                );
            }
        );
    }


    public function getDepartamentosProperty(): array
    {
        if ($this->idArea === '') {
            return [];
        }


        return $this->toPlainArray(

            DB::table('departamentos')

                ->where(
                    'activo',
                    true
                )

                ->where(
                    'id_area',
                    (int) $this->idArea
                )

                ->orderBy(
                    'nombre'
                )

                ->get([
                    'id_departamento',
                    'nombre',
                ])
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Catálogos específicos
    |--------------------------------------------------------------------------
    */

    public function catalogOptions(
        string $clave
    ): array {

        return Cache::remember(
            "form.catalogo.$clave.v2",
            now()->addMinutes(30),
            function () use ($clave) {

                return $this->catalogoValores(
                    $clave
                );
            }
        );
    }


    private function catalogoValores(
        string $clave
    ): array {

        return $this->toPlainArray(

            DB::table(
                'catalogo_valores'
            )

                ->join(
                    'catalogos',
                    'catalogos.id_catalogo',
                    '=',
                    'catalogo_valores.id_catalogo'
                )

                ->where(
                    'catalogos.clave',
                    $clave
                )

                ->where(
                    'catalogo_valores.activo',
                    true
                )

                ->orderBy(
                    'catalogo_valores.orden'
                )

                ->get([
                    'catalogo_valores.id_valor',
                    'catalogo_valores.nombre',
                ])
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar cambios de forma optimista
    |--------------------------------------------------------------------------
    |
    | El Blade envía una fotografía completa del formulario.
    |
    | El guardado ya no depende de que las propiedades actuales del componente
    | permanezcan sin cambios mientras termina la petición.
    |
    */

    #[Json]
    public function save(array $payload): array
    {
        $payload =
            $this->normalizePayload(
                $payload
            );


        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = Validator::make(
            $payload,
            [

                'nombreEquipo' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'idEstadoActivo' => [
                    'required',
                ],

                'idTipoEquipo' => [
                    'required',
                ],

                'idMarca' => [
                    'required',
                ],

                'numeroSerie' => [

                    'required',

                    'string',

                    'max:255',

                    Rule::unique(
                        'equipos',
                        'numero_serie'
                    )->ignore(
                        $this->equipoId,
                        'id_equipo'
                    ),

                ],

                'numeroFactura' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'idArea' => [
                    'required',
                ],

                'childData' => [
                    'array',
                ],

            ]
        )->validate();


        /*
        |--------------------------------------------------------------------------
        | Tipo actualmente persistido
        |--------------------------------------------------------------------------
        |
        | Lo consultamos directamente en BD.
        |
        | Esto es importante porque #[Json] no depende de un render posterior
        | para mantener correcto el tipo anterior en guardados consecutivos.
        |
        */

        $tipoAnterior = (int) DB::table(
            'equipos'
        )
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->value(
                'id_tipo_equipo'
            );


        $tipoNuevo =
            (int) $validated['idTipoEquipo'];


        $configAnterior =
            $this->childConfig()[
                $tipoAnterior
            ]
            ?? null;


        $configNuevo =
            $this->childConfig()[
                $tipoNuevo
            ]
            ?? null;


        $childFields =
            $configNuevo['fields']
            ?? [];


        $childData =
            is_array(
                $payload['childData']
            )
                ? $payload['childData']
                : [];


        /*
        |--------------------------------------------------------------------------
        | Transacción
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $payload,
                $validated,
                $tipoAnterior,
                $tipoNuevo,
                $configAnterior,
                $configNuevo,
                $childFields,
                $childData
            ): void {


                /*
                |--------------------------------------------------------------------------
                | Si cambió el tipo, eliminamos la fila específica anterior
                |--------------------------------------------------------------------------
                */

                if (
                    $tipoAnterior !==
                        $tipoNuevo &&
                    $configAnterior
                ) {

                    DB::table(
                        $configAnterior['table']
                    )
                        ->where(
                            'id_equipo',
                            $this->equipoId
                        )
                        ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | Actualizar equipo
                |--------------------------------------------------------------------------
                |
                | codigo_inventario NO se modifica.
                |
                */

                DB::table('equipos')

                    ->where(
                        'id_equipo',
                        $this->equipoId
                    )

                    ->update([

                        'nombre_equipo' =>
                            trim(
                                (string) $validated['nombreEquipo']
                            ),

                        'host' =>
                            $this->nullableString(
                                $payload['host']
                            ),

                        'id_estado_activo' =>
                            (int) $validated['idEstadoActivo'],

                        'id_tipo_equipo' =>
                            $tipoNuevo,

                        'id_modelo' =>
                            $this->nullableInt(
                                $payload['idModelo']
                            ),

                        'fecha_compra' =>
                            $this->nullableString(
                                $payload['fechaCompra']
                            ),

                        'id_marca' =>
                            (int) $validated['idMarca'],

                        'direccion_mac' =>
                            $this->nullableString(
                                $payload['direccionMac']
                            ),

                        'numero_factura' =>
                            trim(
                                (string) $validated['numeroFactura']
                            ),

                        'numero_serie' =>
                            trim(
                                (string) $validated['numeroSerie']
                            ),

                        'id_proveedor' =>
                            $this->nullableInt(
                                $payload['idProveedor']
                            ),

                        'id_condicion_activo' =>
                            $this->nullableInt(
                                $payload['idCondicionActivo']
                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Área / Departamento
                        |--------------------------------------------------------------------------
                        */

                        'id_area' =>
                            $this->nullableInt(
                                $payload['idDepartamento']
                            ) !== null
                                ? null
                                : (int) $validated['idArea'],

                        'id_departamento' =>
                            $this->nullableInt(
                                $payload['idDepartamento']
                            ),

                        'fecha_fin_garantia' =>
                            $this->nullableString(
                                $payload['fechaFinGarantia']
                            ),

                        'comentarios' =>
                            $this->nullableString(
                                $payload['comentarios']
                            ),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Tabla específica
                |--------------------------------------------------------------------------
                */

                if ($configNuevo) {

                    $row = [];


                    foreach (
                        $childFields
                        as $field => $type
                    ) {

                        $value =
                            $childData[$field]
                            ?? null;


                        /*
                        |--------------------------------------------------------------------------
                        | Boolean
                        |--------------------------------------------------------------------------
                        */

                        if ($type === 'boolean') {

                            $row[$field] =
                                (bool) (
                                    $value
                                    ?? false
                                );

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Null
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $value === null ||
                            $value === ''
                        ) {

                            $row[$field] =
                                null;

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Number
                        |--------------------------------------------------------------------------
                        */

                        if ($type === 'number') {

                            $row[$field] =
                                is_numeric($value)
                                    ? $value + 0
                                    : null;

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Catalog
                        |--------------------------------------------------------------------------
                        */

                        if (
                            str_starts_with(
                                $type,
                                'catalog:'
                            )
                        ) {

                            $row[$field] =
                                (int) $value;

                            continue;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Texto / fecha
                        |--------------------------------------------------------------------------
                        */

                        $row[$field] =
                            $value;
                    }


                    DB::table(
                        $configNuevo['table']
                    )
                        ->updateOrInsert(
                            [
                                'id_equipo' =>
                                    $this->equipoId,
                            ],
                            $row
                        );
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Estado interno
        |--------------------------------------------------------------------------
        */

        $this->tipoEquipoOriginal =
            $tipoNuevo;


        /*
        |--------------------------------------------------------------------------
        | Respuesta JSON
        |--------------------------------------------------------------------------
        */

        return [

            'ok' =>
                true,

            'message' =>
                'Los cambios se guardaron correctamente.',

            'record' => [

                'id' =>
                    $this->equipoId,

                'code' =>
                    $this->codigoInventario,

                'name' =>
                    trim(
                        (string) $validated['nombreEquipo']
                    ),

                'typeId' =>
                    $tipoNuevo,

            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar snapshot
    |--------------------------------------------------------------------------
    */

    private function normalizePayload(
        array $payload
    ): array {

        return array_merge(
            [

                'nombreEquipo' => '',

                'host' => '',

                'idEstadoActivo' => '',

                'idTipoEquipo' => '',

                'idModelo' => '',

                'fechaCompra' => '',

                'idMarca' => '',

                'direccionMac' => '',

                'numeroFactura' => '',

                'numeroSerie' => '',

                'idProveedor' => '',

                'propietario' => '',

                'idCondicionActivo' => '',

                'idArea' => '',

                'idDepartamento' => '',

                'fechaFinGarantia' => '',

                'comentarios' => '',

                'childData' => [],

            ],
            $payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Entero nullable
    |--------------------------------------------------------------------------
    */

    private function nullableInt(
        mixed $value
    ): ?int {

        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }


        return is_numeric($value)
            ? (int) $value
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Texto nullable
    |--------------------------------------------------------------------------
    */

    private function nullableString(
        mixed $value
    ): ?string {

        if (
            $value === null ||
            ! is_string($value)
        ) {
            return null;
        }


        $value =
            trim($value);


        return $value !== ''
            ? $value
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder()
    {
        return view(
            'livewire.placeholders.equipo-edit'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.equipo-edit'
        );
    }
}