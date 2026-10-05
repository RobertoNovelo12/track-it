<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Json;
use Livewire\Component;

class EquipoCreate extends Component
{
    // --- Información general ---
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

    // --- Información adicional (común) ---
    public string $propietario = '';
    public string $idCondicionActivo = '';
    public string $idArea = '';
    public string $idDepartamento = '';
    public string $fechaFinGarantia = '';
    public string $comentarios = '';

    // --- Campos dinámicos de la tabla hija (según tipo de equipo) ---
    public array $childData = [];

    public int $formKey = 0;

    /**
     * Convierte una colección de resultados de DB::table() (objetos stdClass)
     * en un arreglo de arreglos asociativos planos.
     */
    private function toPlainArray($collection): array
    {
        return $collection
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * Mapa: id_tipo_equipo => [tabla hija, [campo => tipo de input]].
     */
    protected function childConfig(): array
    {
        $baseCompu = [
            'procesador' => 'text',
            'ram_gb' => 'number',
            'almacenamiento_gb' => 'number',
            'id_tipo_almacenamiento' => 'catalog:tipo_almacenamiento',
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
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
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
                    'id_tipo_impresora' => 'catalog:tipo_impresora',
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
                    'nombre_red' => 'text',
                ],
            ],

            7 => [
                'table' => 'escaneres',
                'fields' => [
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
                ],
            ],

            8 => [
                'table' => 'grabadores_llaves',
                'fields' => [
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
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
                    'id_tipo_servidor' => 'catalog:tipo_servidor',
                ] + $baseCompu,
            ],

            14 => [
                'table' => 'ups',
                'fields' => [
                    'capacidad_va' => 'number',
                    'fecha_cambio_bateria' => 'date',
                    'id_estado_bateria' => 'catalog:estado_bateria',
                ],
            ],

            15 => [
                'table' => 'telefonos',
                'fields' => [
                    'id_tipo_telefono' => 'catalog:tipo_telefono',
                    'extension' => 'text',
                ],
            ],

            16 => [
                'table' => 'teclados',
                'fields' => [
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
                ],
            ],

            17 => [
                'table' => 'mouse',
                'fields' => [
                    'id_tipo_conexion' => 'catalog:tipo_conexion',
                ],
            ],
        ];
    }

    /**
     * Etiquetas legibles para los campos dinámicos.
     */
    protected function fieldLabels(): array
    {
        return [
            'procesador' => 'Procesador',
            'ram_gb' => 'RAM (GB)',
            'almacenamiento_gb' => 'Almacenamiento (GB)',
            'id_tipo_almacenamiento' => 'Tipo de almacenamiento',
            'sistema_operativo' => 'Sistema operativo',
            'incluye_cargador' => 'Incluye cargador',
            'tamano_pulgadas' => 'Tamaño (pulgadas)',
            'id_tipo_conexion' => 'Tipo de conexión',
            'imei' => 'IMEI',
            'color' => 'Color',
            'imei_1' => 'IMEI 1',
            'imei_2' => 'IMEI 2',
            'numero_telefonico' => 'Número telefónico',
            'id_tipo_impresora' => 'Tipo de impresora',
            'nombre_red' => 'Nombre en red',
            'numero_puertos' => 'Número de puertos',
            'velocidad' => 'Velocidad',
            'administrable' => 'Administrable',
            'nombre_ap' => 'Nombre del Access Point',
            'id_tipo_servidor' => 'Tipo de servidor',
            'capacidad_va' => 'Capacidad (VA)',
            'fecha_cambio_bateria' => 'Fecha de cambio de batería',
            'id_estado_bateria' => 'Estado de batería',
            'id_tipo_telefono' => 'Tipo de teléfono',
            'extension' => 'Extensión',
        ];
    }

    public function getChildFieldsProperty(): array
    {
        $config = $this->childConfig();

        $tipo = (int) $this->idTipoEquipo;

        return $config[$tipo]['fields'] ?? [];
    }

    public function getFieldLabelsProperty(): array
    {
        return $this->fieldLabels();
    }

    public function updatedIdTipoEquipo(): void
    {
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

    public function getEstadosProperty(): array
    {
        return Cache::remember(
            'form.estados_equipo.v2',
            now()->addMinutes(30),
            function () {
                return $this->toPlainArray(
                    DB::table('estados_equipo')
                        ->where('activo', true)
                        ->orderBy('orden')
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
                        ->where('activo', true)
                        ->orderBy('nombre')
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
                        ->where('activo', true)
                        ->orderBy('nombre')
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
                        ->where('activo', true)
                        ->orderBy('nombre')
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
                        ->where('activo', true)
                        ->orderBy('nombre')
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

    /**
     * Opciones para un select de tipo catalog:<clave>.
     */
    public function catalogOptions(string $clave): array
    {
        return Cache::remember(
            "form.catalogo.$clave.v2",
            now()->addMinutes(30),
            function () use ($clave) {
                return $this->catalogoValores($clave);
            }
        );
    }

    private function catalogoValores(string $clave): array
    {
        return $this->toPlainArray(
            DB::table('catalogo_valores')
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
        | Estados
        |--------------------------------------------------------------------------
        */

        $estados =
            $this->toFrontendOptions(
                $this->estados,
                'id_estado_equipo'
            );

        /*
        |--------------------------------------------------------------------------
        | Tipos de equipo
        |--------------------------------------------------------------------------
        */

        $tiposEquipo =
            $this->toFrontendOptions(
                $this->tiposEquipo,
                'id_tipo_equipo'
            );

        /*
        |--------------------------------------------------------------------------
        | Marcas
        |--------------------------------------------------------------------------
        */

        $marcas =
            $this->toFrontendOptions(
                $this->marcas,
                'id_marca'
            );

        /*
        |--------------------------------------------------------------------------
        | Todos los modelos
        |--------------------------------------------------------------------------
        |
        | Se envían una sola vez.
        | Alpine filtrará por:
        |
        | idMarca + idTipoEquipo
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
        | Proveedores
        |--------------------------------------------------------------------------
        */

        $proveedores =
            $this->toFrontendOptions(
                $this->proveedores,
                'id_proveedor'
            );

        /*
        |--------------------------------------------------------------------------
        | Condiciones
        |--------------------------------------------------------------------------
        */

        $condiciones =
            $this->toFrontendOptions(
                $this->condiciones,
                'id_valor'
            );

        /*
        |--------------------------------------------------------------------------
        | Áreas
        |--------------------------------------------------------------------------
        */

        $areas =
            $this->toFrontendOptions(
                $this->areas,
                'id_area'
            );

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
        | Configuración de campos dinámicos
        |--------------------------------------------------------------------------
        |
        | No enviamos el nombre de la tabla al navegador.
        | Solamente necesita conocer qué campos dibujar.
        |
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
        | Catálogos utilizados por campos dinámicos
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

            'defaults' => [
                'codigoInventarioPreview' =>
                    $this->codigoInventarioPreview,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Guardado optimista
    |--------------------------------------------------------------------------
    |
    | Recibimos una fotografía completa del formulario.
    | El navegador puede limpiar la interfaz inmediatamente sin afectar
    | los datos que esta petición ya está guardando.
    |
    */
    #[Json]
    public function save(array $payload): array
    {
        $payload = $this->normalizePayload(
            $payload
        );

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
                    'unique:equipos,numero_serie',
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

        $tipoEquipoId =
            (int) $validated['idTipoEquipo'];

        $config =
            $this->childConfig();

        $childFields =
            $config[$tipoEquipoId]['fields']
            ?? [];

        $childTable =
            $config[$tipoEquipoId]['table']
            ?? null;

        $childData =
            is_array($payload['childData'])
                ? $payload['childData']
                : [];

        $codigoInventario =
            $this->generarCodigoInventario();

        $idEquipo = DB::transaction(
            function () use (
                $payload,
                $validated,
                $childFields,
                $childTable,
                $childData,
                $codigoInventario
            ) {
                $idEquipo = DB::table('equipos')
                    ->insertGetId(
                        [
                            'codigo_inventario' =>
                                $codigoInventario,

                            'nombre_equipo' =>
                                $validated['nombreEquipo'],

                            'host' =>
                                $this->nullableString(
                                    $payload['host']
                                ),

                            'id_estado_activo' =>
                                (int) $validated['idEstadoActivo'],

                            'id_tipo_equipo' =>
                                (int) $validated['idTipoEquipo'],

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
                                $validated['numeroFactura'],

                            'numero_serie' =>
                                $validated['numeroSerie'],

                            'id_proveedor' =>
                                $this->nullableInt(
                                    $payload['idProveedor']
                                ),

                            'id_condicion_activo' =>
                                $this->nullableInt(
                                    $payload['idCondicionActivo']
                                ),

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

                            'registrado_por' =>
                                Auth::id(),
                        ],
                        'id_equipo'
                    );

                /*
                |--------------------------------------------------------------------------
                | Tabla hija según tipo
                |--------------------------------------------------------------------------
                */
                if ($childTable) {
                    $row = [
                        'id_equipo' => $idEquipo,
                    ];

                    foreach (
                        $childFields as $field => $type
                    ) {
                        $value =
                            $childData[$field]
                            ?? null;

                        if ($type === 'boolean') {
                            $row[$field] =
                                (bool) ($value ?? false);

                        } elseif (
                            $value === null
                            || $value === ''
                        ) {
                            $row[$field] = null;

                        } elseif ($type === 'number') {
                            $row[$field] =
                                is_numeric($value)
                                    ? $value + 0
                                    : null;

                        } else {
                            $row[$field] =
                                $value;
                        }
                    }

                    DB::table(
                        $childTable
                    )->insert(
                        $row
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Asignación inicial
                |--------------------------------------------------------------------------
                */
                $propietario =
                    trim(
                        (string) $payload['propietario']
                    );

                if ($propietario !== '') {
                    $idTipoAsignacion =
                        DB::table('catalogo_valores')
                            ->join(
                                'catalogos',
                                'catalogos.id_catalogo',
                                '=',
                                'catalogo_valores.id_catalogo'
                            )
                            ->where(
                                'catalogos.clave',
                                'tipo_asignacion'
                            )
                            ->where(
                                'catalogo_valores.clave',
                                'ASIGNACION_INICIAL'
                            )
                            ->value(
                                'catalogo_valores.id_valor'
                            );

                    if ($idTipoAsignacion) {
                        DB::table(
                            'asignaciones'
                        )->insert([
                            'id_equipo' =>
                                $idEquipo,

                            'nombre_colaborador' =>
                                $propietario,

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

                            'id_tipo_asignacion' =>
                                $idTipoAsignacion,

                            'fecha_asignacion' =>
                                now(),

                            'asignado_por' =>
                                Auth::id(),
                        ]);
                    }
                }

                return (int) $idEquipo;
            }
        );

        return [
            'ok' => true,

            'message' =>
                'Equipo añadido correctamente.',

            'record' => [
                'id' =>
                    $idEquipo,

                'code' =>
                    $codigoInventario,

                'name' =>
                    (string) $validated['nombreEquipo'],
            ],

            'nextCode' =>
                'ACT-'
                . str_pad(
                    (string) ($idEquipo + 1),
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
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

    private function nullableInt(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return is_numeric($value)
            ? (int) $value
            : null;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if (
            $value === null
            || ! is_string($value)
        ) {
            return null;
        }

        $value =
            trim($value);

        return $value !== ''
            ? $value
            : null;
    }

    public function getCodigoInventarioPreviewProperty(): string
    {
        $ultimo =
            DB::table('equipos')
                ->max('id_equipo');

        return 'ACT-'
            . str_pad(
                (string) (($ultimo ?? 0) + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    private function generarCodigoInventario(): string
    {
        $ultimo =
            DB::table('equipos')
                ->max('id_equipo');

        return 'ACT-'
            . str_pad(
                (string) (($ultimo ?? 0) + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Placeholder que se muestra mientras el componente
     * se carga de manera lazy.
     */
    public function placeholder()
    {
        return view(
            'livewire.placeholders.equipo-create'
        );
    }

    public function render()
    {
        return view(
            'livewire.equipo-create',
            [
                'equipmentData' =>
                    $this->getEquipmentFrontendData(),
            ]
        );
    }
}