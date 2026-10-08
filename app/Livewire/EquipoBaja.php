<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class EquipoBaja extends Component
{
    use WithFileUploads;

    public int $equipoId;

    public array $equipo = [];

    public string $motivoBaja = '';

    public string $fechaBaja = '';

    public string $comentariosBaja = '';

    public string $autoriza = '';

    public $fotoFrontal = null;

    public $fotoTrasera = null;

    public $fotoLateral1 = null;

    public $fotoLateral2 = null;

    public function mount(int $equipoId): void
    {
        $this->equipoId = $equipoId;

        $this->fechaBaja = now()->format('Y-m-d');

        $this->cargarEquipo();
    }

    protected function cargarEquipo(): void
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
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
            )
            ->leftJoin(
                'proveedores as p',
                'p.id_proveedor',
                '=',
                'e.id_proveedor'
            )
            ->where(
                'e.id_equipo',
                $this->equipoId
            )
            ->select([
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.numero_factura',
                'e.fecha_compra',
                'e.fecha_fin_garantia',
                'e.fecha_registro',
                'te.nombre as tipo_equipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'ee.nombre as estado',
                'p.nombre as proveedor',
            ])
            ->first();

        abort_unless(
            $equipo,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Impedir abrir una baja que ya existe
        |--------------------------------------------------------------------------
        */

        $bajaExistente = DB::table('bajas')
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->exists();

        abort_if(
            $bajaExistente,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Preparar información para la vista
        |--------------------------------------------------------------------------
        */

        $this->equipo = [
            'id_equipo' =>
                (int) $equipo->id_equipo,

            'codigo_inventario' =>
                (string) (
                    $equipo->codigo_inventario
                    ?? ''
                ),

            'nombre_equipo' =>
                (string) (
                    $equipo->nombre_equipo
                    ?? ''
                ),

            'numero_serie' =>
                (string) (
                    $equipo->numero_serie
                    ?? ''
                ),

            'numero_factura' =>
                (string) (
                    $equipo->numero_factura
                    ?? ''
                ),

            'tipo_equipo' =>
                (string) (
                    $equipo->tipo_equipo
                    ?? ''
                ),

            'marca' =>
                (string) (
                    $equipo->marca
                    ?? ''
                ),

            'modelo' =>
                (string) (
                    $equipo->modelo
                    ?? ''
                ),

            'estado' =>
                (string) (
                    $equipo->estado
                    ?? ''
                ),

            'proveedor' =>
                (string) (
                    $equipo->proveedor
                    ?? ''
                ),

            'fecha_compra' =>
                $this->formatearFecha(
                    $equipo->fecha_compra
                    ?? null
                ),

            'fecha_fin_garantia' =>
                $this->formatearFecha(
                    $equipo->fecha_fin_garantia
                    ?? null
                ),

            'fecha_registro' =>
                $this->formatearFecha(
                    $equipo->fecha_registro
                    ?? null
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Fotografías
    |--------------------------------------------------------------------------
    */

    public function updatedFotoFrontal(): void
    {
        $this->validateOnly(
            'fotoFrontal',
            [
                'fotoFrontal' =>
                    $this->photoRules(),
            ]
        );
    }

    public function updatedFotoTrasera(): void
    {
        $this->validateOnly(
            'fotoTrasera',
            [
                'fotoTrasera' =>
                    $this->photoRules(),
            ]
        );
    }

    public function updatedFotoLateral1(): void
    {
        $this->validateOnly(
            'fotoLateral1',
            [
                'fotoLateral1' =>
                    $this->photoRules(),
            ]
        );
    }

    public function updatedFotoLateral2(): void
    {
        $this->validateOnly(
            'fotoLateral2',
            [
                'fotoLateral2' =>
                    $this->photoRules(),
            ]
        );
    }

    public function eliminarFoto(string $tipo): void
    {
        $propiedad = match ($tipo) {
            'frontal' =>
                'fotoFrontal',

            'trasera' =>
                'fotoTrasera',

            'lateral1' =>
                'fotoLateral1',

            'lateral2' =>
                'fotoLateral2',

            default =>
                null,
        };

        if ($propiedad === null) {
            return;
        }

        $this->{$propiedad} = null;

        $this->resetValidation(
            $propiedad
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmar baja
    |--------------------------------------------------------------------------
    */

    public function confirmar()
    {
        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        |
        | Las fotografías NO son obligatorias temporalmente mientras
        | resolvemos el error 422 de Livewire.
        |
        */

        $validated = $this->validate([
            'motivoBaja' => [
                'required',
                'string',
                'max:500',
            ],

            'fechaBaja' => [
                'required',
                'date',
            ],

            'comentariosBaja' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'autoriza' => [
                'required',
                'string',
                'max:255',
            ],
        ], [
            'motivoBaja.required' =>
                'Ingresa el motivo de la baja.',

            'fechaBaja.required' =>
                'Selecciona la fecha de baja.',

            'fechaBaja.date' =>
                'La fecha de baja no es válida.',

            'autoriza.required' =>
                'Ingresa el nombre de la persona que autoriza la baja.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Usuario que realiza la baja
        |--------------------------------------------------------------------------
        */

        $usuarioId = auth()->id();

        if ($usuarioId === null) {
            throw ValidationException::withMessages([
                'autoriza' =>
                    'No fue posible identificar al usuario que realiza la baja.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Transacción
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $validated,
            $usuarioId
        ): void {

            /*
            |--------------------------------------------------------------------------
            | Obtener y bloquear equipo
            |--------------------------------------------------------------------------
            */

            $equipo = DB::table('equipos')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->lockForUpdate()
                ->first([
                    'id_equipo',
                    'codigo_inventario',
                    'nombre_equipo',
                    'id_estado_activo',
                ]);

            if (! $equipo) {
                throw ValidationException::withMessages([
                    'motivoBaja' =>
                        'El equipo seleccionado ya no existe.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Evitar una segunda baja
            |--------------------------------------------------------------------------
            */

            $bajaExistente = DB::table('bajas')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->exists();

            if ($bajaExistente) {
                throw ValidationException::withMessages([
                    'motivoBaja' =>
                        'Este equipo ya se encuentra dado de baja.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Motivo interno OTRO
            |--------------------------------------------------------------------------
            |
            | Como ahora el motivo se escribe libremente, utilizamos OTRO
            | como valor del catálogo y guardamos el texto real en
            | bajas.motivo_baja.
            |
            */

            $idMotivoOtro = DB::table(
                'catalogo_valores as cv'
            )
                ->join(
                    'catalogos as c',
                    'c.id_catalogo',
                    '=',
                    'cv.id_catalogo'
                )
                ->where(
                    'c.clave',
                    'motivo_baja'
                )
                ->where(
                    'cv.clave',
                    'OTRO'
                )
                ->where(
                    'cv.activo',
                    true
                )
                ->value(
                    'cv.id_valor'
                );

            if ($idMotivoOtro === null) {
                throw ValidationException::withMessages([
                    'motivoBaja' =>
                        'No se encontró el valor OTRO del catálogo de motivos de baja.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Estado de baja COMPLETADA
            |--------------------------------------------------------------------------
            */

            $idEstadoBajaCompletada = DB::table(
                'catalogo_valores as cv'
            )
                ->join(
                    'catalogos as c',
                    'c.id_catalogo',
                    '=',
                    'cv.id_catalogo'
                )
                ->where(
                    'c.clave',
                    'estado_baja'
                )
                ->where(
                    'cv.clave',
                    'COMPLETADA'
                )
                ->where(
                    'cv.activo',
                    true
                )
                ->value(
                    'cv.id_valor'
                );

            if ($idEstadoBajaCompletada === null) {
                throw ValidationException::withMessages([
                    'motivoBaja' =>
                        'No se encontró el estado COMPLETADA para la baja.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Estado BAJA del equipo
            |--------------------------------------------------------------------------
            */

            $idEstadoEquipoBaja = DB::table(
                'estados_equipo'
            )
                ->where(
                    'clave',
                    'BAJA'
                )
                ->where(
                    'activo',
                    true
                )
                ->value(
                    'id_estado_equipo'
                );

            if ($idEstadoEquipoBaja === null) {
                throw ValidationException::withMessages([
                    'motivoBaja' =>
                        'No se encontró el estado BAJA para el equipo.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Generar folio
            |--------------------------------------------------------------------------
            |
            | Conserva el formato:
            |
            | BAJA-YYYYMMDDHHMMSSmmm-01
            |
            */

            $ultimoId = DB::table('bajas')
                ->orderByDesc(
                    'id_baja'
                )
                ->lockForUpdate()
                ->value(
                    'id_baja'
                );

            $consecutivo =
                ((int) ($ultimoId ?? 0)) + 1;

            $folio = sprintf(
                'BAJA-%s-%02d',
                now()->format('YmdHisv'),
                $consecutivo
            );

            /*
            |--------------------------------------------------------------------------
            | Crear registro de baja
            |--------------------------------------------------------------------------
            */

            DB::table('bajas')
                ->insert([
                    'folio' =>
                        $folio,

                    'id_equipo' =>
                        $this->equipoId,

                    'id_motivo_baja' =>
                        (int) $idMotivoOtro,

                    'motivo_baja' =>
                        trim(
                            (string)
                            $validated['motivoBaja']
                        ),

                    'id_estado_baja' =>
                        (int)
                        $idEstadoBajaCompletada,

                    'fecha_baja' =>
                        $validated['fechaBaja'],

                    'comentarios' =>
                        trim(
                            (string) (
                                $validated['comentariosBaja']
                                ?? ''
                            )
                        ) !== ''
                            ? trim(
                                (string)
                                $validated['comentariosBaja']
                            )
                            : null,

                    'realizado_por' =>
                        (int) $usuarioId,

                    'autoriza' =>
                        trim(
                            (string)
                            $validated['autoriza']
                        ),

                    'ruta_pdf' =>
                        null,

                    'fecha_registro' =>
                        now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Cambiar equipo a estado BAJA
            |--------------------------------------------------------------------------
            */

            DB::table('equipos')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->update([
                    'id_estado_activo' =>
                        (int)
                        $idEstadoEquipoBaja,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Cerrar asignación activa
            |--------------------------------------------------------------------------
            */

            DB::table('asignaciones')
                ->where(
                    'id_equipo',
                    $this->equipoId
                )
                ->whereNull(
                    'fecha_fin'
                )
                ->update([
                    'fecha_fin' =>
                        now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Bitácora
            |--------------------------------------------------------------------------
            */

            DB::table('bitacora_auditoria')
                ->insert([
                    'id_usuario' =>
                        (int) $usuarioId,

                    'accion' =>
                        'EQUIPO_DADO_DE_BAJA',

                    'modulo' =>
                        'EQUIPOS',

                    'descripcion' =>
                        sprintf(
                            'Se dio de baja el equipo %s. Folio: %s. Motivo: %s',
                            $equipo->codigo_inventario
                                ?: 'Sin código',
                            $folio,
                            trim(
                                (string)
                                $validated['motivoBaja']
                            )
                        ),

                    'fecha_hora' =>
                        now(),
                ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Mensaje de éxito
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'El equipo fue dado de baja correctamente.'
        );

        /*
        |--------------------------------------------------------------------------
        | Regresar a Equipos Tecnológicos
        |--------------------------------------------------------------------------
        */

        return $this->redirectRoute(
            'equipos.index',
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas para fotografías
    |--------------------------------------------------------------------------
    */

    private function photoRules(): array
    {
        return [
            'nullable',
            'image',
            'max:5120',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Formatear fecha
    |--------------------------------------------------------------------------
    */

    private function formatearFecha(
        mixed $fecha
    ): string {
        if (
            $fecha === null ||
            $fecha === ''
        ) {
            return '—';
        }

        try {
            return Carbon::parse(
                $fecha
            )->format(
                'd/m/Y'
            );
        } catch (\Throwable) {
            return (string) $fecha;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.equipo-baja'
        );
    }
}