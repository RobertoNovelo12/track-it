<?php

namespace App\Livewire;

use App\Services\ActaBajaIndividualService;
use App\Services\SystemCacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class EquipoBaja extends Component
{
    use \App\Livewire\Concerns\RecogeFirmasBaja;
    use WithFileUploads;

    public const MAXIMO_COLA = 10;

    public int $equipoId;
    public array $equipo = [];

    public string $numeroTicket = '';
    public string $motivoBaja = '';
    public string $fechaBaja = '';
    public string $comentariosBaja = '';
    public string $autoriza = '';

    public $fotoFrontal = null;
    public $fotoTrasera = null;
    public $fotoLateral1 = null;
    public $fotoLateral2 = null;

    #[Locked]
    public array $cola = [];

    #[Locked]
    public array $pendientes = [];

    #[Locked]
    public array $hechos = [];

    #[Locked]
    public array $codigosCola = [];

    #[Locked]
    public array $tiposCola = [];

    /*
    |--------------------------------------------------------------------------
    | INICIALIZACIÓN
    |--------------------------------------------------------------------------
    */

    public function mount(int $equipoId, ?string $lote = null): void
    {
        Gate::authorize('gestionar');

        $this->equipoId = $equipoId;

        // Conservar la fecha del equipo anterior.
        $fechaAnterior = ($lote !== null && $lote !== '')
            ? session('baja_lote_fecha')
            : null;

        $this->fechaBaja = is_string($fechaAnterior)
            && preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $fechaAnterior
            )
                ? $fechaAnterior
                : now('America/Cancun')->format('Y-m-d');

        // Limpiar todos los campos capturables.
        $this->limpiarFormularioDeBaja();

        // Preparar equipos seleccionados.
        $this->prepararCola($lote);

        // Cargar información real del equipo actual.
        $this->cargarEquipo();
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR FORMULARIO
    |--------------------------------------------------------------------------
    */

    protected function limpiarFormularioDeBaja(): void
    {
        // Limpiar motivo, comentarios y autorización.
        $this->numeroTicket = '';
        $this->motivoBaja = '';
        $this->comentariosBaja = '';
        $this->autoriza = '';

        // Quitar fotografías anteriores.
        $this->fotoFrontal = null;
        $this->fotoTrasera = null;
        $this->fotoLateral1 = null;
        $this->fotoLateral2 = null;

        // Limpiar los cinco responsables.
        $this->firmas = array_fill_keys(
            array_keys(self::FIRMANTES),
            ''
        );

        // Eliminar mensajes de validación.
        $this->resetValidation();

        // No limpiar fechaBaja.
        // No limpiar equipoId.
        // No limpiar información general del equipo.
    }

    /*
    |--------------------------------------------------------------------------
    | PREPARAR COLA DE EQUIPOS
    |--------------------------------------------------------------------------
    */

    protected function prepararCola(?string $cola): void
    {
        $ids = collect(
            explode(',', (string) $cola)
        )
            ->map(
                fn ($id) => (int) trim($id)
            )
            ->filter(
                fn (int $id) => $id > 0
            )
            ->unique()
            ->take(self::MAXIMO_COLA)
            ->values()
            ->all();

        if (
            count($ids) < 2 ||
            !in_array(
                $this->equipoId,
                $ids,
                true
            )
        ) {
            return;
        }

        $this->cola = $ids;

        $this->codigosCola = DB::table('equipos')
            ->whereIn('id_equipo', $ids)
            ->pluck(
                'codigo_inventario',
                'id_equipo'
            )
            ->map(
                fn ($codigo) =>
                    (string) ($codigo ?: 'Sin código')
            )
            ->all();

        $this->tiposCola = DB::table('equipos as e')
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->whereIn('e.id_equipo', $ids)
            ->pluck('te.nombre')
            ->map(
                fn ($tipo) =>
                    (string) ($tipo ?: 'Sin tipo')
            )
            ->unique()
            ->values()
            ->all();

        // Identificar equipos ya dados de baja.
        $yaDeBaja = $this->idsDadosDeBaja($ids);

        // Equipos pendientes.
        $this->pendientes = array_values(
            array_diff($ids, $yaDeBaja)
        );

        // Equipos procesados anteriormente.
        $this->hechos = array_values(
            array_intersect($ids, $yaDeBaja)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTAR ESTADO DE LAS BAJAS
    |--------------------------------------------------------------------------
    */

    protected function idsDadosDeBaja(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('equipos as e')
            ->leftJoin(
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
            )
            ->whereIn('e.id_equipo', $ids)
            ->where(function ($query): void {
                $query
                    ->where('ee.clave', 'BAJA')
                    ->orWhereExists(
                        fn ($sub) => $sub
                            ->selectRaw('1')
                            ->from('bajas as b')
                            ->whereColumn(
                                'b.id_equipo',
                                'e.id_equipo'
                            )
                    );
            })
            ->pluck('e.id_equipo')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function enCola(): bool
    {
        return $this->cola !== [];
    }

    /*
    |--------------------------------------------------------------------------
    | AVANZAR A LA SIGUIENTE BAJA
    |--------------------------------------------------------------------------
    */

    protected function avanzarCola(
        ?string $avisoHecho,
        int $reporteId
    ) {
        $finalizadas = $this->idsDadosDeBaja(
            $this->cola
        );

        $pendientes = array_values(
            array_diff(
                $this->cola,
                $finalizadas
            )
        );

        // Preparar descarga del PDF actual.
        session()->flash(
            'descargar_reporte',
            $reporteId
        );

        /*
         * Si no quedan equipos, volver a Inventario.
         */
        if ($pendientes === []) {
            session()->flash('toast', [
                'type' => 'success',
                'message' =>
                    'Bajas terminadas. Las actas individuales están disponibles en Reportes.',
            ]);

            return $this->redirectRoute(
                'equipos.index',
                navigate: true
            );
        }

        /*
         * Hay otro equipo pendiente.
         */
        $siguienteId = (int) $pendientes[0];

        // La fecha es el único dato del formulario
        // anterior que se conserva.
        session()->flash(
            'baja_lote_fecha',
            $this->fechaBaja
        );

        session()->flash('toast', [
            'type' => 'success',
            'message' => trim(
                ($avisoHecho ?? 'Acta generada.')
                .' Continúa con el siguiente equipo.'
            ),
        ]);

        /*
         * Redirigir a un formulario nuevo.
         */
        return $this->redirectRoute(
            'equipos.baja',
            [
                'equipo' => $siguienteId,
                'cola' => implode(',', $this->cola),
            ],
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CANCELAR Y AVANZAR SIN REGISTRAR LA BAJA
    |--------------------------------------------------------------------------
    */

    public function omitir()
    {
        Gate::authorize('gestionar');

        $posicion = array_search($this->equipoId, $this->cola, true);
        $restantes = $posicion === false ? [] : array_slice($this->cola, $posicion + 1);
        $restantes = array_values(array_diff($restantes, $this->idsDadosDeBaja($restantes)));

        if ($restantes === []) {
            return $this->redirectRoute('equipos.index', navigate: true);
        }

        session()->flash('baja_lote_fecha', $this->fechaBaja);

        return $this->redirectRoute('equipos.baja', [
            'equipo' => $restantes[0],
            'cola' => implode(',', $restantes),
        ], navigate: true);
    }

    /*
    |--------------------------------------------------------------------------
    | CARGAR INFORMACIÓN GENERAL DEL EQUIPO
    |--------------------------------------------------------------------------
    */

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
                'ee.clave as estado_clave',
                'p.nombre as proveedor',
            ])
            ->first();

        abort_unless($equipo, 404);

        $bajaExistente = DB::table('bajas')
            ->where(
                'id_equipo',
                $this->equipoId
            )
            ->exists();

        abort_if(
            $bajaExistente ||
            $equipo->estado_clave === 'BAJA',
            404
        );

        $this->equipo = [
            'id_equipo' =>
                (int) $equipo->id_equipo,

            'codigo_inventario' =>
                (string) (
                    $equipo->codigo_inventario ?? ''
                ),

            'nombre_equipo' =>
                (string) (
                    $equipo->nombre_equipo ?? ''
                ),

            'numero_serie' =>
                (string) (
                    $equipo->numero_serie ?? ''
                ),

            'numero_factura' =>
                (string) (
                    $equipo->numero_factura ?? ''
                ),

            'tipo_equipo' =>
                (string) (
                    $equipo->tipo_equipo ?? ''
                ),

            'marca' =>
                (string) (
                    $equipo->marca ?? ''
                ),

            'modelo' =>
                (string) (
                    $equipo->modelo ?? ''
                ),

            'estado' =>
                (string) (
                    $equipo->estado ?? ''
                ),

            'proveedor' =>
                (string) (
                    $equipo->proveedor ?? ''
                ),

            'fecha_compra' =>
                $this->formatearFecha(
                    $equipo->fecha_compra ?? null
                ),

            'fecha_fin_garantia' =>
                $this->formatearFecha(
                    $equipo->fecha_fin_garantia ?? null
                ),

            'fecha_registro' =>
                $this->formatearFecha(
                    $equipo->fecha_registro ?? null
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAR FOTOGRAFÍAS
    |--------------------------------------------------------------------------
    */

    public function updatedFotoFrontal(): void
    {
        $this->validateOnly(
            'fotoFrontal',
            ['fotoFrontal' => $this->photoRules()]
        );
    }

    public function updatedFotoTrasera(): void
    {
        $this->validateOnly(
            'fotoTrasera',
            ['fotoTrasera' => $this->photoRules()]
        );
    }

    public function updatedFotoLateral1(): void
    {
        $this->validateOnly(
            'fotoLateral1',
            ['fotoLateral1' => $this->photoRules()]
        );
    }

    public function updatedFotoLateral2(): void
    {
        $this->validateOnly(
            'fotoLateral2',
            ['fotoLateral2' => $this->photoRules()]
        );
    }

    public function eliminarFoto(string $tipo): void
    {
        $propiedad = match ($tipo) {
            'frontal' => 'fotoFrontal',
            'trasera' => 'fotoTrasera',
            'lateral1' => 'fotoLateral1',
            'lateral2' => 'fotoLateral2',
            default => null,
        };

        if ($propiedad === null) {
            return;
        }

        $this->{$propiedad} = null;
        $this->resetValidation($propiedad);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR BAJA
    |--------------------------------------------------------------------------
    */

    public function confirmar()
    {
        Gate::authorize('gestionar');

        $validated = $this->validate([
            'numeroTicket' => ['required', 'string', 'max:26'], // texto libre (columna de 26)
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
            'fotoFrontal' => $this->photoRules(),
            'fotoTrasera' => $this->photoRules(),
            'fotoLateral1' => $this->photoRules(),
            'fotoLateral2' => $this->photoRules(),
            ...$this->reglasFirmas(),
        ], [
            'numeroTicket.required' => 'Ingresa el número de tiket.',
            'numeroTicket.max' => 'El número de tiket puede tener hasta 26 caracteres.',
            'numeroTicket.max' => 'El número de tiket no debe superar 20 dígitos.',
            'motivoBaja.required' =>
                'Ingresa el motivo de la baja.',
            'fechaBaja.required' =>
                'Selecciona la fecha de baja.',
            'fechaBaja.date' =>
                'La fecha de baja no es válida.',
            'autoriza.required' =>
                'Ingresa el nombre de la persona que autoriza la baja.',
            'fotoFrontal.image' =>
                'La fotografía frontal debe ser una imagen.',
            'fotoTrasera.image' =>
                'La fotografía trasera debe ser una imagen.',
            'fotoLateral1.image' =>
                'La primera fotografía lateral debe ser una imagen.',
            'fotoLateral2.image' =>
                'La segunda fotografía lateral debe ser una imagen.',
            'fotoFrontal.max' =>
                'La fotografía frontal no debe superar 5 MB.',
            'fotoTrasera.max' =>
                'La fotografía trasera no debe superar 5 MB.',
            'fotoLateral1.max' =>
                'La primera fotografía lateral no debe superar 5 MB.',
            'fotoLateral2.max' =>
                'La segunda fotografía lateral no debe superar 5 MB.',
        ]);

        $usuarioId = auth()->id();

        if ($usuarioId === null) {
            throw ValidationException::withMessages([
                'autoriza' =>
                    'No fue posible identificar al usuario que realiza la baja.',
            ]);
        }

        $archivosGuardados = [];
        $rutaPdf = null;
        $reporteId = null;

        try {
            DB::transaction(function () use (
                $validated,
                $usuarioId,
                &$archivosGuardados,
                &$rutaPdf,
                &$reporteId
            ): void {
                /*
                 * Evitar dos bajas simultáneas.
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

                if (!$equipo) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'El equipo seleccionado ya no existe.',
                    ]);
                }

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
                 * Catálogo del motivo de baja.
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
                    ->where('c.clave', 'motivo_baja')
                    ->where('cv.clave', 'OTRO')
                    ->where('cv.activo', true)
                    ->value('cv.id_valor');

                if ($idMotivoOtro === null) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'No se encontró el valor OTRO del catálogo de motivos de baja.',
                    ]);
                }

                /*
                 * Estado COMPLETADA para la baja.
                 */
                $idEstadoCompletada = DB::table(
                    'catalogo_valores as cv'
                )
                    ->join(
                        'catalogos as c',
                        'c.id_catalogo',
                        '=',
                        'cv.id_catalogo'
                    )
                    ->where('c.clave', 'estado_baja')
                    ->where('cv.clave', 'COMPLETADA')
                    ->where('cv.activo', true)
                    ->value('cv.id_valor');

                if ($idEstadoCompletada === null) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'No se encontró el estado COMPLETADA para la baja.',
                    ]);
                }

                /*
                 * Estado BAJA del equipo.
                 */
                $idEstadoEquipoBaja = DB::table(
                    'estados_equipo'
                )
                    ->where('clave', 'BAJA')
                    ->where('activo', true)
                    ->value('id_estado_equipo');

                if ($idEstadoEquipoBaja === null) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'No se encontró el estado BAJA para el equipo.',
                    ]);
                }

                if (
                    (int) $equipo->id_estado_activo
                    === (int) $idEstadoEquipoBaja
                ) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'El equipo ya tiene estado BAJA.',
                    ]);
                }

                /*
                 * Generar folio individual.
                 */
                $ultimoId = DB::table('bajas')
                    ->orderByDesc('id_baja')
                    ->lockForUpdate()
                    ->value('id_baja');

                $folio = sprintf(
                    'BAJA-%s-%02d',
                    now('America/Cancun')->format('YmdHisv'),
                    ((int) ($ultimoId ?? 0)) + 1
                );

                /*
                 * Registrar baja.
                 */
                $idBaja = DB::table('bajas')
                    ->insertGetId([
                        'folio' => $folio,
                        'numero_ticket' => trim($validated['numeroTicket']),

                        'id_equipo' =>
                            $this->equipoId,

                        'id_motivo_baja' =>
                            (int) $idMotivoOtro,

                        'motivo_baja' =>
                            trim(
                                (string) $validated['motivoBaja']
                            ),

                        'id_estado_baja' =>
                            (int) $idEstadoCompletada,

                        'fecha_baja' =>
                            $validated['fechaBaja'],

                        'comentarios' =>
                            trim(
                                (string) (
                                    $validated['comentariosBaja'] ?? ''
                                )
                            ) !== ''
                                ? trim(
                                    (string) (
                                        $validated['comentariosBaja'] ?? ''
                                    )
                                )
                                : null,

                        'realizado_por' =>
                            (int) $usuarioId,

                        'autoriza' =>
                            trim(
                                (string) $validated['autoriza']
                            ),

                        'ruta_pdf' => null,

                        'firmas' => json_encode(
                            $this->nombresFirmas(),
                            JSON_UNESCAPED_UNICODE
                        ),

                        'fecha_registro' => now(),
                    ], 'id_baja');

                /*
                 * Fotografías de la baja.
                 */
                $fotografias = [
                    'foto_frontal' =>
                        $this->fotoFrontal,

                    'foto_trasera' =>
                        $this->fotoTrasera,

                    'foto_lateral_1' =>
                        $this->fotoLateral1,

                    'foto_lateral_2' =>
                        $this->fotoLateral2,
                ];

                $rutas = [
                    'id_baja' => $idBaja,
                    'foto_frontal' => null,
                    'foto_trasera' => null,
                    'foto_lateral_1' => null,
                    'foto_lateral_2' => null,
                ];

                $hayFotografias = false;

                foreach ($fotografias as $columna => $foto) {
                    if ($foto === null) {
                        continue;
                    }

                    $ruta = $foto->store(
                        'bajas/'.$idBaja,
                        'local'
                    );

                    if (
                        !is_string($ruta) ||
                        $ruta === ''
                    ) {
                        throw new \RuntimeException(
                            'No fue posible guardar la fotografía: '
                            .$columna
                        );
                    }

                    $archivosGuardados[] = $ruta;
                    $rutas[$columna] = $ruta;
                    $hayFotografias = true;
                }

                if ($hayFotografias) {
                    DB::table('baja_evidencias')
                        ->insert($rutas);
                }

                /*
                 * Actualizar el estado del equipo.
                 */
                DB::table('equipos')
                    ->where(
                        'id_equipo',
                        $this->equipoId
                    )
                    ->update([
                        'id_estado_activo' =>
                            (int) $idEstadoEquipoBaja,
                    ]);

                /*
                 * Cerrar asignaciones activas.
                 */
                DB::table('asignaciones')
                    ->where(
                        'id_equipo',
                        $this->equipoId
                    )
                    ->whereNull('fecha_fin')
                    ->update([
                        'fecha_fin' => now(),
                    ]);

                /*
                 * Crear el PDF antes de confirmar
                 * la transacción.
                 */
                $acta = app(
                    ActaBajaIndividualService::class
                )->generar(
                    (int) $idBaja,
                    $folio,
                    $this->equipo,
                    $validated,
                    $this->nombresFirmas(),
                    $rutas,
                    (int) $usuarioId
                );

                $reporteId = $acta['reporte_id'];
                $rutaPdf = $acta['ruta_pdf'];

                /*
                 * Guardar la acción en bitácora.
                 */
                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' =>
                            (int) $usuarioId,

                        'accion' =>
                            'EQUIPO_DADO_DE_BAJA',

                        'modulo' =>
                            'EQUIPOS',

                        'descripcion' => sprintf(
                            'Se dio de baja el equipo %s. Folio: %s. Motivo: %s',
                            $equipo->codigo_inventario
                                ?: 'Sin código',
                            $folio,
                            trim(
                                (string) $validated['motivoBaja']
                            )
                        ),

                        'fecha_hora' => now(),
                    ]);
            });

        } catch (Throwable $exception) {
            if ($archivosGuardados !== []) {
                Storage::disk('local')
                    ->delete($archivosGuardados);
            }

            if ($rutaPdf) {
                Storage::disk('local')
                    ->delete($rutaPdf);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            report($exception);

            $this->addError(
                'motivoBaja',
                'No se completó la baja ni se generó el PDF. Consulta storage/logs/laravel.log.'
            );

            return;
        }

        /*
         * Actualizar notificaciones y caché.
         * Una falla aquí no elimina la baja guardada.
         */
        try {
            $cache = app(
                SystemCacheService::class
            );

            $cache->refreshModule('dashboard');
            $cache->refreshModule('equipos');

            app(
                \App\Services\EquiposRecibidosService::class
            )->atenderPorBaja(
                (int) $this->equipoId
            );

            Cache::forget(
                'equipos.edit.base.'.$this->equipoId
            );

            Cache::forever(
                'equipos.index.version',
                (int) Cache::get(
                    'equipos.index.version',
                    1
                ) + 1
            );

            $notificaciones = app(
                \App\Services\NotificacionService::class
            );

            $notificaciones->notificar(
                'equipos',
                'Equipo dado de baja',
                $notificaciones->nombreActor()
                    .' dio de baja el equipo '
                    .$notificaciones->etiquetaEquipo(
                        $this->equipoId
                    )
                    .'.',
                route(
                    'equipos.show',
                    $this->equipoId,
                    false
                )
            );

            $this->dispatch(
                'notificaciones-actualizadas'
            );

        } catch (Throwable $exception) {
            report($exception);
        }

        /*
         * Avanzar si hay más equipos seleccionados.
         */
        if ($this->enCola()) {
            $this->hechos[] = $this->equipoId;

            return $this->avanzarCola(
                (
                    $this->equipo['codigo_inventario']
                        ?: 'El equipo'
                ).' tiene su acta PDF.',
                (int) $reporteId
            );
        }

        /*
         * Baja individual sin cola:
         * regresar a Inventario.
         */
        session()->flash(
            'descargar_reporte',
            $reporteId
        );

        session()->flash('toast', [
            'type' => 'success',
            'message' =>
                'Se registró la baja y su PDF individual está disponible en Reportes.',
        ]);

        return $this->redirectRoute(
            'equipos.index',
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REGLAS Y UTILIDADES
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

    private function formatearFecha(mixed $fecha): string
    {
        if ($fecha === null || $fecha === '') {
            return '—';
        }

        try {
            return Carbon::parse($fecha)
                ->format('d/m/Y');

        } catch (Throwable) {
            return (string) $fecha;
        }
    }

    public function render()
    {
        return view('livewire.equipo-baja');
    }
}
