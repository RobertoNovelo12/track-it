<?php

namespace App\Livewire;

use App\Services\TipoEquipoFormBuilderService;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;

use Livewire\Attributes\Json;

use Livewire\Component;

class EquipoEdit extends Component

{

    public int $equipoId;

    public string $codigoInventario = '';

    public int $tipoEquipoOriginal = 0;

    // Información general.*

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

    // Información adicional.*

    public string $propietario = '';

    public string $idCondicionActivo = '';

    public string $idHotel = '';

    public string $idArea = '';

    public string $idDepartamento = '';

    public string $fechaFinGarantia = '';

    public string $comentarios = '';

    public array $childData = [];

    // Fechas que se muestran junto a los botones del formulario.
    #[\Livewire\Attributes\Locked]
    public ?string $fechaRegistro = null;

    #[\Livewire\Attributes\Locked]
    public ?string $fechaActualizacion = null;

    // false si aún no se corrió la migración de fecha_actualizacion:
    // así guardar sigue funcionando sin la columna.
    #[\Livewire\Attributes\Locked]
    public bool $registraModificacion = false;

    public int $formKey = 0;

    // Cachés locales de esta petición.*

    private array $loadedForms = [];

    private array $loadedOptions = [];

    private const EDIT_BASE_CACHE_TTL_MINUTES = 10;

    private const TABLE_CACHE_VERSION_KEY =

        'equipos.index.version';

    private function validarOrganizacion(array $payload): array

    {

        $datos = Validator::make(

            $payload,

            [

                'idHotel' => ['required', 'integer', 'min:1'],

                'idDepartamento' => ['nullable', 'integer', 'min:1'],

            ],

            [

                'idHotel.required' => 'Selecciona una unidad.',

                'idHotel.integer' => 'Selecciona una unidad válida.',

                'idDepartamento.integer' =>

                    'Selecciona un departamento válido.',

            ]

        )->validate();

        $hotel = DB::table('unidades_organizativas')

            ->where('id_unidad', (int) $datos['idHotel'])

            ->where('activo', true)

            ->first([

                'id_unidad',

                'id_area_origen',

            ]);

        if (! $hotel) {

            throw \Illuminate\Validation\ValidationException::withMessages([

                'idHotel' =>

                    'La unidad seleccionada no existe o está inactiva.',

            ]);

        }

        $departamentoId = $this->nullableInt(

            $datos['idDepartamento'] ?? null

        );

        if ($departamentoId !== null) {

            $permitido = DB::table('departamentos as d')

                ->where('d.id_departamento', $departamentoId)

                ->whereNull('d.id_departamento_principal')

                ->where('d.activo', true)

                ->where(function ($query) use ($hotel) {

                    $query

                        ->where('d.atiende_todas_unidades', true)

                        ->orWhereExists(function ($subquery) use ($hotel) {

                            $subquery

                                ->selectRaw('1')

                                ->from('departamento_unidad as dh')

                                ->whereColumn(

                                    'dh.id_departamento',

                                    'd.id_departamento'

                                )

                                ->where(

                                    'dh.id_unidad',

                                    $hotel->id_unidad

                                );

                        });

                })

                ->exists();

            if (! $permitido) {

                throw \Illuminate\Validation\ValidationException::withMessages([

                    'idDepartamento' =>

                        'El departamento no está disponible para esta unidad.',

                ]);

            }

        }

        return [

            'id_unidad' => (int) $hotel->id_unidad,

            'id_departamento' => $departamentoId,

            // Compatibilidad temporal con módulos que aún usan áreas.*

            'id_area' => $departamentoId !== null || $hotel->id_area_origen === null

                ? null

                : (int) $hotel->id_area_origen,

        ];

    }

    private function editBaseCacheKey(): string

    {

        return 'equipos.edit.base.' . $this->equipoId;

    }

    private function invalidateEquipoCaches(): void

    {

        Cache::forget($this->editBaseCacheKey());

        $version = (int) Cache::get(

            self::TABLE_CACHE_VERSION_KEY,

            1

        );

        Cache::forever(

            self::TABLE_CACHE_VERSION_KEY,

            $version + 1

        );

    }

    private function equipoDadoDeBaja(): bool
    {
        return DB::table('equipos as e')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->where('e.id_equipo', $this->equipoId)
            ->where(function ($query): void {
                $query->where('ee.clave', 'BAJA')
                    ->orWhereExists(function ($subquery): void {
                        $subquery->selectRaw('1')
                            ->from('bajas as b')
                            ->whereColumn('b.id_equipo', 'e.id_equipo');
                    });
            })
            ->exists();
    }

    public function mount(int $equipoId): void

    {

        $this->equipoId = $equipoId;

        abort_if($this->equipoDadoDeBaja(), 403, 'No se puede editar un equipo dado de baja.');

        $this->cargarEquipo();

    }

    protected function cargarEquipo(): void

    {

        // Consultar la ubicación actual; la precarga antigua del listado puede estar incompleta.
        $row = DB::table('equipos as e')
            ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'e.id_departamento')
            ->where('e.id_equipo', $this->equipoId)
            ->first(['e.*', 'dep.id_area as departamento_area_id',
                'dep.id_departamento_principal as departamento_principal_id']);
        $data = $row ? (array) $row : null;

        abort_unless($data, 404);

        $this->codigoInventario = (string) (

            $data['codigo_inventario'] ?? ''

        );

        $this->fechaRegistro = $data['fecha_registro'] ?? null;

        $this->registraModificacion = array_key_exists('fecha_actualizacion', $data);

        $this->fechaActualizacion = $data['fecha_actualizacion'] ?? null;

        $this->tipoEquipoOriginal = (int) (

            $data['id_tipo_equipo'] ?? 0

        );

        $map = [

            'nombreEquipo' => 'nombre_equipo',

            'host' => 'host',

            'idEstadoActivo' => 'id_estado_activo',

            'idTipoEquipo' => 'id_tipo_equipo',

            'idModelo' => 'id_modelo',

            'fechaCompra' => 'fecha_compra',

            'idMarca' => 'id_marca',

            'direccionMac' => 'direccion_mac',

            'numeroFactura' => 'numero_factura',

            'numeroSerie' => 'numero_serie',

            'idProveedor' => 'id_proveedor',

            'idCondicionActivo' => 'id_condicion_activo',

            'fechaFinGarantia' => 'fecha_fin_garantia',

            'comentarios' => 'comentarios',

        ];

        foreach ($map as $property => $column) {

            $this->{$property} = (string) (

                $data[$column] ?? ''

            );

        }

        $this->idHotel = (string) ($data['id_unidad'] ?? '');

        $this->idDepartamento = (string) (

            $data['departamento_principal_id'] ?? $data['id_departamento'] ?? ''

        );

        $this->idArea = (string) (

            $this->idDepartamento !== ''

                ? ($data['departamento_area_id'] ?? '')

                : ($data['id_area'] ?? '')

        );

        $this->cargarDatosComplementarios();

    }

    protected function cargarDatosComplementarios(): void

    {

        $tipoId = (int) $this->idTipoEquipo;

        $physical = $this->childConfig()[$tipoId]

            ?? null;

        $query = DB::table('equipos as base')

            ->where(

                'base.id_equipo',

                $this->equipoId

            )

            ->select('base.id_equipo');

        $query->selectSub(

            function ($subQuery): void {

                $subQuery

                    ->from('asignaciones as asignacion')

                    ->select(

                        'asignacion.nombre_colaborador'

                    )

                    ->whereColumn(

                        'asignacion.id_equipo',

                        'base.id_equipo'

                    )

                    // Solo la asignación vigente (sin fecha de fin).
                    ->whereNull('asignacion.fecha_fin')

                    ->orderByDesc(

                        'asignacion.fecha_asignacion'

                    )

                    ->limit(1);

            },

            'responsable_actual'

        );

        if ($physical) {

            $query->leftJoin(

                $physical['table'] . ' as child',

                'child.id_equipo',

                '=',

                'base.id_equipo'

            );

            foreach (

                array_keys($physical['fields'])

                as $column

            ) {

                $query->addSelect('child.' . $column);

            }

        }

        $row = $query->first();

        $this->propietario = (string) (

            $row->responsable_actual ?? ''

        );

        $definitions = $this->configuredChildFields(

            $tipoId

        );

        $attributes = DB::table('atributos_equipo')

            ->where('id_equipo', $this->equipoId)

            ->whereNotNull('id_campo')

            ->pluck('valor', 'id_campo')

            ->all();

        $this->childData = [];

        foreach ($definitions as $key => $field) {

            $value = $field['origen_dato'] === 'atributo'

                ? (

                    $attributes[$field['id_campo']]

                    ?? null

                )

                : (

                    $row->{$field['columna_destino']}

                    ?? null

                );

            $this->childData[$key] = $this->frontendValue(

                $value,

                $field['tipo_control']

            );

        }

    }

    private function frontendValue(

        mixed $value,

        string $type

    ): mixed {

        if ($type === 'boolean') {

            return in_array(

                $value,

                [true, 1, '1', 'true', 't'],

                true

            );

        }

        return $value === null

            ? ''

            : (string) $value;

    }

    private function toPlainArray($collection): array

    {

        return $collection

            ->map(fn ($row) => (array) $row)

            ->all();

    }

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

    private bool $formsLoaded = false;

    private function configuredForm(int $tipoId): array

    {

        if (array_key_exists($tipoId, $this->loadedForms)) {

            return $this->loadedForms[$tipoId];

        }

        if (! $this->formsLoaded) {

            $sections = DB::table('tipo_equipo_secciones')

                ->orderBy('orden')

                ->orderBy('id_seccion')

                ->get()

                ->groupBy('id_tipo_equipo');

            $fields = DB::table('tipo_equipo_campos as tec')

                ->join(

                    'campos_equipo as ce',

                    'ce.id_campo',

                    '=',

                    'tec.id_campo'

                )

                ->where('ce.activo', true)

                ->where('tec.visible', true)

                ->whereIn('tec.origen_dato', ['tabla_tipo', 'atributo'])

                ->orderBy('tec.orden')

                ->orderBy('tec.id_tipo_campo')

                ->get([

                    'tec.*',

                    'ce.clave',

                    'ce.nombre as nombre_base',

                    'ce.tipo_control',

                    'ce.descripcion',

                    'ce.placeholder',

                ]);

            $selects = $fields->where('tipo_control', 'select');

            $customIds = $selects

                ->where('fuente_opciones', 'personalizado')

                ->pluck('id_tipo_campo')

                ->unique()

                ->values()

                ->all();

            $catalogIds = $selects

                ->where('fuente_opciones', 'catalogo')

                ->pluck('id_catalogo')

                ->filter()

                ->unique()

                ->values()

                ->all();

            $customOptions = $customIds

                ? DB::table('tipo_equipo_campo_opciones')

                    ->whereIn('id_tipo_campo', $customIds)

                    ->where('activo', true)

                    ->orderBy('orden')

                    ->orderBy('id_opcion')

                    ->get([

                        'id_tipo_campo',

                        'id_opcion as value',

                        'nombre as label',

                    ])

                    ->groupBy('id_tipo_campo')

                : collect();

            $catalogOptions = $catalogIds

                ? DB::table('catalogo_valores')

                    ->whereIn('id_catalogo', $catalogIds)

                    ->where('activo', true)

                    ->orderBy('orden')

                    ->orderBy('nombre')

                    ->get([

                        'id_catalogo',

                        'id_valor as value',

                        'nombre as label',

                    ])

                    ->groupBy('id_catalogo')

                : collect();

            foreach ($sections as $id => $items) {

                $this->loadedForms[(int) $id] = [

                    'sections' => $items->map(function ($item): array {

                        $section = (array) $item;

                        $section['es_base'] = (bool) $item->es_base;

                        return $section;

                    })->values()->all(),

                    'fields' => [],

                ];

            }

            foreach ($fields as $row) {

                $id = (int) $row->id_tipo_equipo;

                $field = (array) $row;

                $field['etiqueta'] = trim((string) $row->etiqueta) !== ''

                    ? $row->etiqueta

                    : $row->nombre_base;

                $field['visible'] = (bool) $row->visible;

                $field['obligatorio'] = (bool) $row->obligatorio;

                $this->loadedForms[$id] ??= [

                    'sections' => [],

                    'fields' => [],

                ];

                $this->loadedForms[$id]['fields'][] = $field;

                if ($row->tipo_control !== 'select') {

                    continue;

                }

                $options = match ($row->fuente_opciones) {

                    'personalizado' => $customOptions->get(

                        $row->id_tipo_campo,

                        collect()

                    ),

                    'catalogo' => $catalogOptions->get(

                        $row->id_catalogo,

                        collect()

                    ),

                    default => null,

                };

                if ($options !== null) {

                    $this->loadedOptions[(string) $row->id_tipo_campo] =

                        $options->map(fn ($option): array => [

                            'value' => (string) $option->value,

                            'label' => (string) $option->label,

                        ])->values()->all();

                }

            }

            $this->formsLoaded = true;

        }

        return $this->loadedForms[$tipoId] ?? [

            'sections' => [],

            'fields' => [],

        ];

    }

    private function configuredChildFields(

        int $tipoId

    ): array {

        $form = $this->configuredForm($tipoId);

        $physical = $this->childConfig()[$tipoId]

            ?? null;

        $result = [];

        foreach ($form['fields'] as $field) {

            if (! $field['visible']) {

                continue;

            }

            if (

                ! in_array(

                    $field['origen_dato'],

                    ['tabla_tipo', 'atributo'],

                    true

                )

            ) {

                continue;

            }

            if ($field['origen_dato'] === 'tabla_tipo') {

                $column = $field['columna_destino'];

                if (

                    ! $physical

                    || $field['tabla_destino'] !== $physical['table']

                    || ! array_key_exists(

                        (string) $column,

                        $physical['fields']

                    )

                ) {

                    throw new \RuntimeException(

                        'El campo '

                        . $field['etiqueta']

                        . ' tiene un destino físico no reconocido.'

                    );

                }

            }

            $key = 'tipo_'

                . $tipoId

                . '_campo_'

                . $field['id_campo'];

            $result[$key] = $field;

        }

        return $result;

    }

    private function frontendType(

        string $key,

        array $field

    ): string {

        return $field['tipo_control'] === 'select'

            ? 'catalog:' . $key

            : $field['tipo_control'];

    }

    private function fieldOptions(array $field): array

    {

        if ($field['tipo_control'] !== 'select') {

            return [];

        }

        $cacheKey = (string) $field['id_tipo_campo'];

        if (array_key_exists($cacheKey, $this->loadedOptions)) {

            return $this->loadedOptions[$cacheKey];

        }

        if ($field['fuente_opciones'] === 'personalizado') {

            $items = DB::table('tipo_equipo_campo_opciones')

                ->where(

                    'id_tipo_campo',

                    $field['id_tipo_campo']

                )

                ->where('activo', true)

                ->orderBy('orden')

                ->orderBy('id_opcion')

                ->get([

                    'id_opcion as value',

                    'nombre as label',

                ]);

        } elseif ($field['fuente_opciones'] === 'catalogo') {

            $items = DB::table('catalogo_valores')

                ->where(

                    'id_catalogo',

                    $field['id_catalogo']

                )

                ->where('activo', true)

                ->orderBy('orden')

                ->orderBy('nombre')

                ->get([

                    'id_valor as value',

                    'nombre as label',

                ]);

        } else {

            $sources = [

                'estados_equipo' => [

                    'estados_equipo',

                    'id_estado_equipo',

                ],

                'marcas' => [

                    'marcas',

                    'id_marca',

                ],

                'modelos' => [

                    'modelos',

                    'id_modelo',

                ],

                'proveedores' => [

                    'proveedores',

                    'id_proveedor',

                ],

                'unidades_organizativas' => ['unidades_organizativas', 'id_unidad'],
                'areas' => [

                    'areas',

                    'id_area',

                ],

                'departamentos' => [

                    'departamentos',

                    'id_departamento',

                ],

            ];

            $source = $sources[

                $field['fuente_opciones'] ?? ''

            ] ?? null;

            if (! $source) {

                throw new \RuntimeException(

                    'La lista '

                    . $field['etiqueta']

                    . ' no tiene una fuente de opciones reconocida.'

                );

            }

            $items = DB::table($source[0])

                ->where('activo', true)

                ->orderBy('nombre')

                ->get([

                    $source[1] . ' as value',

                    'nombre as label',

                ]);

        }

        return $this->loadedOptions[$cacheKey] = $items

            ->map(fn ($item): array => [

                'value' => (string) $item->value,

                'label' => (string) $item->label,

            ])

            ->values()

            ->all();

    }

    public function getChildFieldsProperty(): array

    {

        if ($this->idTipoEquipo === '') {

            return [];

        }

        $result = [];

        foreach (

            $this->configuredChildFields(

                (int) $this->idTipoEquipo

            ) as $key => $field

        ) {

            $result[$key] = $this->frontendType(

                $key,

                $field

            );

        }

        return $result;

    }

    public function getFieldLabelsProperty(): array

    {

        if ($this->idTipoEquipo === '') {

            return [];

        }

        $result = [];

        foreach (

            $this->configuredChildFields(

                (int) $this->idTipoEquipo

            ) as $key => $field

        ) {

            $result[$key] = $field['etiqueta'];

        }

        return $result;

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

            fn () => $this->toPlainArray(

                DB::table('estados_equipo')

                    ->where('activo', true)

                    ->orderBy('orden')

                    ->get(['id_estado_equipo', 'nombre'])

            )

        );

    }

    public function getTiposEquipoProperty(): array

    {

        return Cache::remember(

            'form.tipos_equipo.v2',

            now()->addMinutes(30),

            fn () => $this->toPlainArray(

                DB::table('tipos_equipo')

                    ->where('activo', true)

                    ->orderBy('nombre')

                    ->get(['id_tipo_equipo', 'nombre'])

            )

        );

    }

    public function getMarcasProperty(): array

    {

        return Cache::remember(

            'form.marcas.v2',

            now()->addMinutes(30),

            fn () => $this->toPlainArray(

                DB::table('marcas')

                    ->where('activo', true)

                    ->orderBy('nombre')

                    ->get(['id_marca', 'nombre'])

            )

        );

    }

    private function getAllModelos(): array

    {

        return Cache::remember(

            'form.modelos.all.v2',

            now()->addMinutes(30),

            fn () => $this->toPlainArray(

                DB::table('modelos')

                    ->where('activo', true)

                    ->orderBy('nombre')

                    ->get([

                        'id_modelo',

                        'id_marca',

                        'id_tipo_equipo',

                        'nombre',

                    ])

            )

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

        return collect($this->getAllModelos())

            ->filter(

                fn (array $modelo): bool =>

                    (string) $modelo['id_marca']

                        === $this->idMarca

                    && (string) $modelo['id_tipo_equipo']

                        === $this->idTipoEquipo

            )

            ->values()

            ->all();

    }

    public function getProveedoresProperty(): array

    {

        return Cache::remember(

            'form.proveedores.v2',

            now()->addMinutes(30),

            fn () => $this->toPlainArray(

                DB::table('proveedores')

                    ->where('activo', true)

                    ->orderBy('nombre')

                    ->get(['id_proveedor', 'nombre'])

            )

        );

    }

    public function getCondicionesProperty(): array

    {

        return Cache::remember(

            'form.condicion_activo.v2',

            now()->addMinutes(30),

            fn () => $this->catalogoValores(

                'condicion_activo'

            )

        );

    }

    public function getAreasProperty(): array

    {

        return Cache::remember(

            'form.areas.v2',

            now()->addMinutes(30),

            fn () => $this->toPlainArray(

                DB::table('areas')

                    ->where('activo', true)

                    ->orderBy('nombre')

                    ->get(['id_area', 'nombre'])

            )

        );

    }

    public function getHotelesProperty(): array

    {

        return $this->toPlainArray(

            DB::table('unidades_organizativas')

                ->where('activo', true)

                ->orderBy('nombre')

                ->get(['id_unidad', 'nombre'])

        );

    }

    private function getAllDepartamentos(): array

    {

        $departamentos = DB::table('departamentos')

            ->whereNull('id_departamento_principal')

            ->where('activo', true)

            ->orderBy('nombre')

            ->get([

                'id_departamento',

                'nombre',

                'atiende_todas_unidades',

            ]);

        $vinculos = DB::table('departamento_unidad')

            ->whereIn(

                'id_departamento',

                $departamentos->pluck('id_departamento')

            )

            ->get([

                'id_departamento',

                'id_unidad',

            ])

            ->groupBy('id_departamento');

        return $departamentos->map(fn ($item): array => [

            'id_departamento' => (int) $item->id_departamento,

            'nombre' => (string) $item->nombre,

            'atiende_todas_unidades' => (bool) $item->atiende_todas_unidades,

            'id_hoteles' => $vinculos

                ->get($item->id_departamento, collect())

                ->map(

                    fn ($vinculo): string => (string) $vinculo->id_unidad

                )

                ->values()

                ->all(),

        ])->all();

    }

    public function getDepartamentosProperty(): array

    {

        if ($this->idHotel === '') {

            return [];

        }

        return array_values(array_filter(

            $this->getAllDepartamentos(),

            fn (array $item): bool =>

                $item['atiende_todas_unidades']

                || in_array(

                    $this->idHotel,

                    $item['id_hoteles'],

                    true

                )

        ));

    }

    public function catalogOptions(string $clave): array

    {

        return Cache::remember(

            'form.catalogo.' . $clave . '.v2',

            now()->addMinutes(30),

            fn () => $this->catalogoValores($clave)

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

                ->where('catalogos.clave', $clave)

                ->where('catalogo_valores.activo', true)

                ->orderBy('catalogo_valores.orden')

                ->get([

                    'catalogo_valores.id_valor',

                    'catalogo_valores.nombre',

                ])

        );

    }

    private function toFrontendOptions(

        array $items,

        string $valueKey,

        string $labelKey = 'nombre'

    ): array {

        return collect($items)

            ->map(fn (array $item): array => [

                'value' => (string) (

                    $item[$valueKey] ?? ''

                ),

                'label' => (string) (

                    $item[$labelKey] ?? ''

                ),

            ])

            ->values()

            ->all();

    }

    private function getEquipmentFrontendData(): array

    {

        $baseFieldsByType = [];

        $childFieldsByType = [];

        $childFieldMetaByType = [];

        $sectionsByType = [];

        $labels = [];

        $catalogs = [];

        $tipoIds = array_map(

            fn (array $tipo): int =>

                (int) $tipo['id_tipo_equipo'],

            $this->tiposEquipo

        );

        // Cargar también la configuración del tipo actual.*

        $tipoIds[] = $this->tipoEquipoOriginal;

        foreach (

            array_unique(array_filter($tipoIds))

            as $tipoId

        ) {

            $form = $this->configuredForm($tipoId);
            $baseFieldsByType[$tipoId] = [];
            foreach ($form['fields'] as $field) {
                if (! $field['visible'] || ! in_array($field['origen_dato'], ['equipos', 'asignacion'], true)) {
                    continue;
                }
                $baseFieldsByType[$tipoId][$field['clave']] = [
                    'etiqueta' => $field['etiqueta'],
                    'id_seccion' => $field['id_seccion'],
                    'obligatorio' => (bool) $field['obligatorio'],
                    'orden' => (int) $field['orden'],
                ];
            }


            $childFieldsByType[$tipoId] = [];

            $childFieldMetaByType[$tipoId] = [];

            $sectionsByType[$tipoId] = array_map(

                fn (array $section): array => [

                    'id_seccion' => $section['id_seccion'],

                    'clave' => $section['clave'],

                    'nombre' => $section['nombre'],

                    'orden' => $section['orden'],

                    'es_base' => $section['es_base'],

                ],

                $form['sections']

            );

            foreach (

                $this->configuredChildFields($tipoId)

                as $key => $field

            ) {

                $childFieldsByType[$tipoId][$key] =

                    $this->frontendType($key, $field);

                $labels[$key] = $field['etiqueta'];

                $childFieldMetaByType[$tipoId][$key] = [

                    'orden' => (int) $field['orden'],

                    'id_campo' => $field['id_campo'],

                    'id_seccion' => $field['id_seccion'],

                    'etiqueta' => $field['etiqueta'],

                    'tipo_control' => $field['tipo_control'],

                    'obligatorio' => $field['obligatorio'],

                    'placeholder' => $field['placeholder'],

                    'descripcion' => $field['descripcion'],

                ];

                if ($field['tipo_control'] === 'select') {

                    $catalogs[$key] =

                        $this->fieldOptions($field);

                }

            }

        }

        $initial = [

            'equipoId' => $this->equipoId,

            'codigoInventario' => $this->codigoInventario,

            'tipoEquipoOriginal' =>

                (string) $this->tipoEquipoOriginal,

            'childData' => $this->childData,

        ];

        foreach ([

            'nombreEquipo',

            'host',

            'idEstadoActivo',

            'idTipoEquipo',

            'idModelo',

            'fechaCompra',

            'idMarca',

            'direccionMac',

            'numeroFactura',

            'numeroSerie',

            'idProveedor',

            'propietario',

            'idCondicionActivo',

            'idArea',

            'idHotel',

            'idDepartamento',

            'fechaFinGarantia',

            'comentarios',

        ] as $property) {

            $initial[$property] = $this->{$property};

        }

        return [

            'estados' => $this->toFrontendOptions(

                $this->estados,

                'id_estado_equipo'

            ),

            'tiposEquipo' => $this->toFrontendOptions(

                $this->tiposEquipo,

                'id_tipo_equipo'

            ),

            'marcas' => $this->toFrontendOptions(

                $this->marcas,

                'id_marca'

            ),

            'modelos' => array_map(

                fn (array $modelo): array => [

                    'value' => (string) $modelo['id_modelo'],

                    'label' => (string) $modelo['nombre'],

                    'idMarca' => (string) $modelo['id_marca'],

                    'idTipoEquipo' => (string) $modelo['id_tipo_equipo'],

                ],

                $this->getAllModelos()

            ),

            'proveedores' => $this->toFrontendOptions(

                $this->proveedores,

                'id_proveedor'

            ),

            'condiciones' => $this->toFrontendOptions(

                $this->condiciones,

                'id_valor'

            ),

            'hoteles' => $this->toFrontendOptions($this->hoteles, 'id_unidad'),

            'areas' => $this->toFrontendOptions(

                $this->areas,

                'id_area'

            ),

            'departamentos' => array_map(

                fn (array $item): array => [

                    'value' => (string) $item['id_departamento'],

                    'label' => (string) $item['nombre'],

                    'atiendeTodosHoteles' => $item['atiende_todas_unidades'],
                    'idHoteles' => $item['id_hoteles'],

                ],

                $this->getAllDepartamentos()

            ),

            'childFieldsByType' => $childFieldsByType,

            'baseFieldsByType' => $baseFieldsByType,

            'childFieldMetaByType' => $childFieldMetaByType,

            'sectionsByType' => $sectionsByType,

            'fieldLabels' => $labels,

            'dynamicCatalogs' => $catalogs,

            'initial' => $initial,

        ];

    }

    private function validatedChildData(

        array $input,

        array $definitions

    ): array {

        $values = [];

        $rules = [];

        $labels = [];

        foreach ($definitions as $key => $field) {

            $type = $field['tipo_control'];

            $value = $input[$key] ?? null;

            if (is_string($value)) {

                $value = trim($value);

                $value = $value === ''

                    ? null

                    : $value;

            }

            if ($type === 'boolean' && $value === null) {

                $value = false;

            }

            $values[$key] = $value;

            $path = 'childData.' . $key;

            $labels[$path] = $field['etiqueta'];

            $rules[$path] = [

                $field['obligatorio']

                    ? 'required'

                    : 'nullable',

            ];

            $typeRules = match ($type) {

                'text' => [

                    'string',

                    'max:255',

                ],

                'textarea' => [

                    'string',

                ],

                'number' => [

                    'numeric',

                ],

                'date' => [

                    'date_format:Y-m-d',

                ],

                'boolean' => [

                    'boolean',

                ],

                'select' => [

                    'integer',

                    Rule::in(

                        array_column(

                            $this->fieldOptions($field),

                            'value'

                        )

                    ),

                ],

                default => throw new \RuntimeException(

                    'Tipo de campo no reconocido: ' . $type

                ),

            };

            $rules[$path] = array_merge(

                $rules[$path],

                $typeRules

            );

        }

        Validator::make(

            ['childData' => $values],

            $rules,

            [],

            $labels

        )->validate();

        foreach ($definitions as $key => $field) {

            if ($field['tipo_control'] === 'boolean') {

                $values[$key] = (bool) $values[$key];

            }

        }

        return $values;

    }

    #[Json]

    public function save(array $payload): array

    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');


        abort_if($this->equipoDadoDeBaja(), 403, 'No se puede editar un equipo dado de baja.');

        $payload = $this->normalizePayload($payload);

        // El tipo de equipo no se cambia al editar: siempre el registrado.
        $payload['idTipoEquipo'] = (string) DB::table('equipos')
            ->where('id_equipo', $this->equipoId)
            ->value('id_tipo_equipo');

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

                    'integer',

                    Rule::exists(

                        'tipos_equipo',

                        'id_tipo_equipo'

                    ),

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

                'idHotel' => ['required', 'integer', 'min:1'],

                'childData' => [

                    'array',

                ],

            ]

        )->validate();

        $organizacion = $this->validarOrganizacion($payload);

        $tipoNuevo = (int) $validated['idTipoEquipo'];

        $definitions = $this->configuredChildFields(

            $tipoNuevo

        );

        $values = $this->validatedChildData(

            $payload['childData'],

            $definitions

        );

        $estadoAnterior = (int) DB::table('equipos')
            ->where('id_equipo', $this->equipoId)
            ->value('id_estado_activo');

        // Misma marca de tiempo para la base de datos y para la pantalla.
        $momentoGuardado = now();

        $record = DB::transaction(

            function () use (

                $momentoGuardado,

                $payload,

                $validated,

                $tipoNuevo,

                $definitions,

                $values,

                $organizacion

            ): array {

                $equipo = DB::table('equipos')

                    ->where(

                        'id_equipo',

                        $this->equipoId

                    )

                    ->lockForUpdate()

                    ->first();

                abort_unless($equipo, 404);

                abort_if($this->equipoDadoDeBaja(), 403, 'No se puede editar un equipo dado de baja.');

                $tipoAnterior = (int) $equipo->id_tipo_equipo;

                $configAnterior = $this->childConfig()[

                    $tipoAnterior

                ] ?? null;

                $configNuevo = $this->childConfig()[

                    $tipoNuevo

                ] ?? null;

                if (

                    $tipoAnterior !== $tipoNuevo

                    && $configAnterior

                    && $configAnterior['table']

                        !== ($configNuevo['table'] ?? null)

                ) {

                    DB::table($configAnterior['table'])

                        ->where(

                            'id_equipo',

                            $this->equipoId

                        )

                        ->delete();

                }

                DB::table('equipos')

                    ->where(

                        'id_equipo',

                        $this->equipoId

                    )

                    ->update([

                        'nombre_equipo' => trim(

                            (string) $validated['nombreEquipo']

                        ),

                        'host' => $this->nullableString(

                            $payload['host']

                        ),

                        'id_estado_activo' =>

                            (int) $validated['idEstadoActivo'],

                        'id_tipo_equipo' =>

                            $tipoNuevo,

                        'id_modelo' => $this->nullableInt(

                            $payload['idModelo']

                        ),

                        'fecha_compra' => $this->nullableString(

                            $payload['fechaCompra']

                        ),

                        'id_marca' =>

                            (int) $validated['idMarca'],

                        'direccion_mac' => $this->nullableString(

                            $payload['direccionMac']

                        ),

                        'numero_factura' => trim(

                            (string) $validated['numeroFactura']

                        ),

                        'numero_serie' => trim(

                            (string) $validated['numeroSerie']

                        ),

                        'id_proveedor' => $this->nullableInt(

                            $payload['idProveedor']

                        ),

                        'id_condicion_activo' => $this->nullableInt(

                            $payload['idCondicionActivo']

                        ),

                        'id_unidad' => $organizacion['id_unidad'],

                        'id_area' => $organizacion['id_area'],

                        'id_departamento' => $organizacion['id_departamento'],

                        'fecha_fin_garantia' => $this->nullableString(

                            $payload['fechaFinGarantia']

                        ),

                        'comentarios' => $this->nullableString(

                            $payload['comentarios']

                        ),


                        ...($this->registraModificacion
                            ? ['fecha_actualizacion' => $momentoGuardado]
                            : []),

                    ]);

                if ($configNuevo) {

                    $exists = DB::table(

                        $configNuevo['table']

                    )

                        ->where(

                            'id_equipo',

                            $this->equipoId

                        )

                        ->exists();

                    $row = [];

                    if (! $exists) {

                        foreach (

                            $configNuevo['fields']

                            as $column => $type

                        ) {

                            $row[$column] = $type === 'boolean'

                                ? false

                                : null;

                        }

                    }

                    foreach (

                        $definitions

                        as $key => $field

                    ) {

                        if (

                            $field['origen_dato'] === 'tabla_tipo'

                            && array_key_exists(

                                $key,

                                $payload['childData']

                            )

                        ) {

                            $row[

                                $field['columna_destino']

                            ] = $values[$key];

                        }

                    }

                    if ($row !== []) {

                        DB::table($configNuevo['table'])

                            ->updateOrInsert(

                                [

                                    'id_equipo' =>

                                        $this->equipoId,

                                ],

                                $row

                            );

                    }

                }

                foreach ($definitions as $key => $field) {

                    if (

                        $field['origen_dato'] !== 'atributo'

                        || ! array_key_exists(

                            $key,

                            $payload['childData']

                        )

                    ) {

                        continue;

                    }

                    $identity = [

                        'id_equipo' => $this->equipoId,

                        'id_campo' => (int) $field['id_campo'],

                    ];

                    $value = $values[$key];

                    if ($value === null) {

                        // Vaciar un campo opcional elimina solo su valor.*

                        DB::table('atributos_equipo')

                            ->where($identity)

                            ->delete();

                        continue;

                    }

                    DB::table('atributos_equipo')

                        ->updateOrInsert(

                            $identity,

                            [

                                'nombre' =>

                                    $field['etiqueta'],

                                'valor' => is_bool($value)

                                    ? ($value ? '1' : '0')

                                    : (string) $value,

                            ]

                        );

                }

                return [

                    'id' => $this->equipoId,

                    'code' =>

                        (string) $equipo->codigo_inventario,

                    'name' => trim(

                        (string) $validated['nombreEquipo']

                    ),

                    'typeId' => $tipoNuevo,

                ];

            }

        );

        $this->invalidateEquipoCaches();

        $notificaciones = app(\App\Services\NotificacionService::class);

        $estadoNuevo = (int) DB::table('equipos')
            ->where('id_equipo', $this->equipoId)
            ->value('id_estado_activo');

        $notificaciones->notificar(
            'equipos',
            $estadoAnterior !== $estadoNuevo
                ? 'Cambio de estado de equipo'
                : 'Equipo modificado',
            $notificaciones->nombreActor()
                . ($estadoAnterior !== $estadoNuevo
                    ? ' cambió el estado de '
                        . $notificaciones->etiquetaEquipo($this->equipoId)
                        . ' a «' . ($notificaciones->estadoDeEquipo($this->equipoId) ?? 'otro estado') . '».'
                    : ' modificó la información de '
                        . $notificaciones->etiquetaEquipo($this->equipoId)
                        . '.'),
            route('equipos.show', $this->equipoId, false)
        );

        $this->dispatch('notificaciones-actualizadas');

        $this->tipoEquipoOriginal = $tipoNuevo;

        if ($this->registraModificacion) {
            $this->fechaActualizacion = $momentoGuardado->toDateTimeString();

            // save() es #[Json] (no vuelve a renderizar): la vista
            // actualiza la fecha mostrada con este evento.
            $this->dispatch(
                'equipo-fecha-actualizada',
                fecha: \App\Support\Fecha::conHora($this->fechaActualizacion),
            );
        }

        return [

            'ok' => true,

            'message' =>

                'Los cambios se guardaron correctamente.',

            'record' => $record,

        ];

    }

    private function normalizePayload(array $payload): array

    {

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

                'idHotel' => '',

                'idArea' => '',

                'idDepartamento' => '',

                'fechaFinGarantia' => '',

                'comentarios' => '',

                'childData' => [],

            ],

            $payload

        );

    }

    private function nullableInt(mixed $value): ?int

    {

        if ($value === null || $value === '') {

            return null;

        }

        return is_numeric($value)

            ? (int) $value

            : null;

    }

    private function nullableString(mixed $value): ?string

    {

        if ($value === null || ! is_string($value)) {

            return null;

        }

        $value = trim($value);

        return $value !== ''

            ? $value

            : null;

    }

    public function placeholder()

    {

        return view(

            'livewire.placeholders.equipo-edit'

        );

    }

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