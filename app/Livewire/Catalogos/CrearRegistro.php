<?php

namespace App\Livewire\Catalogos;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CrearRegistro extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Tipo de registro
    |--------------------------------------------------------------------------
    */

    public string $tipoRegistro = 'marca';


    /*
    |--------------------------------------------------------------------------
    | Campos comunes
    |--------------------------------------------------------------------------
    */

    public string $nombre = '';

    public string $descripcion = '';

    public string $sitioWeb = '';

    public ?int $idProveedorSugerido = null;

    public ?int $garantiaEstandarMeses = null;

    public string $comentarios = '';


    /*
    |--------------------------------------------------------------------------
    | Campos exclusivos de marca
    |--------------------------------------------------------------------------
    */

    public ?int $idPaisOrigen = null;


    /*
    |--------------------------------------------------------------------------
    | Campos exclusivos de modelo
    |--------------------------------------------------------------------------
    */

    public ?int $idMarca = null;

    public ?int $idTipoEquipo = null;

    public ?int $idAreaUsoComun = null;


    /*
    |--------------------------------------------------------------------------
    | Cambio de tipo
    |--------------------------------------------------------------------------
    */

    public function updatedTipoRegistro(
        string $value
    ): void {
        if (
            ! in_array(
                $value,
                ['marca', 'modelo'],
                true
            )
        ) {
            $this->tipoRegistro = 'marca';
        }


        $this->resetValidation();


        if ($this->tipoRegistro === 'marca') {
            $this->idMarca = null;

            $this->idTipoEquipo = null;

            $this->idAreaUsoComun = null;
        } else {
            $this->idPaisOrigen = null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        if ($this->tipoRegistro === 'modelo') {
            $this->createModelo();

            return;
        }


        $this->createMarca();
    }


    /*
    |--------------------------------------------------------------------------
    | Crear marca
    |--------------------------------------------------------------------------
    */

    private function createMarca(): void
    {
        $validated = $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'sitioWeb' => [
                'nullable',
                'url',
                'max:500',
            ],

            'idPaisOrigen' => [
                'nullable',
                'integer',
            ],

            'idProveedorSugerido' => [
                'nullable',
                'integer',
            ],

            'garantiaEstandarMeses' => [
                'nullable',
                'integer',
                'min:1',
                'max:600',
            ],

            'comentarios' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'nombre.required' =>
                'Ingresa el nombre de la marca.',

            'nombre.max' =>
                'El nombre no puede superar los 255 caracteres.',

            'descripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',

            'sitioWeb.url' =>
                'Ingresa una dirección web válida.',

            'sitioWeb.max' =>
                'El sitio web no puede superar los 500 caracteres.',

            'garantiaEstandarMeses.integer' =>
                'La garantía debe indicarse en meses.',

            'garantiaEstandarMeses.min' =>
                'La garantía debe ser mayor a 0 meses.',

            'garantiaEstandarMeses.max' =>
                'La garantía no puede superar los 600 meses.',

            'comentarios.max' =>
                'Los comentarios no pueden superar los 5000 caracteres.',
        ]);


        $nombre =
            trim(
                $validated['nombre']
            );


        $duplicate = DB::table('marcas')
            ->whereRaw(
                'LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))',
                [$nombre]
            )
            ->exists();


        if ($duplicate) {
            $this->addError(
                'nombre',
                'Ya existe una marca con este nombre.'
            );

            return;
        }


        if (
            $validated['idPaisOrigen'] !== null
            &&
            ! $this->paisOrigenActivoExists(
                (int) $validated['idPaisOrigen']
            )
        ) {
            $this->addError(
                'idPaisOrigen',
                'El país de origen seleccionado no es válido.'
            );

            return;
        }


        if (
            $validated['idProveedorSugerido'] !== null
            &&
            ! DB::table('proveedores')
                ->where(
                    'id_proveedor',
                    $validated['idProveedorSugerido']
                )
                ->where(
                    'activo',
                    true
                )
                ->exists()
        ) {
            $this->addError(
                'idProveedorSugerido',
                'El proveedor seleccionado no es válido.'
            );

            return;
        }


        $created = null;


        DB::transaction(
            function () use (
                $validated,
                $nombre,
                &$created
            ): void {
                $idMarca = DB::table('marcas')
                    ->insertGetId(
                        [
                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                $this->nullableTrim(
                                    $validated['descripcion']
                                ),

                            'activo' =>
                                true,

                            'codigo_interno' =>
                                null,

                            'sitio_web' =>
                                $this->nullableTrim(
                                    $validated['sitioWeb']
                                ),

                            'id_pais_origen' =>
                                $validated['idPaisOrigen'],

                            'id_proveedor_sugerido' =>
                                $validated['idProveedorSugerido'],

                            'garantia_estandar_meses' =>
                                $validated['garantiaEstandarMeses'],

                            'comentarios' =>
                                $this->nullableTrim(
                                    $validated['comentarios']
                                ),

                            'fecha_creacion' =>
                                now(),

                            'fecha_actualizacion' =>
                                now(),
                        ],
                        'id_marca'
                    );


                $codigoInterno =
                    'MAR-'
                    . str_pad(
                        (string) $idMarca,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );


                DB::table('marcas')
                    ->where(
                        'id_marca',
                        $idMarca
                    )
                    ->update([
                        'codigo_interno' =>
                            $codigoInterno,

                        'fecha_actualizacion' =>
                            now(),
                    ]);


                $this->writeAudit(
                    'MARCA_CREADA',
                    "Se creó la marca {$nombre} ({$codigoInterno})."
                );


                $created = [
                    'id' =>
                        (int) $idMarca,

                    'codigo' =>
                        $codigoInterno,

                    'nombre' =>
                        $nombre,
                ];
            }
        );


        $this->resetCreateForm();


        $this->dispatch(
            'catalog-record-created',
            message:
                "La marca {$created['nombre']} se creó correctamente con el código {$created['codigo']}."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear modelo
    |--------------------------------------------------------------------------
    */

    private function createModelo(): void
    {
        $validated = $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'sitioWeb' => [
                'nullable',
                'url',
                'max:500',
            ],

            'idMarca' => [
                'required',
                'integer',
            ],

            'idTipoEquipo' => [
                'required',
                'integer',
            ],

            'idProveedorSugerido' => [
                'nullable',
                'integer',
            ],

            'garantiaEstandarMeses' => [
                'nullable',
                'integer',
                'min:1',
                'max:600',
            ],

            'idAreaUsoComun' => [
                'nullable',
                'integer',
            ],

            'comentarios' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'nombre.required' =>
                'Ingresa el nombre del modelo.',

            'nombre.max' =>
                'El nombre no puede superar los 255 caracteres.',

            'descripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',

            'sitioWeb.url' =>
                'Ingresa una dirección web válida.',

            'sitioWeb.max' =>
                'El sitio web no puede superar los 500 caracteres.',

            'idMarca.required' =>
                'Selecciona una marca.',

            'idTipoEquipo.required' =>
                'Selecciona un tipo de dispositivo.',

            'garantiaEstandarMeses.integer' =>
                'La garantía debe indicarse en meses.',

            'garantiaEstandarMeses.min' =>
                'La garantía debe ser mayor a 0 meses.',

            'garantiaEstandarMeses.max' =>
                'La garantía no puede superar los 600 meses.',

            'comentarios.max' =>
                'Los comentarios no pueden superar los 5000 caracteres.',
        ]);


        $marcaExists = DB::table('marcas')
            ->where(
                'id_marca',
                $validated['idMarca']
            )
            ->where(
                'activo',
                true
            )
            ->exists();


        if (! $marcaExists) {
            $this->addError(
                'idMarca',
                'La marca seleccionada no es válida.'
            );

            return;
        }


        $tipoExists = DB::table('tipos_equipo')
            ->where(
                'id_tipo_equipo',
                $validated['idTipoEquipo']
            )
            ->where(
                'activo',
                true
            )
            ->exists();


        if (! $tipoExists) {
            $this->addError(
                'idTipoEquipo',
                'El tipo de dispositivo seleccionado no es válido.'
            );

            return;
        }


        if (
            $validated['idProveedorSugerido'] !== null
            &&
            ! DB::table('proveedores')
                ->where(
                    'id_proveedor',
                    $validated['idProveedorSugerido']
                )
                ->where(
                    'activo',
                    true
                )
                ->exists()
        ) {
            $this->addError(
                'idProveedorSugerido',
                'El proveedor seleccionado no es válido.'
            );

            return;
        }


        if (
            $validated['idAreaUsoComun'] !== null
            &&
            ! DB::table('areas')
                ->where(
                    'id_area',
                    $validated['idAreaUsoComun']
                )
                ->where(
                    'activo',
                    true
                )
                ->exists()
        ) {
            $this->addError(
                'idAreaUsoComun',
                'El área seleccionada no es válida.'
            );

            return;
        }


        $nombre =
            trim(
                $validated['nombre']
            );


        $duplicate = DB::table('modelos')
            ->where(
                'id_marca',
                $validated['idMarca']
            )
            ->where(
                'id_tipo_equipo',
                $validated['idTipoEquipo']
            )
            ->whereRaw(
                'LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))',
                [$nombre]
            )
            ->exists();


        if ($duplicate) {
            $this->addError(
                'nombre',
                'Ya existe este modelo para la marca y tipo de dispositivo seleccionados.'
            );

            return;
        }


        $created = null;


        DB::transaction(
            function () use (
                $validated,
                $nombre,
                &$created
            ): void {
                $idModelo = DB::table('modelos')
                    ->insertGetId(
                        [
                            'id_marca' =>
                                $validated['idMarca'],

                            'id_tipo_equipo' =>
                                $validated['idTipoEquipo'],

                            'nombre' =>
                                $nombre,

                            'descripcion' =>
                                $this->nullableTrim(
                                    $validated['descripcion']
                                ),

                            'activo' =>
                                true,

                            'codigo_interno' =>
                                null,

                            'sitio_web' =>
                                $this->nullableTrim(
                                    $validated['sitioWeb']
                                ),

                            'id_proveedor_sugerido' =>
                                $validated['idProveedorSugerido'],

                            'garantia_estandar_meses' =>
                                $validated['garantiaEstandarMeses'],

                            'id_area_uso_comun' =>
                                $validated['idAreaUsoComun'],

                            'comentarios' =>
                                $this->nullableTrim(
                                    $validated['comentarios']
                                ),

                            'fecha_creacion' =>
                                now(),

                            'fecha_actualizacion' =>
                                now(),
                        ],
                        'id_modelo'
                    );


                $codigoInterno =
                    'MOD-'
                    . str_pad(
                        (string) $idModelo,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );


                DB::table('modelos')
                    ->where(
                        'id_modelo',
                        $idModelo
                    )
                    ->update([
                        'codigo_interno' =>
                            $codigoInterno,

                        'fecha_actualizacion' =>
                            now(),
                    ]);


                $this->writeAudit(
                    'MODELO_CREADO',
                    "Se creó el modelo {$nombre} ({$codigoInterno})."
                );


                $created = [
                    'id' =>
                        (int) $idModelo,

                    'codigo' =>
                        $codigoInterno,

                    'nombre' =>
                        $nombre,
                ];
            }
        );


        $this->resetCreateForm();


        $this->dispatch(
            'catalog-record-created',
            message:
                "El modelo {$created['nombre']} se creó correctamente con el código {$created['codigo']}."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reiniciar formulario
    |--------------------------------------------------------------------------
    */

    private function resetCreateForm(): void
    {
        $currentType =
            $this->tipoRegistro;


        $this->reset([
            'nombre',
            'descripcion',
            'sitioWeb',
            'idProveedorSugerido',
            'garantiaEstandarMeses',
            'comentarios',
            'idPaisOrigen',
            'idMarca',
            'idTipoEquipo',
            'idAreaUsoComun',
        ]);


        $this->tipoRegistro =
            $currentType;


        $this->resetValidation();
    }


    /*
    |--------------------------------------------------------------------------
    | País de origen válido
    |--------------------------------------------------------------------------
    */

    private function paisOrigenActivoExists(
        int $paisId
    ): bool {
        return DB::table('catalogo_valores as cv')
            ->join(
                'catalogos as c',
                'c.id_catalogo',
                '=',
                'cv.id_catalogo'
            )
            ->where(
                'c.clave',
                'PAIS_ORIGEN'
            )
            ->where(
                'cv.id_valor',
                $paisId
            )
            ->where(
                'cv.activo',
                true
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Texto nullable
    |--------------------------------------------------------------------------
    */

    private function nullableTrim(
        mixed $value
    ): ?string {
        if (
            $value === null
            ||
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
    | Bitácora
    |--------------------------------------------------------------------------
    */

    private function writeAudit(
        string $action,
        string $description
    ): void {
        DB::table('bitacora_auditoria')
            ->insert([
                'id_usuario' =>
                    auth()->id(),

                'accion' =>
                    $action,

                'modulo' =>
                    'CATALOGOS',

                'descripcion' =>
                    $description,

                'fecha_hora' =>
                    now(),
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Placeholder
    |--------------------------------------------------------------------------
    */

    public function placeholder(): View
    {
        return view(
            'livewire.placeholders.catalogos-crear-registro'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        $marcas =
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
                    'id_pais_origen',
                ]);


        $tiposEquipo =
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
                ]);


        $paisesOrigen =
            DB::table('catalogo_valores as cv')
                ->join(
                    'catalogos as c',
                    'c.id_catalogo',
                    '=',
                    'cv.id_catalogo'
                )
                ->where(
                    'c.clave',
                    'PAIS_ORIGEN'
                )
                ->where(
                    'cv.activo',
                    true
                )
                ->orderBy(
                    'cv.orden'
                )
                ->orderBy(
                    'cv.nombre'
                )
                ->get([
                    'cv.id_valor',
                    'cv.nombre',
                ]);


        $proveedores =
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
                    'contacto',
                    'telefono',
                    'correo',
                ]);


        $areas =
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
                ]);


        /*
        |--------------------------------------------------------------------------
        | Contacto de proveedor seleccionado
        |--------------------------------------------------------------------------
        */

        $proveedorSeleccionado =
            $this->idProveedorSugerido !== null
                ? $proveedores->firstWhere(
                    'id_proveedor',
                    $this->idProveedorSugerido
                )
                : null;


        $contactoProveedor = null;


        if ($proveedorSeleccionado) {
            $partes = collect([
                $proveedorSeleccionado->contacto,
                $proveedorSeleccionado->telefono,
                $proveedorSeleccionado->correo,
            ])
                ->filter(
                    fn ($value) =>
                        filled($value)
                )
                ->values();


            $contactoProveedor =
                $partes->isNotEmpty()
                    ? $partes->implode(' · ')
                    : 'Sin información de contacto';
        }


        /*
        |--------------------------------------------------------------------------
        | País heredado de la marca para un modelo
        |--------------------------------------------------------------------------
        */

        $paisOrigenModelo = null;


        if (
            $this->tipoRegistro === 'modelo'
            &&
            $this->idMarca !== null
        ) {
            $paisOrigenModelo =
                DB::table('marcas as m')
                    ->leftJoin(
                        'catalogo_valores as cv',
                        'cv.id_valor',
                        '=',
                        'm.id_pais_origen'
                    )
                    ->where(
                        'm.id_marca',
                        $this->idMarca
                    )
                    ->value(
                        'cv.nombre'
                    );
        }


        return view(
            'livewire.catalogos.crear-registro',
            [
                'marcas' =>
                    $marcas,

                'tiposEquipo' =>
                    $tiposEquipo,

                'paisesOrigen' =>
                    $paisesOrigen,

                'proveedores' =>
                    $proveedores,

                'areas' =>
                    $areas,

                'contactoProveedor' =>
                    $contactoProveedor,

                'paisOrigenModelo' =>
                    $paisOrigenModelo,
            ]
        );
    }
}