<?php

namespace App\Livewire\Catalogos;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EditarRegistro extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Identidad del registro
    |--------------------------------------------------------------------------
    */

    public string $tipo = '';

    public int $registroId;

    public string $nombreOriginal = '';


    /*
    |--------------------------------------------------------------------------
    | Campos comunes
    |--------------------------------------------------------------------------
    */

    public string $nombre = '';

    public string $codigoInterno = '';

    public string $descripcion = '';

    public string $sitioWeb = '';

    public ?int $idProveedorSugerido = null;

    public ?int $garantiaEstandarMeses = null;

    public string $comentarios = '';

    public bool $activo = true;


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
    | Montaje
    |--------------------------------------------------------------------------
    */

    public function mount(
        string $tipo,
        int $registroId
    ): void {
        if (
            ! in_array(
                $tipo,
                ['marca', 'modelo'],
                true
            )
        ) {
            abort(404);
        }


        $this->tipo =
            $tipo;

        $this->registroId =
            $registroId;


        $this->loadRegistro();
    }


    /*
    |--------------------------------------------------------------------------
    | Cargar registro
    |--------------------------------------------------------------------------
    */

    private function loadRegistro(): void
    {
        if ($this->tipo === 'marca') {
            $registro = DB::table('marcas')
                ->where(
                    'id_marca',
                    $this->registroId
                )
                ->first();


            if (! $registro) {
                abort(404);
            }


            $this->nombre =
                (string) $registro->nombre;

            $this->nombreOriginal =
                (string) $registro->nombre;

            $this->codigoInterno =
                (string) ($registro->codigo_interno ?? '');

            $this->descripcion =
                (string) ($registro->descripcion ?? '');

            $this->sitioWeb =
                (string) ($registro->sitio_web ?? '');

            $this->idPaisOrigen =
                $registro->id_pais_origen !== null
                    ? (int) $registro->id_pais_origen
                    : null;

            $this->idProveedorSugerido =
                $registro->id_proveedor_sugerido !== null
                    ? (int) $registro->id_proveedor_sugerido
                    : null;

            $this->garantiaEstandarMeses =
                $registro->garantia_estandar_meses !== null
                    ? (int) $registro->garantia_estandar_meses
                    : null;

            $this->comentarios =
                (string) ($registro->comentarios ?? '');

            $this->activo =
                (bool) $registro->activo;


            return;
        }


        $registro = DB::table('modelos')
            ->where(
                'id_modelo',
                $this->registroId
            )
            ->first();


        if (! $registro) {
            abort(404);
        }


        $this->nombre =
            (string) $registro->nombre;

        $this->nombreOriginal =
            (string) $registro->nombre;

        $this->codigoInterno =
            (string) ($registro->codigo_interno ?? '');

        $this->descripcion =
            (string) ($registro->descripcion ?? '');

        $this->sitioWeb =
            (string) ($registro->sitio_web ?? '');

        $this->idProveedorSugerido =
            $registro->id_proveedor_sugerido !== null
                ? (int) $registro->id_proveedor_sugerido
                : null;

        $this->garantiaEstandarMeses =
            $registro->garantia_estandar_meses !== null
                ? (int) $registro->garantia_estandar_meses
                : null;

        $this->idAreaUsoComun =
            $registro->id_area_uso_comun !== null
                ? (int) $registro->id_area_uso_comun
                : null;

        $this->comentarios =
            (string) ($registro->comentarios ?? '');

        $this->activo =
            (bool) $registro->activo;

        $this->idMarca =
            (int) $registro->id_marca;

        $this->idTipoEquipo =
            (int) $registro->id_tipo_equipo;
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar cambios
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        if ($this->tipo === 'modelo') {
            $this->saveModelo();

            return;
        }


        $this->saveMarca();
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar marca
    |--------------------------------------------------------------------------
    */

    private function saveMarca(): void
    {
        $validated = $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'codigoInterno' => [
                'required',
                'string',
                'max:50',
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

            'codigoInterno.required' =>
                'Ingresa el código interno.',

            'codigoInterno.max' =>
                'El código interno no puede superar los 50 caracteres.',

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


        $codigoInterno =
            trim(
                $validated['codigoInterno']
            );


        $duplicateName = DB::table('marcas')
            ->whereRaw(
                'LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))',
                [$nombre]
            )
            ->where(
                'id_marca',
                '!=',
                $this->registroId
            )
            ->exists();


        if ($duplicateName) {
            $this->addError(
                'nombre',
                'Ya existe una marca con este nombre.'
            );

            return;
        }


        $duplicateCode = DB::table('marcas')
            ->whereRaw(
                'LOWER(BTRIM(codigo_interno)) = LOWER(BTRIM(?))',
                [$codigoInterno]
            )
            ->where(
                'id_marca',
                '!=',
                $this->registroId
            )
            ->exists();


        if ($duplicateCode) {
            $this->addError(
                'codigoInterno',
                'Ya existe una marca con este código interno.'
            );

            return;
        }


        if (
            $validated['idPaisOrigen'] !== null
            &&
            ! $this->paisOrigenExists(
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
                ->exists()
        ) {
            $this->addError(
                'idProveedorSugerido',
                'El proveedor seleccionado no es válido.'
            );

            return;
        }


        DB::transaction(
            function () use (
                $validated,
                $nombre,
                $codigoInterno
            ): void {
                DB::table('marcas')
                    ->where(
                        'id_marca',
                        $this->registroId
                    )
                    ->update([
                        'nombre' =>
                            $nombre,

                        'codigo_interno' =>
                            $codigoInterno,

                        'descripcion' =>
                            $this->nullableTrim(
                                $validated['descripcion']
                            ),

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

                        'fecha_actualizacion' =>
                            now(),
                    ]);


                $this->writeAudit(
                    'MARCA_ACTUALIZADA',
                    "Se actualizó la marca {$nombre}."
                );
            }
        );


        $this->nombre =
            $nombre;

        $this->nombreOriginal =
            $nombre;

        $this->codigoInterno =
            $codigoInterno;


        $this->resetValidation();


        $this->dispatch(
            'catalog-record-updated',
            message:
                'La marca se actualizó correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar modelo
    |--------------------------------------------------------------------------
    */

    private function saveModelo(): void
    {
        $validated = $this->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'codigoInterno' => [
                'required',
                'string',
                'max:50',
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

            'codigoInterno.required' =>
                'Ingresa el código interno.',

            'codigoInterno.max' =>
                'El código interno no puede superar los 50 caracteres.',

            'descripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',

            'sitioWeb.url' =>
                'Ingresa una dirección web válida.',

            'sitioWeb.max' =>
                'El sitio web no puede superar los 500 caracteres.',

            'idMarca.required' =>
                'Selecciona una marca.',

            'idTipoEquipo.required' =>
                'Selecciona un tipo de equipo.',

            'garantiaEstandarMeses.integer' =>
                'La garantía debe indicarse en meses.',

            'garantiaEstandarMeses.min' =>
                'La garantía debe ser mayor a 0 meses.',

            'garantiaEstandarMeses.max' =>
                'La garantía no puede superar los 600 meses.',

            'comentarios.max' =>
                'Los comentarios no pueden superar los 5000 caracteres.',
        ]);


        if (
            ! DB::table('marcas')
                ->where(
                    'id_marca',
                    $validated['idMarca']
                )
                ->exists()
        ) {
            $this->addError(
                'idMarca',
                'La marca seleccionada no es válida.'
            );

            return;
        }


        if (
            ! DB::table('tipos_equipo')
                ->where(
                    'id_tipo_equipo',
                    $validated['idTipoEquipo']
                )
                ->exists()
        ) {
            $this->addError(
                'idTipoEquipo',
                'El tipo de equipo seleccionado no es válido.'
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


        $codigoInterno =
            trim(
                $validated['codigoInterno']
            );


        $duplicateName = DB::table('modelos')
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
            ->where(
                'id_modelo',
                '!=',
                $this->registroId
            )
            ->exists();


        if ($duplicateName) {
            $this->addError(
                'nombre',
                'Ya existe este modelo para la marca y tipo de equipo seleccionados.'
            );

            return;
        }


        $duplicateCode = DB::table('modelos')
            ->whereRaw(
                'LOWER(BTRIM(codigo_interno)) = LOWER(BTRIM(?))',
                [$codigoInterno]
            )
            ->where(
                'id_modelo',
                '!=',
                $this->registroId
            )
            ->exists();


        if ($duplicateCode) {
            $this->addError(
                'codigoInterno',
                'Ya existe un modelo con este código interno.'
            );

            return;
        }


        DB::transaction(
            function () use (
                $validated,
                $nombre,
                $codigoInterno
            ): void {
                DB::table('modelos')
                    ->where(
                        'id_modelo',
                        $this->registroId
                    )
                    ->update([
                        'id_marca' =>
                            $validated['idMarca'],

                        'id_tipo_equipo' =>
                            $validated['idTipoEquipo'],

                        'nombre' =>
                            $nombre,

                        'codigo_interno' =>
                            $codigoInterno,

                        'descripcion' =>
                            $this->nullableTrim(
                                $validated['descripcion']
                            ),

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

                        'fecha_actualizacion' =>
                            now(),
                    ]);


                $this->writeAudit(
                    'MODELO_ACTUALIZADO',
                    "Se actualizó el modelo {$nombre}."
                );
            }
        );


        $this->nombre =
            $nombre;

        $this->nombreOriginal =
            $nombre;

        $this->codigoInterno =
            $codigoInterno;


        $this->resetValidation();


        $this->dispatch(
            'catalog-record-updated',
            message:
                'El modelo se actualizó correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Activar / desactivar
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(): void
    {
        $newStatus =
            ! $this->activo;


        if ($this->tipo === 'marca') {
            DB::transaction(
                function () use (
                    $newStatus
                ): void {
                    DB::table('marcas')
                        ->where(
                            'id_marca',
                            $this->registroId
                        )
                        ->update([
                            'activo' =>
                                $newStatus,

                            'fecha_actualizacion' =>
                                now(),
                        ]);


                    $this->writeAudit(
                        $newStatus
                            ? 'MARCA_ACTIVADA'
                            : 'MARCA_DESACTIVADA',

                        $newStatus
                            ? "Se activó la marca {$this->nombreOriginal}."
                            : "Se desactivó la marca {$this->nombreOriginal}."
                    );
                }
            );
        } else {
            DB::transaction(
                function () use (
                    $newStatus
                ): void {
                    DB::table('modelos')
                        ->where(
                            'id_modelo',
                            $this->registroId
                        )
                        ->update([
                            'activo' =>
                                $newStatus,

                            'fecha_actualizacion' =>
                                now(),
                        ]);


                    $this->writeAudit(
                        $newStatus
                            ? 'MODELO_ACTIVADO'
                            : 'MODELO_DESACTIVADO',

                        $newStatus
                            ? "Se activó el modelo {$this->nombreOriginal}."
                            : "Se desactivó el modelo {$this->nombreOriginal}."
                    );
                }
            );
        }


        $this->activo =
            $newStatus;


        $this->dispatch(
            'catalog-record-status-updated',
            message:
                $newStatus
                    ? 'El registro fue activado correctamente.'
                    : 'El registro fue desactivado correctamente.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | País de origen válido
    |--------------------------------------------------------------------------
    */

    private function paisOrigenExists(
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
            'livewire.placeholders.catalogos-editar-registro'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Marcas disponibles para modelo
        |--------------------------------------------------------------------------
        */

        $marcas =
            DB::table('marcas')
                ->where(
                    function ($query) {
                        $query->where(
                            'activo',
                            true
                        );


                        if (
                            $this->idMarca !== null
                        ) {
                            $query->orWhere(
                                'id_marca',
                                $this->idMarca
                            );
                        }
                    }
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_marca',
                    'nombre',
                    'id_pais_origen',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Tipos de equipo
        |--------------------------------------------------------------------------
        */

        $tiposEquipo =
            DB::table('tipos_equipo')
                ->where(
                    function ($query) {
                        $query->where(
                            'activo',
                            true
                        );


                        if (
                            $this->idTipoEquipo !== null
                        ) {
                            $query->orWhere(
                                'id_tipo_equipo',
                                $this->idTipoEquipo
                            );
                        }
                    }
                )
                ->orderBy(
                    'nombre'
                )
                ->get([
                    'id_tipo_equipo',
                    'nombre',
                ]);


        /*
        |--------------------------------------------------------------------------
        | Países de origen
        |--------------------------------------------------------------------------
        */

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
                    function ($query) {
                        $query->where(
                            'cv.activo',
                            true
                        );


                        if (
                            $this->idPaisOrigen !== null
                        ) {
                            $query->orWhere(
                                'cv.id_valor',
                                $this->idPaisOrigen
                            );
                        }
                    }
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


        /*
        |--------------------------------------------------------------------------
        | Proveedores
        |--------------------------------------------------------------------------
        */

        $proveedores =
            DB::table('proveedores')
                ->where(
                    function ($query) {
                        $query->where(
                            'activo',
                            true
                        );


                        if (
                            $this->idProveedorSugerido !== null
                        ) {
                            $query->orWhere(
                                'id_proveedor',
                                $this->idProveedorSugerido
                            );
                        }
                    }
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


        /*
        |--------------------------------------------------------------------------
        | Áreas
        |--------------------------------------------------------------------------
        */

        $areas =
            DB::table('areas')
                ->where(
                    function ($query) {
                        $query->where(
                            'activo',
                            true
                        );


                        if (
                            $this->idAreaUsoComun !== null
                        ) {
                            $query->orWhere(
                                'id_area',
                                $this->idAreaUsoComun
                            );
                        }
                    }
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
        | Contacto del proveedor seleccionado
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
        | País heredado del modelo
        |--------------------------------------------------------------------------
        */

        $paisOrigenModelo = null;


        if (
            $this->tipo === 'modelo'
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


        /*
        |--------------------------------------------------------------------------
        | Métricas del registro
        |--------------------------------------------------------------------------
        */

        $totalEquipos =
            $this->tipo === 'marca'
                ? DB::table('equipos')
                    ->where(
                        'id_marca',
                        $this->registroId
                    )
                    ->count()
                : DB::table('equipos')
                    ->where(
                        'id_modelo',
                        $this->registroId
                    )
                    ->count();


        $totalModelos =
            $this->tipo === 'marca'
                ? DB::table('modelos')
                    ->where(
                        'id_marca',
                        $this->registroId
                    )
                    ->count()
                : null;


        return view(
            'livewire.catalogos.editar-registro',
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

                'totalEquipos' =>
                    $totalEquipos,

                'totalModelos' =>
                    $totalModelos,
            ]
        );
    }
}