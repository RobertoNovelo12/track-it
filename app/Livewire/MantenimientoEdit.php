<?php

namespace App\Livewire;

use App\Models\CatalogoValor;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MantenimientoEdit extends Component
{
    public int $mantenimientoId;

    public string $equipoId = '';
    public string $tipoMantenimiento = '';
    public string $tipoIntervencion = '';
    public string $estadoMantenimiento = '';
    public string $prioridad = '';
    public string $tecnicoId = '';

    public string $fechaIntervencion = '';
    public string $horaInicio = '';

    public string $fechaFin = '';
    public string $horaFin = '';

    public string $fallaReportada = '';
    public string $diagnostico = '';
    public string $intervencionRealizada = '';
    public string $observaciones = '';
    public string $fechaProximoMantenimiento = '';

    public array $refacciones = [];


    /*
    |--------------------------------------------------------------------------
    | Cargar mantenimiento
    |--------------------------------------------------------------------------
    */
    public function mount(int $mantenimientoId): void
    {
        $this->mantenimientoId = $mantenimientoId;

        $mantenimiento = DB::table('mantenimientos')
            ->where(
                'id_mantenimiento',
                $this->mantenimientoId
            )
            ->first();

        abort_if(
            ! $mantenimiento,
            404,
            'El mantenimiento no existe.'
        );

        $this->equipoId =
            (string) $mantenimiento->id_equipo;

        $this->tipoMantenimiento =
            (string) $mantenimiento->id_tipo_mantenimiento;

        $this->tipoIntervencion =
            $mantenimiento->id_tipo_intervencion
                ? (string) $mantenimiento->id_tipo_intervencion
                : '';

        $this->estadoMantenimiento =
            (string) $mantenimiento->id_estado_mantenimiento;

        $this->prioridad =
            $mantenimiento->id_prioridad
                ? (string) $mantenimiento->id_prioridad
                : '';

        $this->tecnicoId =
            (string) $mantenimiento->id_tecnico;

        $this->fechaIntervencion =
            $mantenimiento->fecha_intervencion
                ? (string) $mantenimiento->fecha_intervencion
                : '';

        $this->horaInicio =
            $mantenimiento->hora_inicio
                ? substr(
                    (string) $mantenimiento->hora_inicio,
                    0,
                    5
                )
                : '';

        $this->fechaFin =
            $mantenimiento->fecha_fin
                ? (string) $mantenimiento->fecha_fin
                : '';

        $this->horaFin =
            $mantenimiento->hora_fin
                ? substr(
                    (string) $mantenimiento->hora_fin,
                    0,
                    5
                )
                : '';

        $this->fallaReportada =
            $mantenimiento->falla_reportada ?? '';

        $this->diagnostico =
            $mantenimiento->diagnostico ?? '';

        $this->intervencionRealizada =
            $mantenimiento->intervencion_realizada ?? '';

        $this->observaciones =
            $mantenimiento->observaciones ?? '';

        $this->fechaProximoMantenimiento =
            $mantenimiento->fecha_proximo_mantenimiento
                ? (string) $mantenimiento->fecha_proximo_mantenimiento
                : '';


        /*
        |--------------------------------------------------------------------------
        | Refacciones existentes
        |--------------------------------------------------------------------------
        */
        $this->refacciones = DB::table(
            'mantenimiento_refacciones'
        )
            ->where(
                'id_mantenimiento',
                $this->mantenimientoId
            )
            ->orderBy('id_mantenimiento_refaccion')
            ->get([
                'id_mantenimiento_refaccion',
                'nombre_refaccion',
                'codigo_parte',
                'cantidad',
                'unidad',
            ])
            ->map(function ($refaccion) {
                return [
                    'id_mantenimiento_refaccion' =>
                        $refaccion->id_mantenimiento_refaccion,

                    'nombre_refaccion' =>
                        $refaccion->nombre_refaccion ?? '',

                    'codigo_parte' =>
                        $refaccion->codigo_parte ?? '',

                    'cantidad' =>
                        $refaccion->cantidad,

                    'unidad' =>
                        $refaccion->unidad ?? '',
                ];
            })
            ->values()
            ->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | Agregar refacción
    |--------------------------------------------------------------------------
    */
    public function agregarRefaccion(): void
    {
        $this->refacciones[] = [
            'id_mantenimiento_refaccion' => null,
            'nombre_refaccion' => '',
            'codigo_parte' => '',
            'cantidad' => 1,
            'unidad' => '',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Eliminar refacción del formulario
    |--------------------------------------------------------------------------
    */
    public function eliminarRefaccion(int $index): void
    {
        if (! isset($this->refacciones[$index])) {
            return;
        }

        unset($this->refacciones[$index]);

        $this->refacciones =
            array_values($this->refacciones);

        $this->resetValidation('refacciones');
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar cambios
    |--------------------------------------------------------------------------
    */
    public function guardar(): void
    {
        $validated = $this->validate([
            'equipoId' => [
                'required',
                'exists:equipos,id_equipo',
            ],

            'tipoMantenimiento' => [
                'required',
                'exists:catalogo_valores,id_valor',
            ],

            'tipoIntervencion' => [
                'nullable',
                'exists:catalogo_valores,id_valor',
            ],

            'estadoMantenimiento' => [
                'required',
                'exists:catalogo_valores,id_valor',
            ],

            'prioridad' => [
                'nullable',
                'exists:catalogo_valores,id_valor',
            ],

            'tecnicoId' => [
                'required',
                'exists:usuarios,id_usuario',
            ],

            'fechaIntervencion' => [
                'required',
                'date',
            ],

            'horaInicio' => [
                'nullable',
                'date_format:H:i',
            ],

            'fechaFin' => [
                'nullable',
                'date',
                'after_or_equal:fechaIntervencion',
            ],

            'horaFin' => [
                'nullable',
                'date_format:H:i',
            ],

            'fallaReportada' => [
                'nullable',
                'string',
            ],

            'diagnostico' => [
                'nullable',
                'string',
            ],

            'intervencionRealizada' => [
                'nullable',
                'string',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],

            'fechaProximoMantenimiento' => [
                'nullable',
                'date',
            ],

            'refacciones' => [
                'array',
            ],

            'refacciones.*.id_mantenimiento_refaccion' => [
                'nullable',
                'integer',
            ],

            'refacciones.*.nombre_refaccion' => [
                'required',
                'string',
            ],

            'refacciones.*.codigo_parte' => [
                'nullable',
                'string',
            ],

            'refacciones.*.cantidad' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'refacciones.*.unidad' => [
                'nullable',
                'string',
            ],
        ], [
            'equipoId.required' =>
                'Selecciona un equipo.',

            'tipoMantenimiento.required' =>
                'Selecciona el tipo de mantenimiento.',

            'estadoMantenimiento.required' =>
                'Selecciona el estado.',

            'tecnicoId.required' =>
                'Selecciona el técnico responsable.',

            'fechaIntervencion.required' =>
                'Selecciona la fecha de inicio.',

            'fechaFin.after_or_equal' =>
                'La fecha de finalización no puede ser anterior a la fecha de inicio.',

            'refacciones.*.nombre_refaccion.required' =>
                'Ingresa el nombre de la refacción.',

            'refacciones.*.cantidad.required' =>
                'Ingresa la cantidad.',

            'refacciones.*.cantidad.gt' =>
                'La cantidad debe ser mayor a cero.',
        ]);


        DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Actualizar mantenimiento
            |--------------------------------------------------------------------------
            */
            DB::table('mantenimientos')
                ->where(
                    'id_mantenimiento',
                    $this->mantenimientoId
                )
                ->update([
                    'id_tipo_mantenimiento' =>
                        (int) $validated['tipoMantenimiento'],

                    'id_tipo_intervencion' =>
                        filled($validated['tipoIntervencion'])
                            ? (int) $validated['tipoIntervencion']
                            : null,

                    'id_estado_mantenimiento' =>
                        (int) $validated['estadoMantenimiento'],

                    'id_prioridad' =>
                        filled($validated['prioridad'])
                            ? (int) $validated['prioridad']
                            : null,

                    'id_tecnico' =>
                        (int) $validated['tecnicoId'],

                    'fecha_intervencion' =>
                        $validated['fechaIntervencion'],

                    'hora_inicio' =>
                        filled($validated['horaInicio'])
                            ? $validated['horaInicio']
                            : null,

                    'fecha_fin' =>
                        filled($validated['fechaFin'])
                            ? $validated['fechaFin']
                            : null,

                    'hora_fin' =>
                        filled($validated['horaFin'])
                            ? $validated['horaFin']
                            : null,

                    'falla_reportada' =>
                        $this->nullableText(
                            $validated['fallaReportada']
                        ),

                    'diagnostico' =>
                        $this->nullableText(
                            $validated['diagnostico']
                        ),

                    'intervencion_realizada' =>
                        $this->nullableText(
                            $validated['intervencionRealizada']
                        ),

                    'observaciones' =>
                        $this->nullableText(
                            $validated['observaciones']
                        ),

                    'fecha_proximo_mantenimiento' =>
                        filled(
                            $validated[
                                'fechaProximoMantenimiento'
                            ]
                        )
                            ? $validated[
                                'fechaProximoMantenimiento'
                            ]
                            : null,
                ]);


            /*
            |--------------------------------------------------------------------------
            | IDs de refacciones conservadas
            |--------------------------------------------------------------------------
            */
            $idsConservados = collect(
                $validated['refacciones'] ?? []
            )
                ->pluck(
                    'id_mantenimiento_refaccion'
                )
                ->filter()
                ->map(
                    fn ($id) => (int) $id
                )
                ->values()
                ->all();


            /*
            |--------------------------------------------------------------------------
            | Eliminar únicamente las refacciones quitadas
            |--------------------------------------------------------------------------
            */
            $queryRefacciones = DB::table(
                'mantenimiento_refacciones'
            )
                ->where(
                    'id_mantenimiento',
                    $this->mantenimientoId
                );

            if (count($idsConservados) > 0) {
                $queryRefacciones->whereNotIn(
                    'id_mantenimiento_refaccion',
                    $idsConservados
                );
            }

            $queryRefacciones->delete();


            /*
            |--------------------------------------------------------------------------
            | Actualizar o crear refacciones
            |--------------------------------------------------------------------------
            */
            foreach (
                $validated['refacciones'] ?? []
                as $refaccion
            ) {

                $datos = [
                    'nombre_refaccion' =>
                        trim(
                            $refaccion[
                                'nombre_refaccion'
                            ]
                        ),

                    'codigo_parte' =>
                        filled(
                            $refaccion[
                                'codigo_parte'
                            ] ?? null
                        )
                            ? trim(
                                $refaccion[
                                    'codigo_parte'
                                ]
                            )
                            : null,

                    'cantidad' =>
                        $refaccion['cantidad'],

                    'unidad' =>
                        filled(
                            $refaccion[
                                'unidad'
                            ] ?? null
                        )
                            ? trim(
                                $refaccion['unidad']
                            )
                            : null,
                ];


                if (
                    filled(
                        $refaccion[
                            'id_mantenimiento_refaccion'
                        ] ?? null
                    )
                ) {
                    DB::table(
                        'mantenimiento_refacciones'
                    )
                        ->where(
                            'id_mantenimiento_refaccion',
                            (int) $refaccion[
                                'id_mantenimiento_refaccion'
                            ]
                        )
                        ->where(
                            'id_mantenimiento',
                            $this->mantenimientoId
                        )
                        ->update($datos);

                    continue;
                }


                DB::table(
                    'mantenimiento_refacciones'
                )
                    ->insert([
                        'id_mantenimiento' =>
                            $this->mantenimientoId,

                        ...$datos,
                    ]);
            }
        });


        session()->flash(
            'mantenimientoActualizado',
            'El mantenimiento se actualizó correctamente.'
        );


        $this->redirectRoute(
            'mantenimientos.index',
            navigate: true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Convertir texto vacío a NULL
    |--------------------------------------------------------------------------
    */
    private function nullableText(
        ?string $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
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
        | Equipos
        |--------------------------------------------------------------------------
        */
        $equipos = DB::table('equipos as e')
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
            ->select([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.service_tag',
                'e.fecha_fin_garantia',

                'te.nombre as tipo_equipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
            ])
            ->orderBy('e.codigo_inventario')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Equipo seleccionado
        |--------------------------------------------------------------------------
        */
        $equipoSeleccionado = null;

        if ($this->equipoId !== '') {
            $equipoSeleccionado =
                $equipos->firstWhere(
                    'id_equipo',
                    (int) $this->equipoId
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Catálogos
        |--------------------------------------------------------------------------
        */
        $tiposMantenimiento =
            CatalogoValor::deCatalogo(
                'tipo_mantenimiento'
            )->get([
                'id_valor',
                'nombre',
            ]);


        $tiposIntervencion =
            CatalogoValor::deCatalogo(
                'tipo_intervencion'
            )->get([
                'id_valor',
                'nombre',
            ]);


        $estadosMantenimiento =
            CatalogoValor::deCatalogo(
                'estado_mantenimiento'
            )->get([
                'id_valor',
                'nombre',
            ]);


        $prioridades =
            CatalogoValor::deCatalogo(
                'prioridad_mantenimiento'
            )->get([
                'id_valor',
                'nombre',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Técnicos
        |--------------------------------------------------------------------------
        */
        $tecnicos = DB::table('usuarios')
            ->select([
                'id_usuario',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
            ])
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();


        return view(
            'livewire.mantenimiento-edit',
            [
                'equipos' =>
                    $equipos,

                'equipoSeleccionado' =>
                    $equipoSeleccionado,

                'tiposMantenimiento' =>
                    $tiposMantenimiento,

                'tiposIntervencion' =>
                    $tiposIntervencion,

                'estadosMantenimiento' =>
                    $estadosMantenimiento,

                'prioridades' =>
                    $prioridades,

                'tecnicos' =>
                    $tecnicos,
            ]
        );
    }
}