<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TipoEquipoFormBuilderService
{
    private const PROTECTED_FIELDS = [
        'nombre_equipo',
        'id_estado_activo',
        'id_marca',
        'numero_serie',
        'numero_factura',
        'id_unidad',
    ];


    private const CONTROL_TYPES = [
        'text',
        'textarea',
        'number',
        'date',
        'boolean',
        'select',
    ];


    /*
    |--------------------------------------------------------------------------
    | Cargar constructor
    |--------------------------------------------------------------------------
    */

    public function load(
        int $tipoEquipoId
    ): array {
        $tipo = DB::table('tipos_equipo')
            ->where(
                'id_tipo_equipo',
                $tipoEquipoId
            )
            ->first([
                'id_tipo_equipo',
                'nombre',
                'descripcion',
                'activo',
            ]);


        abort_unless(
            $tipo,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Garantizar las tres secciones base
        |--------------------------------------------------------------------------
        |
        | Esto también cubre tipos de equipo nuevos.
        |
        | Si el usuario ya renombró alguna sección base, no la sobrescribimos.
        |
        */

        $sections = $this->ensureRequiredFields($tipoEquipoId);


        $sectionsById =
            collect($sections)
                ->keyBy(
                    'id_seccion'
                );


        $sectionIdsByKey =
            collect($sections)
                ->pluck(
                    'id_seccion',
                    'clave'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->all();


        $sectionOrderById =
            collect($sections)
                ->pluck(
                    'orden',
                    'id_seccion'
                )
                ->map(
                    fn ($order) =>
                        (int) $order
                )
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Campos disponibles + configuración del tipo
        |--------------------------------------------------------------------------
        */

        $rows = DB::table(
            'campos_equipo as ce'
        )
            ->leftJoin(
                'tipo_equipo_campos as tec',
                function ($join) use (
                    $tipoEquipoId
                ): void {
                    $join
                        ->on(
                            'tec.id_campo',
                            '=',
                            'ce.id_campo'
                        )
                        ->where(
                            'tec.id_tipo_equipo',
                            '=',
                            $tipoEquipoId
                        );
                }
            )
            ->where(
                'ce.activo',
                true
            )
            ->where(
                function ($query): void {
                    $query
                        ->where(
                            'ce.es_sistema',
                            true
                        )
                        ->orWhereNotNull(
                            'tec.id_tipo_campo'
                        );
                }
            )
            ->get([
                'ce.id_campo',
                'ce.clave',
                'ce.nombre',
                'ce.tipo_control',
                'ce.descripcion',
                'ce.placeholder',
                'ce.es_sistema',

                'tec.id_tipo_campo',
                'tec.etiqueta',
                'tec.origen_dato',
                'tec.tabla_destino',
                'tec.columna_destino',
                'tec.fuente_opciones',
                'tec.id_catalogo',

                'tec.id_seccion',
                'tec.seccion',

                'tec.visible',
                'tec.obligatorio',
                'tec.orden',
            ]);


        /*
        |--------------------------------------------------------------------------
        | IDs
        |--------------------------------------------------------------------------
        */

        $fieldIds =
            $rows
                ->pluck(
                    'id_campo'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values()
                ->all();


        $typeFieldIds =
            $rows
                ->pluck(
                    'id_tipo_campo'
                )
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values()
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Metadatos de referencia
        |--------------------------------------------------------------------------
        |
        | Se utilizan cuando un campo maestro todavía no pertenece al tipo
        | actual.
        |
        */

        $metadataByField =
            $this->loadReferenceMetadata(
                $fieldIds
            );


        /*
        |--------------------------------------------------------------------------
        | Opciones personalizadas
        |--------------------------------------------------------------------------
        */

        $customOptions =
            empty(
                $typeFieldIds
            )
                ? collect()
                : DB::table(
                    'tipo_equipo_campo_opciones'
                )
                    ->whereIn(
                        'id_tipo_campo',
                        $typeFieldIds
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->orderBy(
                        'orden'
                    )
                    ->orderBy(
                        'id_opcion'
                    )
                    ->get([
                        'id_opcion',
                        'id_tipo_campo',
                        'nombre',
                        'orden',
                    ])
                    ->groupBy(
                        'id_tipo_campo'
                    );


        /*
        |--------------------------------------------------------------------------
        | Construcción de campos
        |--------------------------------------------------------------------------
        */

        $fields =
            $rows
                ->map(
                    function ($row) use (
                        $metadataByField,
                        $customOptions,
                        $sectionsById,
                        $sectionIdsByKey
                    ): array {
                        $idCampo =
                            (int) $row->id_campo;


                        $idTipoCampo =
                            $row->id_tipo_campo !== null
                                ? (int) $row->id_tipo_campo
                                : null;


                        /*
                        |--------------------------------------------------------------------------
                        | Metadatos
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $idTipoCampo !== null
                        ) {
                            $metadata = [
                                'origen_dato' =>
                                    $row->origen_dato,

                                'tabla_destino' =>
                                    $row->tabla_destino,

                                'columna_destino' =>
                                    $row->columna_destino,

                                'fuente_opciones' =>
                                    $row->fuente_opciones,

                                'id_catalogo' =>
                                    $row->id_catalogo,

                                'seccion' =>
                                    $row->seccion
                                    ?? 'adicional',
                            ];
                        } else {
                            $metadata =
                                $this->normalizeReferenceMetadata(
                                    $metadataByField[
                                        $idCampo
                                    ]
                                    ?? null
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Sección real
                        |--------------------------------------------------------------------------
                        */

                        $idSeccion =
                            null;


                        if (
                            $row->id_seccion !== null
                            &&
                            $sectionsById->has(
                                (int) $row->id_seccion
                            )
                        ) {
                            $idSeccion =
                                (int) $row->id_seccion;
                        }


                        if (
                            $idSeccion === null
                        ) {
                            $idSeccion =
                                $this->resolveSectionIdFromLegacy(
                                    (string) (
                                        $metadata['seccion']
                                        ?? 'adicional'
                                    ),
                                    $sectionIdsByKey
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Nombre visible
                        |--------------------------------------------------------------------------
                        */

                        $baseName =
                            (string) $row->nombre;


                        $label =
                            trim(
                                (string) (
                                    $row->etiqueta
                                    ?? ''
                                )
                            );


                        if (
                            $label === ''
                        ) {
                            $label =
                                $baseName;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Protegido
                        |--------------------------------------------------------------------------
                        */

                        $protected =
                            in_array(
                                (string) $row->clave,
                                self::PROTECTED_FIELDS,
                                true
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Opciones personalizadas
                        |--------------------------------------------------------------------------
                        */

                        $options =
                            $idTipoCampo !== null
                                ? (
                                    $customOptions
                                        ->get(
                                            $idTipoCampo,
                                            collect()
                                        )
                                        ->map(
                                            fn ($option): array => [
                                                'id_opcion' =>
                                                    (int) $option->id_opcion,

                                                'nombre' =>
                                                    (string) $option->nombre,
                                            ]
                                        )
                                        ->values()
                                        ->all()
                                )
                                : [];


                        return [
                            'uid' =>
                                'field-'
                                . $idCampo,

                            'id_campo' =>
                                $idCampo,

                            'id_tipo_campo' =>
                                $idTipoCampo,

                            'clave' =>
                                (string) $row->clave,

                            'nombre_base' =>
                                $baseName,

                            'etiqueta' =>
                                $label,

                            'tipo_control' =>
                                (string) $row->tipo_control,

                            'descripcion' =>
                                (string) (
                                    $row->descripcion
                                    ?? ''
                                ),

                            'placeholder' =>
                                (string) (
                                    $row->placeholder
                                    ?? ''
                                ),

                            'es_sistema' =>
                                (bool) $row->es_sistema,

                            'origen_dato' =>
                                $metadata[
                                    'origen_dato'
                                ],

                            'tabla_destino' =>
                                $metadata[
                                    'tabla_destino'
                                ],

                            'columna_destino' =>
                                $metadata[
                                    'columna_destino'
                                ],

                            'fuente_opciones' =>
                                $metadata[
                                    'fuente_opciones'
                                ],

                            'id_catalogo' =>
                                $metadata[
                                    'id_catalogo'
                                ],

                            /*
                            |--------------------------------------------------------------------------
                            | Nueva relación real
                            |--------------------------------------------------------------------------
                            */

                            'id_seccion' =>
                                $idSeccion,

                            /*
                            |--------------------------------------------------------------------------
                            | Legacy
                            |--------------------------------------------------------------------------
                            |
                            | Se mantiene temporalmente porque todavía existen
                            | partes del sistema que utilizan esta clave.
                            |
                            */

                            'seccion' =>
                                $metadata[
                                    'seccion'
                                ],

                            'visible' =>
                                $idTipoCampo !== null
                                    ? (bool) $row->visible
                                    : false,

                            'obligatorio' =>
                                $protected
                                    ? true
                                    : (
                                        $idTipoCampo !== null
                                            ? (bool) $row->obligatorio
                                            : false
                                    ),

                            'orden' =>
                                $row->orden !== null
                                    ? (int) $row->orden
                                    : 999999,

                            'protected' =>
                                $protected,

                            'is_new_custom' =>
                                false,

                            'custom_options' =>
                                $options,
                        ];
                    }
                )
                ->all();


        /*
        |--------------------------------------------------------------------------
        | Campos seleccionados
        |--------------------------------------------------------------------------
        |
        | Primero ordenamos por sección.
        |
        | Después por el orden interno del campo.
        |
        */

        $selected =
            array_values(
                array_filter(
                    $fields,
                    fn (array $field): bool =>
                        (bool) $field['visible']
                )
            );


        usort(
            $selected,
            function (
                array $a,
                array $b
            ) use (
                $sectionOrderById
            ): int {
                $sectionA =
                    $sectionOrderById[
                        (int) (
                            $a['id_seccion']
                            ?? 0
                        )
                    ]
                    ?? 999999;


                $sectionB =
                    $sectionOrderById[
                        (int) (
                            $b['id_seccion']
                            ?? 0
                        )
                    ]
                    ?? 999999;


                if (
                    $sectionA !==
                    $sectionB
                ) {
                    return $sectionA
                        <=>
                        $sectionB;
                }


                $fieldOrderA =
                    (int) (
                        $a['orden']
                        ?? 999999
                    );


                $fieldOrderB =
                    (int) (
                        $b['orden']
                        ?? 999999
                    );


                if (
                    $fieldOrderA !==
                    $fieldOrderB
                ) {
                    return $fieldOrderA
                        <=>
                        $fieldOrderB;
                }


                return (
                    (int) (
                        $a['id_campo']
                        ?? 0
                    )
                )
                    <=>
                    (
                        (int) (
                            $b['id_campo']
                            ?? 0
                        )
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Campos disponibles
        |--------------------------------------------------------------------------
        */

        $available =
            array_values(
                array_filter(
                    $fields,
                    fn (array $field): bool =>
                        ! (bool) $field['visible']
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Secciones con sus campos
        |--------------------------------------------------------------------------
        |
        | Esta estructura será la que utilizaremos después en el constructor.
        |
        | Por ahora también seguimos devolviendo "fields" para no romper el
        | Alpine actual.
        |
        */

        $sectionsWithFields =
            array_map(
                function (
                    array $section
                ) use (
                    $selected
                ): array {
                    $section['fields'] =
                        array_values(
                            array_filter(
                                $selected,
                                fn (array $field): bool =>
                                    (int) (
                                        $field['id_seccion']
                                        ?? 0
                                    )
                                    ===
                                    (int) $section[
                                        'id_seccion'
                                    ]
                            )
                        );


                    return $section;
                },
                $sections
            );


        /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

        return [
            'tipo' => [
                'id' =>
                    (int) $tipo->id_tipo_equipo,

                'nombre' =>
                    (string) $tipo->nombre,

                'descripcion' =>
                    (string) (
                        $tipo->descripcion
                        ?? ''
                    ),

                'activo' =>
                    (bool) $tipo->activo,
            ],

            /*
            |--------------------------------------------------------------------------
            | Nueva estructura
            |--------------------------------------------------------------------------
            */

            'sections' =>
                array_values(
                    $sectionsWithFields
                ),

            /*
            |--------------------------------------------------------------------------
            | Compatibilidad con el constructor actual
            |--------------------------------------------------------------------------
            */

            'fields' =>
                array_values(
                    array_merge(
                        $selected,
                        $available
                    )
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar constructor
    |--------------------------------------------------------------------------
    |
    | El navegador continúa enviando la fotografía de los campos.
    |
    | Ahora cada campo conserva también su id_seccion.
    |
    */

    public function save(
        int $tipoEquipoId,
        array $fields
    ): array {
        $tipo = DB::table(
            'tipos_equipo'
        )
            ->where(
                'id_tipo_equipo',
                $tipoEquipoId
            )
            ->first([
                'id_tipo_equipo',
                'nombre',
            ]);


        abort_unless(
            $tipo,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Secciones válidas
        |--------------------------------------------------------------------------
        */

        $sections =
            $this->ensureBaseSections(
                $tipoEquipoId,
                (string) $tipo->nombre
            );


        $sectionsById =
            collect($sections)
                ->keyBy(
                    'id_seccion'
                );


        $sectionIdsByKey =
            collect($sections)
                ->pluck(
                    'id_seccion',
                    'clave'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->all();


        /*
        |--------------------------------------------------------------------------
        | IDs de campos existentes
        |--------------------------------------------------------------------------
        */

        $existingFieldIds =
            collect(
                $fields
            )
                ->pluck(
                    'id_campo'
                )
                ->filter(
                    fn ($id) =>
                        is_numeric(
                            $id
                        )
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values()
                ->all();


        $masters =
            empty(
                $existingFieldIds
            )
                ? collect()
                : DB::table(
                    'campos_equipo'
                )
                    ->whereIn(
                        'id_campo',
                        $existingFieldIds
                    )
                    ->get([
                        'id_campo',
                        'clave',
                        'nombre',
                        'tipo_control',
                        'es_sistema',
                    ])
                    ->keyBy(
                        'id_campo'
                    );


        $relations =
            DB::table(
                'tipo_equipo_campos'
            )
                ->where(
                    'id_tipo_equipo',
                    $tipoEquipoId
                )
                ->get()
                ->keyBy(
                    'id_campo'
                );


        $metadataByField =
            $this->loadReferenceMetadata(
                $existingFieldIds
            );


        /*
        |--------------------------------------------------------------------------
        | Guardar
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $tipoEquipoId,
                $fields,
                $masters,
                $relations,
                $metadataByField,
                $sectionsById,
                $sectionIdsByKey
            ): void {
                /*
                |--------------------------------------------------------------------------
                | Orden independiente por sección
                |--------------------------------------------------------------------------
                |
                | Cada sección comienza:
                |
                | 10, 20, 30...
                |
                */

                $visibleOrderBySection =
                    [];


                /*
                |--------------------------------------------------------------------------
                | IMPORTANTE
                |--------------------------------------------------------------------------
                |
                | NO ordenamos $fields.
                |
                | El orden recibido desde Alpine sigue siendo la fuente de verdad.
                |
                */

                foreach (
                    array_values(
                        $fields
                    )
                    as $field
                ) {
                    $visible =
                        (bool) (
                            $field['visible']
                            ?? false
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Campo nuevo personalizado
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (bool) (
                            $field[
                                'is_new_custom'
                            ]
                            ?? false
                        )
                    ) {
                        if (
                            ! $visible
                        ) {
                            continue;
                        }


                        $type =
                            (string) (
                                $field[
                                    'tipo_control'
                                ]
                                ?? ''
                            );


                        if (
                            ! in_array(
                                $type,
                                self::CONTROL_TYPES,
                                true
                            )
                        ) {
                            continue;
                        }


                        $label =
                            $this->normalizeLabel(
                                $field[
                                    'etiqueta'
                                ]
                                ?? null,
                                'Campo'
                            );


                        $key =
                            $this->generateCustomKey(
                                $tipoEquipoId,
                                $label
                            );


                        $legacySection =
                            (string) (
                                $field['seccion']
                                ?? 'adicional'
                            );


                        $idSeccion =
                            $this->resolveFieldSectionId(
                                $field[
                                    'id_seccion'
                                ]
                                ?? null,
                                null,
                                $legacySection,
                                $sectionsById,
                                $sectionIdsByKey
                            );


                        $legacySection =
                            $this->resolveLegacySectionForSave(
                                $idSeccion,
                                $legacySection,
                                $sectionsById,
                                $sectionIdsByKey
                            );


                        $order =
                            $this->nextVisibleOrder(
                                $visibleOrderBySection,
                                $idSeccion
                            );


                        $idCampo =
                            DB::table(
                                'campos_equipo'
                            )
                                ->insertGetId(
                                    [
                                        'clave' =>
                                            $key,

                                        'nombre' =>
                                            $label,

                                        'tipo_control' =>
                                            $type,

                                        'descripcion' =>
                                            null,

                                        'placeholder' =>
                                            null,

                                        'es_sistema' =>
                                            false,

                                        'activo' =>
                                            true,
                                    ],
                                    'id_campo'
                                );


                        $idTipoCampo =
                            DB::table(
                                'tipo_equipo_campos'
                            )
                                ->insertGetId(
                                    [
                                        'id_tipo_equipo' =>
                                            $tipoEquipoId,

                                        'id_campo' =>
                                            $idCampo,

                                        'etiqueta' =>
                                            null,

                                        'origen_dato' =>
                                            'atributo',

                                        'tabla_destino' =>
                                            null,

                                        'columna_destino' =>
                                            null,

                                        'fuente_opciones' =>
                                            $type === 'select'
                                                ? 'personalizado'
                                                : null,

                                        'id_catalogo' =>
                                            null,

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Sección real
                                        |--------------------------------------------------------------------------
                                        */

                                        'id_seccion' =>
                                            $idSeccion,

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Legacy
                                        |--------------------------------------------------------------------------
                                        */

                                        'seccion' =>
                                            $legacySection,

                                        'visible' =>
                                            true,

                                        'obligatorio' =>
                                            (bool) (
                                                $field[
                                                    'obligatorio'
                                                ]
                                                ?? false
                                            ),

                                        'orden' =>
                                            $order,
                                    ],
                                    'id_tipo_campo'
                                );


                        if (
                            $type === 'select'
                        ) {
                            $this->persistCustomOptions(
                                $idTipoCampo,
                                $field[
                                    'custom_options'
                                ]
                                ?? []
                            );
                        }


                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Campo maestro existente
                    |--------------------------------------------------------------------------
                    */

                    $idCampo =
                        isset(
                            $field[
                                'id_campo'
                            ]
                        )
                        &&
                        is_numeric(
                            $field[
                                'id_campo'
                            ]
                        )
                            ? (int) $field[
                                'id_campo'
                            ]
                            : null;


                    if (
                        $idCampo === null
                    ) {
                        continue;
                    }


                    $master =
                        $masters->get(
                            $idCampo
                        );


                    if (
                        ! $master
                    ) {
                        continue;
                    }


                    $relation =
                        $relations->get(
                            $idCampo
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Protegido
                    |--------------------------------------------------------------------------
                    */

                    $protected =
                        in_array(
                            (string) $master->clave,
                            self::PROTECTED_FIELDS,
                            true
                        );


                    if (
                        $protected
                    ) {
                        $visible =
                            true;
                    }


                    $required =
                        $protected
                            ? true
                            : (bool) (
                                $field[
                                    'obligatorio'
                                ]
                                ?? false
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Etiqueta
                    |--------------------------------------------------------------------------
                    */

                    $label =
                        $this->normalizeLabel(
                            $field[
                                'etiqueta'
                            ]
                            ?? null,
                            (string) $master->nombre
                        );


                    $customLabel =
                        $label !==
                            (string) $master->nombre
                            ? $label
                            : null;


                    /*
                    |--------------------------------------------------------------------------
                    | Metadatos
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $relation
                    ) {
                        $metadata = [
                            'origen_dato' =>
                                $relation->origen_dato,

                            'tabla_destino' =>
                                $relation->tabla_destino,

                            'columna_destino' =>
                                $relation->columna_destino,

                            'fuente_opciones' =>
                                $relation->fuente_opciones,

                            'id_catalogo' =>
                                $relation->id_catalogo,

                            'seccion' =>
                                $relation->seccion
                                ?? 'adicional',
                        ];
                    } else {
                        $metadata =
                            $this->normalizeReferenceMetadata(
                                $metadataByField[
                                    $idCampo
                                ]
                                ?? null
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sección
                    |--------------------------------------------------------------------------
                    */

                    $legacySection =
                        (string) (
                            $field[
                                'seccion'
                            ]
                            ?? $metadata[
                                'seccion'
                            ]
                            ?? 'adicional'
                        );


                    $idSeccion =
                        $this->resolveFieldSectionId(
                            $field[
                                'id_seccion'
                            ]
                            ?? null,
                            $relation?->id_seccion,
                            $legacySection,
                            $sectionsById,
                            $sectionIdsByKey
                        );


                    $legacySection =
                        $this->resolveLegacySectionForSave(
                            $idSeccion,
                            $relation?->seccion
                                ?? $legacySection,
                            $sectionsById,
                            $sectionIdsByKey
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Orden
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $visible
                    ) {
                        $order =
                            $this->nextVisibleOrder(
                                $visibleOrderBySection,
                                $idSeccion
                            );
                    } else {
                        $order =
                            (int) (
                                $field[
                                    'orden'
                                ]
                                ?? $relation?->orden
                                ?? 999999
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Relación existente
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $relation
                    ) {
                        DB::table(
                            'tipo_equipo_campos'
                        )
                            ->where(
                                'id_tipo_campo',
                                $relation
                                    ->id_tipo_campo
                            )
                            ->update([
                                'etiqueta' =>
                                    $customLabel,

                                'id_seccion' =>
                                    $idSeccion,

                                /*
                                |--------------------------------------------------------------------------
                                | Se conserva legacy durante la transición
                                |--------------------------------------------------------------------------
                                */

                                'seccion' =>
                                    $legacySection,

                                'visible' =>
                                    $visible,

                                'obligatorio' =>
                                    $required,

                                'orden' =>
                                    $order,
                            ]);


                        if (
                            (string) $relation
                                ->fuente_opciones
                            === 'personalizado'
                            &&
                            (string) $master
                                ->tipo_control
                            === 'select'
                        ) {
                            $this->persistCustomOptions(
                                (int) $relation
                                    ->id_tipo_campo,
                                $field[
                                    'custom_options'
                                ]
                                ?? []
                            );
                        }


                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Campo disponible que continúa sin utilizarse
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $visible
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Crear nueva relación
                    |--------------------------------------------------------------------------
                    */

                    $idTipoCampo =
                        DB::table(
                            'tipo_equipo_campos'
                        )
                            ->insertGetId(
                                [
                                    'id_tipo_equipo' =>
                                        $tipoEquipoId,

                                    'id_campo' =>
                                        $idCampo,

                                    'etiqueta' =>
                                        $customLabel,

                                    'origen_dato' =>
                                        $metadata[
                                            'origen_dato'
                                        ],

                                    'tabla_destino' =>
                                        $metadata[
                                            'tabla_destino'
                                        ],

                                    'columna_destino' =>
                                        $metadata[
                                            'columna_destino'
                                        ],

                                    'fuente_opciones' =>
                                        $metadata[
                                            'fuente_opciones'
                                        ],

                                    'id_catalogo' =>
                                        $metadata[
                                            'id_catalogo'
                                        ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Sección real
                                    |--------------------------------------------------------------------------
                                    */

                                    'id_seccion' =>
                                        $idSeccion,

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Legacy
                                    |--------------------------------------------------------------------------
                                    */

                                    'seccion' =>
                                        $legacySection,

                                    'visible' =>
                                        true,

                                    'obligatorio' =>
                                        $required,

                                    'orden' =>
                                        $order,
                                ],
                                'id_tipo_campo'
                            );


                    if (
                        $metadata[
                            'fuente_opciones'
                        ] === 'personalizado'
                    ) {
                        $this->persistCustomOptions(
                            $idTipoCampo,
                            $field[
                                'custom_options'
                            ]
                            ?? []
                        );
                    }
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Invalidación del módulo
        |--------------------------------------------------------------------------
        */

        app(
            SystemCacheService::class
        )->refreshModule(
            'catalogos'
        );


        return $this->load(
            $tipoEquipoId
        );
    }

    public function ensureRequiredFields(int $tipoEquipoId): array
    {
        return DB::transaction(function () use ($tipoEquipoId): array {
            $tipo = DB::table('tipos_equipo')
                ->where('id_tipo_equipo', $tipoEquipoId)
                ->lockForUpdate()
                ->first();

            abort_unless($tipo, 404);

            $sections = $this->ensureBaseSections(
                $tipoEquipoId,
                (string) $tipo->nombre
            );

            $sectionIds = collect($sections)
                ->pluck('id_seccion', 'clave')
                ->all();

            /*
            * Clave => nombre, control, fuente de opciones, sección inicial.
            */
            $definitions = [
                'nombre_equipo' => [
                    'Nombre del equipo',
                    'text',
                    null,
                    'general',
                ],

                'id_estado_activo' => [
                    'Estado',
                    'select',
                    'estados_equipo',
                    'general',
                ],

                'id_marca' => [
                    'Marca',
                    'select',
                    'marcas',
                    'general',
                ],

                'numero_serie' => [
                    'Número de serie',
                    'text',
                    null,
                    'general',
                ],

                'numero_factura' => [
                    'Número de factura',
                    'text',
                    null,
                    'general',
                ],

                'id_unidad' => [
                    app(PlantillaInstalacionService::class)->etiquetas()['unidad_singular'],
                    'select',
                    'unidades_organizativas',
                    'adicional',
                ],
            ];

            /*
            * Crear únicamente los campos maestros que todavía no existen.
            */
            $masters = [];

            foreach (
                $definitions as $key => [$name, $control, $source, $section]
            ) {
                $masters[] = [
                    'clave' => $key,
                    'nombre' => $name,
                    'tipo_control' => $control,
                    'es_sistema' => true,
                    'activo' => true,
                ];
            }

            DB::table('campos_equipo')->insertOrIgnore($masters);

            DB::table('campos_equipo')
                ->whereIn('clave', array_keys($definitions))
                ->update([
                    'activo' => true,
                    'es_sistema' => true,
                ]);

            $fieldIds = DB::table('campos_equipo')
                ->whereIn('clave', array_keys($definitions))
                ->pluck('id_campo', 'clave');

            $relations = DB::table('tipo_equipo_campos')
                ->where('id_tipo_equipo', $tipoEquipoId)
                ->get()
                ->keyBy('id_campo');

            $order = (int) ($relations->max('orden') ?? 0);

            $rows = [];

            /*
            * Agregar los obligatorios que falten.
            * Los existentes conservan su etiqueta, sección y orden.
            */
            foreach (
                $definitions as $key => [$name, $control, $source, $section]
            ) {
                $id = (int) $fieldIds[$key];

                if ($relations->has($id)) {
                    continue;
                }

                $order += 10;

                $rows[] = [
                    'id_tipo_equipo' => $tipoEquipoId,
                    'id_campo' => $id,
                    'etiqueta' => $name,

                    'origen_dato' => 'equipos',
                    'tabla_destino' => 'equipos',
                    'columna_destino' => $key,

                    'fuente_opciones' => $source,
                    'id_catalogo' => null,

                    'id_seccion' => $sectionIds[$section],
                    'seccion' => $section,

                    'visible' => true,
                    'obligatorio' => true,
                    'orden' => $order,
                ];
            }

            if ($rows !== []) {
                DB::table('tipo_equipo_campos')->insert($rows);
            }

            /*
            * Garantizar que los obligatorios permanezcan visibles.
            */
            DB::table('tipo_equipo_campos')
                ->where('id_tipo_equipo', $tipoEquipoId)
                ->whereIn('id_campo', $fieldIds->values()->all())
                ->update([
                    'visible' => true,
                    'obligatorio' => true,
                ]);

            return $sections;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Garantizar secciones base
    |--------------------------------------------------------------------------
    |
    | Cada tipo de equipo debe empezar como mínimo con:
    |
    | 1. Información general
    | 2. Información de {tipo}
    | 3. Información adicional
    |
    | No se limita la cantidad de secciones adicionales.
    |
    */

    private function ensureBaseSections(
        int $tipoEquipoId,
        string $tipoNombre
    ): array {
        $tipoNombre =
            trim(
                $tipoNombre
            );


        $definitions = [
            [
                'clave' =>
                    'general',

                'nombre' =>
                    'Información general',

                'orden' =>
                    10,
            ],

            [
                'clave' =>
                    'informacion_tipo',

                'nombre' =>
                    'Información de '
                    . $tipoNombre,

                'orden' =>
                    20,
            ],

            [
                'clave' =>
                    'adicional',

                'nombre' =>
                    'Información adicional',

                'orden' =>
                    30,
            ],
        ];


        foreach (
            $definitions
            as $definition
        ) {
            DB::table(
                'tipo_equipo_secciones'
            )
                ->insertOrIgnore([
                    'id_tipo_equipo' =>
                        $tipoEquipoId,

                    'clave' =>
                        $definition[
                            'clave'
                        ],

                    'nombre' =>
                        $definition[
                            'nombre'
                        ],

                    'orden' =>
                        $definition[
                            'orden'
                        ],

                    'es_base' =>
                        true,
                ]);
        }


        return DB::table(
            'tipo_equipo_secciones'
        )
            ->where(
                'id_tipo_equipo',
                $tipoEquipoId
            )
            ->orderBy(
                'orden'
            )
            ->orderBy(
                'id_seccion'
            )
            ->get([
                'id_seccion',
                'clave',
                'nombre',
                'orden',
                'es_base',
            ])
            ->map(
                fn ($section): array => [
                    'id_seccion' =>
                        (int) $section
                            ->id_seccion,

                    'clave' =>
                        (string) $section
                            ->clave,

                    'nombre' =>
                        (string) $section
                            ->nombre,

                    'orden' =>
                        (int) $section
                            ->orden,

                    'es_base' =>
                        (bool) $section
                            ->es_base,
                ]
            )
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Resolver sección desde clave legacy
    |--------------------------------------------------------------------------
    |
    | Equivalencias actuales:
    |
    | general + compra
    |     -> Información general
    |
    | especifico
    |     -> Información de {tipo}
    |
    | ubicacion + adicional
    |     -> Información adicional
    |
    */

    private function resolveSectionIdFromLegacy(
        string $legacySection,
        array $sectionIdsByKey
    ): int {
        $legacySection =
            trim(
                mb_strtolower(
                    $legacySection
                )
            );


        $baseKey =
            match (
                $legacySection
            ) {
                'general',
                'compra' =>
                    'general',

                'especifico' =>
                    'informacion_tipo',

                'ubicacion',
                'adicional' =>
                    'adicional',

                default =>
                    'adicional',
            };


        $id =
            $sectionIdsByKey[
                $baseKey
            ]
            ?? null;


        if (
            is_numeric(
                $id
            )
        ) {
            return (int) $id;
        }


        /*
        |--------------------------------------------------------------------------
        | Protección
        |--------------------------------------------------------------------------
        |
        | ensureBaseSections() garantiza que "adicional" exista.
        |
        */

        return (int) (
            $sectionIdsByKey[
                'adicional'
            ]
            ?? 0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolver sección real de un campo
    |--------------------------------------------------------------------------
    */

    private function resolveFieldSectionId(
        mixed $requestedSectionId,
        mixed $currentSectionId,
        string $legacySection,
        $sectionsById,
        array $sectionIdsByKey
    ): int {
        /*
        |--------------------------------------------------------------------------
        | Primera prioridad: lo enviado por el constructor
        |--------------------------------------------------------------------------
        */

        if (
            is_numeric(
                $requestedSectionId
            )
        ) {
            $requestedSectionId =
                (int) $requestedSectionId;


            if (
                $sectionsById->has(
                    $requestedSectionId
                )
            ) {
                return $requestedSectionId;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Segunda prioridad: relación actual
        |--------------------------------------------------------------------------
        */

        if (
            is_numeric(
                $currentSectionId
            )
        ) {
            $currentSectionId =
                (int) $currentSectionId;


            if (
                $sectionsById->has(
                    $currentSectionId
                )
            ) {
                return $currentSectionId;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Tercera prioridad: equivalencia legacy
        |--------------------------------------------------------------------------
        */

        return $this->resolveSectionIdFromLegacy(
            $legacySection,
            $sectionIdsByKey
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mantener columna legacy sincronizada
    |--------------------------------------------------------------------------
    |
    | Si el campo sigue dentro de la misma sección equivalente, conservamos
    | valores anteriores como "compra" o "ubicacion".
    |
    | Esto evita romper cualquier pantalla antigua durante la transición.
    |
    */

    private function resolveLegacySectionForSave(
        int $idSeccion,
        mixed $existingLegacySection,
        $sectionsById,
        array $sectionIdsByKey
    ): string {
        $existingLegacySection =
            trim(
                (string) (
                    $existingLegacySection
                    ?? ''
                )
            );


        if (
            $existingLegacySection !== ''
        ) {
            $legacyId =
                $this->resolveSectionIdFromLegacy(
                    $existingLegacySection,
                    $sectionIdsByKey
                );


            if (
                $legacyId ===
                $idSeccion
            ) {
                return $existingLegacySection;
            }
        }


        $section =
            $sectionsById->get(
                $idSeccion
            );


        if (
            ! $section
        ) {
            return 'adicional';
        }


        return match (
            (string) $section[
                'clave'
            ]
        ) {
            'general' =>
                'general',

            'informacion_tipo' =>
                'especifico',

            'adicional' =>
                'adicional',

            /*
            |--------------------------------------------------------------------------
            | Secciones creadas por el usuario
            |--------------------------------------------------------------------------
            |
            | La columna legacy no entiende secciones personalizadas.
            | Mientras exista esta columna, utilizamos "adicional".
            |
            */

            default =>
                'adicional',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Siguiente orden de campo dentro de una sección
    |--------------------------------------------------------------------------
    */

    private function nextVisibleOrder(
        array &$visibleOrderBySection,
        int $idSeccion
    ): int {
        if (
            ! isset(
                $visibleOrderBySection[
                    $idSeccion
                ]
            )
        ) {
            $visibleOrderBySection[
                $idSeccion
            ] = 10;
        }


        $order =
            $visibleOrderBySection[
                $idSeccion
            ];


        $visibleOrderBySection[
            $idSeccion
        ] += 10;


        return $order;
    }


    /*
    |--------------------------------------------------------------------------
    | Metadatos de referencia
    |--------------------------------------------------------------------------
    */

    private function loadReferenceMetadata(
        array $fieldIds
    ): array {
        if (
            empty(
                $fieldIds
            )
        ) {
            return [];
        }


        $rows =
            DB::table(
                'tipo_equipo_campos'
            )
                ->whereIn(
                    'id_campo',
                    $fieldIds
                )
                ->get([
                    'id_campo',
                    'origen_dato',
                    'tabla_destino',
                    'columna_destino',
                    'fuente_opciones',
                    'id_catalogo',
                    'seccion',
                ]);


        return $rows
            ->groupBy(
                'id_campo'
            )
            ->map(
                function ($group) {
                    return $group
                        ->sortBy(
                            function ($row): int {
                                return match (
                                    (string) $row
                                        ->origen_dato
                                ) {
                                    'equipos' =>
                                        1,

                                    'asignacion' =>
                                        2,

                                    'tabla_tipo' =>
                                        3,

                                    'atributo' =>
                                        4,

                                    default =>
                                        5,
                                };
                            }
                        )
                        ->first();
                }
            )
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Normalizar metadatos
    |--------------------------------------------------------------------------
    */

    private function normalizeReferenceMetadata(
        mixed $metadata
    ): array {
        if (
            ! $metadata
        ) {
            return [
                'origen_dato' =>
                    'atributo',

                'tabla_destino' =>
                    null,

                'columna_destino' =>
                    null,

                'fuente_opciones' =>
                    null,

                'id_catalogo' =>
                    null,

                'seccion' =>
                    'adicional',
            ];
        }


        $origin =
            (string) (
                $metadata->origen_dato
                ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | Campo físico común
        |--------------------------------------------------------------------------
        */

        if (
            $origin ===
            'equipos'
        ) {
            return [
                'origen_dato' =>
                    'equipos',

                'tabla_destino' =>
                    $metadata
                        ->tabla_destino,

                'columna_destino' =>
                    $metadata
                        ->columna_destino,

                'fuente_opciones' =>
                    $metadata
                        ->fuente_opciones,

                'id_catalogo' =>
                    $metadata->id_catalogo !== null
                        ? (int) $metadata
                            ->id_catalogo
                        : null,

                'seccion' =>
                    (string) (
                        $metadata->seccion
                        ?? 'general'
                    ),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Asignación
        |--------------------------------------------------------------------------
        */

        if (
            $origin ===
            'asignacion'
        ) {
            return [
                'origen_dato' =>
                    'asignacion',

                'tabla_destino' =>
                    $metadata
                        ->tabla_destino,

                'columna_destino' =>
                    $metadata
                        ->columna_destino,

                'fuente_opciones' =>
                    $metadata
                        ->fuente_opciones,

                'id_catalogo' =>
                    $metadata->id_catalogo !== null
                        ? (int) $metadata
                            ->id_catalogo
                        : null,

                'seccion' =>
                    (string) (
                        $metadata->seccion
                        ?? 'adicional'
                    ),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Campo específico reutilizado
        |--------------------------------------------------------------------------
        |
        | Si un campo originalmente pertenece a la tabla específica de otro
        | tipo de equipo y se reutiliza aquí, continúa tratándose como atributo
        | dinámico.
        |
        */

        return [
            'origen_dato' =>
                'atributo',

            'tabla_destino' =>
                null,

            'columna_destino' =>
                null,

            'fuente_opciones' =>
                $metadata
                    ->fuente_opciones,

            'id_catalogo' =>
                $metadata->id_catalogo !== null
                    ? (int) $metadata
                        ->id_catalogo
                    : null,

            'seccion' =>
                'adicional',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Opciones personalizadas
    |--------------------------------------------------------------------------
    */

    private function persistCustomOptions(
        int $idTipoCampo,
        array $options
    ): void {
        DB::table(
            'tipo_equipo_campo_opciones'
        )
            ->where(
                'id_tipo_campo',
                $idTipoCampo
            )
            ->delete();


        $rows =
            [];


        $used =
            [];


        foreach (
            $options
            as $option
        ) {
            $name =
                trim(
                    (string) (
                        $option[
                            'nombre'
                        ]
                        ?? ''
                    )
                );


            if (
                $name === ''
            ) {
                continue;
            }


            $normalized =
                mb_strtolower(
                    $name
                );


            if (
                isset(
                    $used[
                        $normalized
                    ]
                )
            ) {
                continue;
            }


            $used[
                $normalized
            ] =
                true;


            $rows[] = [
                'id_tipo_campo' =>
                    $idTipoCampo,

                'nombre' =>
                    $name,

                'orden' =>
                    (
                        count(
                            $rows
                        )
                        + 1
                    ) * 10,

                'activo' =>
                    true,
            ];
        }


        if (
            ! empty(
                $rows
            )
        ) {
            DB::table(
                'tipo_equipo_campo_opciones'
            )
                ->insert(
                    $rows
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Etiqueta
    |--------------------------------------------------------------------------
    */

    private function normalizeLabel(
        mixed $value,
        string $fallback
    ): string {
        $value =
            trim(
                (string) $value
            );


        return $value !== ''
            ? mb_substr(
                $value,
                0,
                150
            )
            : $fallback;
    }


    /*
    |--------------------------------------------------------------------------
    | Clave personalizada
    |--------------------------------------------------------------------------
    */

    private function generateCustomKey(
        int $tipoEquipoId,
        string $label
    ): string {
        $slug =
            Str::slug(
                $label,
                '_'
            );


        if (
            $slug === ''
        ) {
            $slug =
                'campo';
        }


        $base =
            'custom_'
            . $tipoEquipoId
            . '_'
            . $slug;


        $candidate =
            $base;


        $suffix =
            2;


        while (
            DB::table(
                'campos_equipo'
            )
                ->where(
                    'clave',
                    $candidate
                )
                ->exists()
        ) {
            $candidate =
                $base
                . '_'
                . $suffix;


            $suffix++;
        }


        return $candidate;
    }
}