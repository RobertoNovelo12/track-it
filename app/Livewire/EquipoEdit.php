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
    | CACHE DE DATOS BASE PARA EDICIÓN
    |--------------------------------------------------------------------------
    |
    | Esta caché puede ser alimentada previamente por EquiposIndex.
    |
    | Si el usuario llegó directamente a la URL de edición y no existe
    | la caché, EquipoEdit consulta la base normalmente y la genera.
    |
    */

    private const EDIT_BASE_CACHE_TTL_MINUTES = 10;

    private const TABLE_CACHE_VERSION_KEY =
        'equipos.index.version';


    private function editBaseCacheKey(): string
    {
        return
            'equipos.edit.base.'
            . $this->equipoId;
    }

    /*
    |--------------------------------------------------------------------------
    | INVALIDAR CACHES DEL EQUIPO
    |--------------------------------------------------------------------------
    |
    | Después de guardar cambios:
    |
    | 1. Eliminamos la cache base de edición de este equipo.
    | 2. Incrementamos la versión de la tabla de Equipos.
    |
    | De esta forma no se reutilizan datos anteriores después de editar.
    |
    */

    private function invalidateEquipoCaches(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CACHE DE EDICIÓN
        |--------------------------------------------------------------------------
        */

        Cache::forget(
            $this->editBaseCacheKey()
        );


        /*
        |--------------------------------------------------------------------------
        | CACHE DEL LISTADO
        |--------------------------------------------------------------------------
        |
        | EquiposIndex utiliza esta versión dentro de la clave de cada página.
        |
        | Al incrementarla, las páginas anteriores dejan de utilizarse sin
        | necesidad de ejecutar Cache::flush().
        |
        */

        $currentVersion =
            (int) Cache::get(
                self::TABLE_CACHE_VERSION_KEY,
                1
            );


        Cache::forever(
            self::TABLE_CACHE_VERSION_KEY,
            $currentVersion + 1
        );
    }

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
        $equipoData =
            Cache::remember(
                $this->editBaseCacheKey(),

                now()->addMinutes(
                    self::EDIT_BASE_CACHE_TTL_MINUTES
                ),

                function (): ?array {
                    $equipo =
                        DB::table('equipos as e')

                            /*
                            |--------------------------------------------------------------------------
                            | DEPARTAMENTO
                            |--------------------------------------------------------------------------
                            |
                            | Aprovechamos la misma consulta para obtener también
                            | el área correspondiente al departamento.
                            |
                            | Así eliminamos la segunda consulta que antes se hacía
                            | más abajo en cargarEquipo().
                            |
                            */

                            ->leftJoin(
                                'departamentos as dep',
                                'dep.id_departamento',
                                '=',
                                'e.id_departamento'
                            )

                            ->where(
                                'e.id_equipo',
                                $this->equipoId
                            )

                            ->first([
                                'e.*',

                                'dep.id_area as departamento_area_id',
                            ]);


                    return $equipo
                        ? (array) $equipo
                        : null;
                }
            );


        $equipo =
            $equipoData
                ? (object) $equipoData
                : null;

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
            | departamento_area_id ya viene de la misma consulta del equipo.
            |
            | Si el equipo pertenece a un departamento, utilizamos el área
            | obtenida mediante el LEFT JOIN.
            |
            | Si pertenece directamente a un área, utilizamos id_area
            | del propio equipo.
            |
            */

            if (
                $equipo->id_departamento
                !== null
            ) {
                $this->idArea =
                    $equipo->departamento_area_id !== null
                        ? (string) $equipo->departamento_area_id
                        : '';

                $this->idDepartamento =
                    (string) $equipo->id_departamento;

            } else {
                $this->idArea =
                    $equipo->id_area !== null
                        ? (string) $equipo->id_area
                        : '';

                $this->idDepartamento = '';
            }


            /*
            |--------------------------------------------------------------------------
            | DATOS COMPLEMENTARIOS
            |--------------------------------------------------------------------------
            |
            | Responsable actual + datos específicos del tipo se obtienen
            | en una sola consulta remota.
            |
            */

            $this->cargarDatosComplementarios();
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

    /*
    |--------------------------------------------------------------------------
    | CARGAR DATOS COMPLEMENTARIOS
    |--------------------------------------------------------------------------
    |
    | Obtiene en una sola consulta:
    |
    | - Responsable actual.
    | - Información específica según el tipo de equipo.
    |
    | Se utiliza una fila virtual como punto de partida para garantizar
    | que la consulta siempre pueda devolver el responsable aunque el
    | registro específico del tipo todavía no exista.
    |
    */

    protected function cargarDatosComplementarios(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ESTADO INICIAL
        |--------------------------------------------------------------------------
        */

        $this->propietario = '';

        $this->childData = [];


        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN DEL TIPO
        |--------------------------------------------------------------------------
        */

        $tipo =
            (int) $this->idTipoEquipo;


        $config =
            $this->childConfig()[$tipo]
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | FILA BASE VIRTUAL
        |--------------------------------------------------------------------------
        |
        | Esto NO consulta una tabla.
        |
        | PostgreSQL genera simplemente:
        |
        | SELECT ?::bigint AS id_equipo
        |
        | y a partir de ese ID hacemos los JOIN/subqueries necesarios.
        |
        */

        $anchor =
            DB::query()
                ->selectRaw(
                    '?::bigint as id_equipo',
                    [
                        $this->equipoId,
                    ]
                );


        /*
        |--------------------------------------------------------------------------
        | CONSULTA ÚNICA
        |--------------------------------------------------------------------------
        */

        $query =
            DB::query()

                ->fromSub(
                    $anchor,
                    'anchor'
                )

                ->select([
                    'anchor.id_equipo as anchor_equipo_id',
                ]);


        /*
        |--------------------------------------------------------------------------
        | RESPONSABLE ACTUAL
        |--------------------------------------------------------------------------
        |
        | Se obtiene mediante una subconsulta correlacionada dentro de la
        | misma consulta SQL.
        |
        */

        $query->selectSub(
            function ($subQuery): void {
                $subQuery
                    ->from(
                        'asignaciones as asignacion'
                    )

                    ->select(
                        'asignacion.nombre_colaborador'
                    )

                    ->whereColumn(
                        'asignacion.id_equipo',
                        'anchor.id_equipo'
                    )

                    ->orderByDesc(
                        'asignacion.fecha_asignacion'
                    )

                    ->limit(1);
            },
            'responsable_actual'
        );


        /*
        |--------------------------------------------------------------------------
        | TABLA ESPECÍFICA
        |--------------------------------------------------------------------------
        */

        if ($config) {

            $query->leftJoin(
                $config['table'] . ' as child',
                'child.id_equipo',
                '=',
                'anchor.id_equipo'
            );


            /*
            |--------------------------------------------------------------------------
            | Saber si realmente existe el registro específico
            |--------------------------------------------------------------------------
            */

            $query->addSelect(
                'child.id_equipo as child_equipo_id'
            );


            /*
            |--------------------------------------------------------------------------
            | Seleccionar únicamente los campos configurados
            |--------------------------------------------------------------------------
            */

            foreach (
                array_keys(
                    $config['fields']
                )
                as $field
            ) {
                $query->addSelect(
                    'child.' . $field
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EJECUTAR
        |--------------------------------------------------------------------------
        |
        | Esta es la única consulta remota de este método.
        |
        */

        $row =
            $query->first();


        if (! $row) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSABLE
        |--------------------------------------------------------------------------
        */

        $this->propietario =
            (string) (
                $row->responsable_actual
                ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | SIN TABLA ESPECÍFICA
        |--------------------------------------------------------------------------
        */

        if (! $config) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SIN REGISTRO ESPECÍFICO
        |--------------------------------------------------------------------------
        |
        | El LEFT JOIN permite que el responsable siga cargándose aunque
        | por alguna razón todavía no exista la fila específica.
        |
        */

        if (
            (
                $row->child_equipo_id
                ?? null
            ) === null
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZAR CAMPOS ESPECÍFICOS
        |--------------------------------------------------------------------------
        */

        foreach (
            $config['fields']
            as $field => $type
        ) {

            $value =
                $row->{$field}
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | BOOLEAN
            |--------------------------------------------------------------------------
            */

            if ($type === 'boolean') {

                $this->childData[$field] =
                    (bool) $value;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | RESTO DE CAMPOS
            |--------------------------------------------------------------------------
            |
            | Livewire los maneja como string.
            |
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


    private function getAllModelos(): array
    {
        return Cache::remember(
            'form.modelos.all.v2',
            now()->addMinutes(30),
            function () {
                return $this->toPlainArray(
                    DB::table('modelos')
                        ->where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_modelo',
                            'id_marca',
                            'id_tipo_equipo',
                            'nombre',
                        ])
                );
            }
        );
    }

    public function getModelosProperty(): array
    {
        if (
            $this->idMarca === ''
            || $this->idTipoEquipo === ''
        ) {
            return [];
        }

        return collect(
            $this->getAllModelos()
        )
            ->filter(
                fn (array $modelo): bool =>
                    (string) $modelo['id_marca']
                        === (string) $this->idMarca
                    &&
                    (string) $modelo['id_tipo_equipo']
                        === (string) $this->idTipoEquipo
            )
            ->values()
            ->all();
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


    private function getAllDepartamentos(): array
    {
        return Cache::remember(
            'form.departamentos.all.v2',
            now()->addMinutes(30),
            function () {
                return $this->toPlainArray(
                    DB::table('departamentos')
                        ->where('activo', true)
                        ->orderBy('nombre')
                        ->get([
                            'id_departamento',
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

        return collect(
            $this->getAllDepartamentos()
        )
            ->filter(
                fn (array $departamento): bool =>
                    (string) $departamento['id_area']
                        === (string) $this->idArea
            )
            ->values()
            ->all();
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
| Datos iniciales para Alpine
|--------------------------------------------------------------------------
*/

private function toFrontendOptions(
    array $items,
    string $valueKey,
    string $labelKey = 'nombre'
): array {
    return collect($items)
        ->map(
            fn (array $item): array => [
                'value' =>
                    (string) (
                        $item[$valueKey]
                        ?? ''
                    ),

                'label' =>
                    (string) (
                        $item[$labelKey]
                        ?? ''
                    ),
            ]
        )
        ->values()
        ->all();
}

private function getDynamicCatalogKeys(): array
{
    return collect(
        $this->childConfig()
    )
        ->flatMap(
            fn (array $config): array =>
                array_values(
                    $config['fields']
                    ?? []
                )
        )
        ->filter(
            fn ($type): bool =>
                is_string($type)
                && str_starts_with(
                    $type,
                    'catalog:'
                )
        )
        ->map(
            fn (string $type): string =>
                substr(
                    $type,
                    8
                )
        )
        ->unique()
        ->values()
        ->all();
}

private function getEquipmentFrontendData(): array
{
    /*
    |--------------------------------------------------------------------------
    | Opciones generales
    |--------------------------------------------------------------------------
    */

    $estados =
        $this->toFrontendOptions(
            $this->estados,
            'id_estado_equipo'
        );

    $tiposEquipo =
        $this->toFrontendOptions(
            $this->tiposEquipo,
            'id_tipo_equipo'
        );

    $marcas =
        $this->toFrontendOptions(
            $this->marcas,
            'id_marca'
        );

    $proveedores =
        $this->toFrontendOptions(
            $this->proveedores,
            'id_proveedor'
        );

    $condiciones =
        $this->toFrontendOptions(
            $this->condiciones,
            'id_valor'
        );

    $areas =
        $this->toFrontendOptions(
            $this->areas,
            'id_area'
        );


    /*
    |--------------------------------------------------------------------------
    | Todos los modelos
    |--------------------------------------------------------------------------
    |
    | Alpine filtrará por Marca + Tipo.
    |
    */

    $modelos =
        collect(
            $this->getAllModelos()
        )
            ->map(
                fn (array $modelo): array => [
                    'value' =>
                        (string) $modelo['id_modelo'],

                    'label' =>
                        (string) $modelo['nombre'],

                    'idMarca' =>
                        (string) $modelo['id_marca'],

                    'idTipoEquipo' =>
                        (string) $modelo['id_tipo_equipo'],
                ]
            )
            ->values()
            ->all();


    /*
    |--------------------------------------------------------------------------
    | Todos los departamentos
    |--------------------------------------------------------------------------
    |
    | Alpine filtrará utilizando idArea.
    |
    */

    $departamentos =
        collect(
            $this->getAllDepartamentos()
        )
            ->map(
                fn (array $departamento): array => [
                    'value' =>
                        (string) $departamento['id_departamento'],

                    'label' =>
                        (string) $departamento['nombre'],

                    'idArea' =>
                        (string) $departamento['id_area'],
                ]
            )
            ->values()
            ->all();


    /*
    |--------------------------------------------------------------------------
    | Configuración de campos específicos
    |--------------------------------------------------------------------------
    */

    $childFieldsByType = [];

    foreach (
        $this->childConfig()
        as $tipoId => $config
    ) {
        $childFieldsByType[
            (string) $tipoId
        ] =
            $config['fields']
            ?? [];
    }


    /*
    |--------------------------------------------------------------------------
    | Catálogos utilizados por campos específicos
    |--------------------------------------------------------------------------
    */

    $dynamicCatalogs = [];

    foreach (
        $this->getDynamicCatalogKeys()
        as $clave
    ) {
        $dynamicCatalogs[$clave] =
            $this->toFrontendOptions(
                $this->catalogOptions(
                    $clave
                ),
                'id_valor'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Estado inicial del equipo
    |--------------------------------------------------------------------------
    |
    | Estos valores ya fueron cargados por mount() / cargarEquipo().
    |
    */

    $initial = [
        'equipoId' =>
            $this->equipoId,

        'codigoInventario' =>
            $this->codigoInventario,

        'tipoEquipoOriginal' =>
            (string) $this->tipoEquipoOriginal,

        'nombreEquipo' =>
            $this->nombreEquipo,

        'host' =>
            $this->host,

        'idEstadoActivo' =>
            $this->idEstadoActivo,

        'idTipoEquipo' =>
            $this->idTipoEquipo,

        'idModelo' =>
            $this->idModelo,

        'fechaCompra' =>
            $this->fechaCompra,

        'idMarca' =>
            $this->idMarca,

        'direccionMac' =>
            $this->direccionMac,

        'numeroFactura' =>
            $this->numeroFactura,

        'numeroSerie' =>
            $this->numeroSerie,

        'idProveedor' =>
            $this->idProveedor,

        /*
         * El responsable sólo se muestra como referencia.
         * Editar equipo no modifica asignaciones.
         */
        'propietario' =>
            $this->propietario,

        'idCondicionActivo' =>
            $this->idCondicionActivo,

        'idArea' =>
            $this->idArea,

        'idDepartamento' =>
            $this->idDepartamento,

        'fechaFinGarantia' =>
            $this->fechaFinGarantia,

        'comentarios' =>
            $this->comentarios,

        'childData' =>
            $this->childData,
    ];


    /*
    |--------------------------------------------------------------------------
    | Resultado
    |--------------------------------------------------------------------------
    */

    return [
        'estados' =>
            $estados,

        'tiposEquipo' =>
            $tiposEquipo,

        'marcas' =>
            $marcas,

        'modelos' =>
            $modelos,

        'proveedores' =>
            $proveedores,

        'condiciones' =>
            $condiciones,

        'areas' =>
            $areas,

        'departamentos' =>
            $departamentos,

        'childFieldsByType' =>
            $childFieldsByType,

        'fieldLabels' =>
            $this->fieldLabels(),

        'dynamicCatalogs' =>
            $dynamicCatalogs,

        'initial' =>
            $initial,
    ];
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
        | INVALIDAR CACHES
        |--------------------------------------------------------------------------
        |
        | La transacción terminó correctamente, por lo que ya podemos descartar
        | cualquier representación anterior del equipo.
        |
        */

        $this->invalidateEquipoCaches();


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
            'livewire.equipo-edit',
            [
                'equipmentData' =>
                    $this->getEquipmentFrontendData(),
            ]
        );
    }
}