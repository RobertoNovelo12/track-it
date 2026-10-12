<?php
namespace App\Livewire;

use App\Models\CatalogoValor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class MantenimientoCreate extends Component
{
    public string $equipoId            = '';
    public string $tipoMantenimiento   = '';
    public string $tipoIntervencion    = '';
    public string $estadoMantenimiento = '';
    public string $prioridad           = '';
    public string $tecnicoId           = '';

    public string $fechaIntervencion = '';
    public string $horaInicio        = '';

    public string $fechaFin = '';
    public string $horaFin  = '';

    public string $fallaReportada            = '';
    public string $diagnostico               = '';
    public string $intervencionRealizada     = '';
    public string $observaciones             = '';
    public string $fechaProximoMantenimiento = '';

    public array $refacciones = [];

    public function mount(): void
    {
        $this->fechaIntervencion = now()->format('Y-m-d');
    }

    public function agregarRefaccion(): void
    {
        $this->refacciones[] = [
            'nombre_refaccion' => '',
            'codigo_parte'     => '',
            'cantidad'         => 1,
            'unidad'           => '',
        ];
    }

    public function eliminarRefaccion(int $index): void
    {
        if (! isset($this->refacciones[$index])) {
            return;
        }

        unset($this->refacciones[$index]);

        $this->refacciones = array_values($this->refacciones);

        $this->resetValidation('refacciones');
    }

    public function guardar(): void
    {
        $validated = $this->validate([
            'equipoId'                       => [
                'required',
                'exists:equipos,id_equipo',
            ],

            'tipoMantenimiento'              => [
                'required',
                'exists:catalogo_valores,id_valor',
            ],

            'tipoIntervencion'               => [
                'nullable',
                'exists:catalogo_valores,id_valor',
            ],

            'estadoMantenimiento'            => [
                'required',
                'exists:catalogo_valores,id_valor',
            ],

            'prioridad'                      => [
                'nullable',
                'exists:catalogo_valores,id_valor',
            ],

            'tecnicoId'                      => [
                'required',
                'exists:usuarios,id_usuario',
            ],

            'fechaIntervencion'              => [
                'required',
                'date',
            ],

            'horaInicio'                     => [
                'nullable',
                'date_format:H:i',
            ],

            'fechaFin'                       => [
                'nullable',
                'date',
                'after_or_equal:fechaIntervencion',
            ],

            'horaFin'                        => [
                'nullable',
                'date_format:H:i',
            ],

            'fallaReportada'                 => [
                'nullable',
                'string',
            ],

            'diagnostico'                    => [
                'nullable',
                'string',
            ],

            'intervencionRealizada'          => [
                'nullable',
                'string',
            ],

            'observaciones'                  => [
                'nullable',
                'string',
            ],

            'fechaProximoMantenimiento'      => [
                'nullable',
                'date',
            ],

            'refacciones'                    => [
                'array',
            ],

            'refacciones.*.nombre_refaccion' => [
                'required',
                'string',
            ],

            'refacciones.*.codigo_parte'     => [
                'nullable',
                'string',
            ],

            'refacciones.*.cantidad'         => [
                'required',
                'numeric',
                'gt:0',
            ],

            'refacciones.*.unidad'           => [
                'nullable',
                'string',
            ],
        ], [
            'equipoId.required'                       => 'Selecciona un equipo.',
            'tipoMantenimiento.required'              => 'Selecciona el tipo de mantenimiento.',
            'estadoMantenimiento.required'            => 'Selecciona el estado.',
            'tecnicoId.required'                      => 'Selecciona el técnico responsable.',
            'fechaIntervencion.required'              => 'Selecciona la fecha de inicio.',

            'fechaFin.after_or_equal'                 =>
            'La fecha de finalización no puede ser anterior a la fecha de inicio.',

            'refacciones.*.nombre_refaccion.required' =>
            'Ingresa el nombre de la refacción.',

            'refacciones.*.cantidad.required'         =>
            'Ingresa la cantidad.',

            'refacciones.*.cantidad.gt'               =>
            'La cantidad debe ser mayor a cero.',
        ]);

        DB::transaction(function () use ($validated) {
            $mantenimientoId = DB::table('mantenimientos')
                ->insertGetId([
                    'folio'                       => $this->generarFolio(),

                    'id_equipo'                   =>
                    (int) $validated['equipoId'],

                    'id_tipo_mantenimiento'       =>
                    (int) $validated['tipoMantenimiento'],

                    'id_tipo_intervencion'        =>
                    filled($validated['tipoIntervencion'])
                        ? (int) $validated['tipoIntervencion']
                        : null,

                    'id_estado_mantenimiento'     =>
                    (int) $validated['estadoMantenimiento'],

                    'id_prioridad'                =>
                    filled($validated['prioridad'])
                        ? (int) $validated['prioridad']
                        : null,

                    'id_tecnico'                  =>
                    (int) $validated['tecnicoId'],

                    'fecha_intervencion'          =>
                    $validated['fechaIntervencion'],

                    'hora_inicio'                 =>
                    filled($validated['horaInicio'])
                        ? $validated['horaInicio']
                        : null,

                    'fecha_fin'                   =>
                    filled($validated['fechaFin'])
                        ? $validated['fechaFin']
                        : null,

                    'hora_fin'                    =>
                    filled($validated['horaFin'])
                        ? $validated['horaFin']
                        : null,

                    'falla_reportada'             =>
                    $this->nullableText(
                        $validated['fallaReportada']
                    ),

                    'diagnostico'                 =>
                    $this->nullableText(
                        $validated['diagnostico']
                    ),

                    'intervencion_realizada'      =>
                    $this->nullableText(
                        $validated['intervencionRealizada']
                    ),

                    'observaciones'               =>
                    $this->nullableText(
                        $validated['observaciones']
                    ),

                    'fecha_proximo_mantenimiento' =>
                    filled($validated['fechaProximoMantenimiento'])
                        ? $validated['fechaProximoMantenimiento']
                        : null,

                    'fecha_registro'              => now(),
                ], 'id_mantenimiento');

            foreach ($validated['refacciones'] ?? [] as $refaccion) {
                DB::table('mantenimiento_refacciones')
                    ->insert([
                        'id_mantenimiento' => $mantenimientoId,

                        'nombre_refaccion' =>
                        trim($refaccion['nombre_refaccion']),

                        'codigo_parte'     =>
                        filled($refaccion['codigo_parte'] ?? null)
                            ? trim($refaccion['codigo_parte'])
                            : null,

                        'cantidad'         =>
                        $refaccion['cantidad'],

                        'unidad'           =>
                        filled($refaccion['unidad'] ?? null)
                            ? trim($refaccion['unidad'])
                            : null,
                    ]);
            }
        });

        session()->flash(
            'mantenimientoCreado',
            'El mantenimiento se registró correctamente.'
        );

        $this->redirectRoute(
            'mantenimientos.index',
            navigate: true
        );
    }

    private function generarFolio(): string
    {
        do {
            $folio =
            'MNT-' .
            now()->format('Ymd-His') .
            '-' .
            strtoupper(Str::random(4));
        } while (
            DB::table('mantenimientos')
            ->where('folio', $folio)
            ->exists()
        );

        return $folio;
    }

    private function nullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    public function render()
    {
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

        $equipoSeleccionado = null;

        if ($this->equipoId !== '') {
            $equipoSeleccionado = $equipos->firstWhere(
                'id_equipo',
                (int) $this->equipoId
            );
        }

        $tiposMantenimiento = CatalogoValor::deCatalogo(
            'tipo_mantenimiento'
        )->get([
            'id_valor',
            'nombre',
        ]);

        $tiposIntervencion = CatalogoValor::deCatalogo(
            'tipo_intervencion'
        )->get([
            'id_valor',
            'nombre',
        ]);

        $estadosMantenimiento = CatalogoValor::deCatalogo(
            'estado_mantenimiento'
        )->get([
            'id_valor',
            'nombre',
        ]);

        $prioridades = CatalogoValor::deCatalogo(
            'prioridad_mantenimiento'
        )->get([
            'id_valor',
            'nombre',
        ]);

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

        return view('livewire.mantenimiento-create', [
            'equipos'              => $equipos,
            'equipoSeleccionado'   => $equipoSeleccionado,
            'tiposMantenimiento'   => $tiposMantenimiento,
            'tiposIntervencion'    => $tiposIntervencion,
            'estadosMantenimiento' => $estadosMantenimiento,
            'prioridades'          => $prioridades,
            'tecnicos'             => $tecnicos,
        ]);
    }
}
