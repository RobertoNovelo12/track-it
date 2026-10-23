<?php

namespace App\Livewire;

use App\Services\SystemCacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

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

    /*
    |--------------------------------------------------------------------------
    | Baja en lote (?cola=1,2,3 desde Inventario)
    |--------------------------------------------------------------------------
    |
    | Al confirmar una baja se carga el siguiente equipo de la cola sin
    | volver a Inventario. Se conservan motivo, fecha y quién autoriza;
    | fotos y comentarios son de cada equipo. Al terminar se abre el acta
    | de baja múltiple con todos los equipos de la cola.
    |
    */
    public const MAXIMO_COLA = 10;

    #[Locked]
    public array $cola = [];

    #[Locked]
    public array $pendientes = [];

    #[Locked]
    public array $hechos = [];

    #[Locked]
    public array $codigosCola = [];

    /** Tipos de equipo del lote (aviso si son distintos). */
    #[Locked]
    public array $tiposCola = [];

    /*
     * El parámetro se llama $lote (no $cola): Livewire asigna los
     * parámetros de mount a la propiedad pública del mismo nombre, y
     * $cola es un arreglo.
     */
    public function mount(int $equipoId, ?string $lote = null): void
    {
        $this->equipoId = $equipoId;
        $this->fechaBaja = now()->format('Y-m-d');
        $this->prepararCola($lote);
        $this->cargarEquipo();
    }

    /**
     * Ids de la cola: enteros, sin repetir, máximo 10 y con el equipo actual.
     */
    protected function prepararCola(?string $cola): void
    {
        $ids = collect(explode(',', (string) $cola))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(self::MAXIMO_COLA)
            ->values()
            ->all();

        if (count($ids) < 2 || ! in_array($this->equipoId, $ids, true)) {
            return;
        }

        $this->cola = $ids;

        $this->codigosCola = DB::table('equipos')
            ->whereIn('id_equipo', $ids)
            ->pluck('codigo_inventario', 'id_equipo')
            ->map(fn ($codigo) => (string) ($codigo ?: 'Sin código'))
            ->all();

        $this->tiposCola = DB::table('equipos as e')
            ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->whereIn('e.id_equipo', $ids)
            ->pluck('te.nombre')
            ->map(fn ($tipo) => (string) ($tipo ?: 'Sin tipo'))
            ->unique()
            ->values()
            ->all();

        // Los que ya estaban dados de baja no entran a la cola de bajas.
        $yaDeBaja = $this->idsDadosDeBaja($ids);

        $this->pendientes = array_values(array_diff($ids, $yaDeBaja));
    }

    protected function idsDadosDeBaja(array $ids): array
    {
        return DB::table('equipos as e')
            ->leftJoin('estados_equipo as ee', 'ee.id_estado_equipo', '=', 'e.id_estado_activo')
            ->whereIn('e.id_equipo', $ids)
            ->where(function ($query): void {
                $query->where('ee.clave', 'BAJA')
                    ->orWhereExists(fn ($sub) => $sub->selectRaw('1')
                        ->from('bajas as b')
                        ->whereColumn('b.id_equipo', 'e.id_equipo'));
            })
            ->pluck('e.id_equipo')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function enCola(): bool
    {
        return $this->cola !== [];
    }

    /**
     * Pasa al siguiente equipo pendiente o, si ya no hay, abre el acta.
     */
    protected function avanzarCola(?string $avisoHecho)
    {
        $this->pendientes = array_values(array_filter(
            $this->pendientes,
            fn (int $id) => $id !== $this->equipoId
        ));

        // Por si otro usuario dio de baja alguno mientras tanto.
        $this->pendientes = array_values(array_diff(
            $this->pendientes,
            $this->idsDadosDeBaja($this->pendientes)
        ));

        if ($this->pendientes === []) {
            session()->flash('toast', [
                'type' => 'success',
                'message' => count($this->hechos) === 1
                    ? 'Se dio de baja 1 equipo. Ahora puedes generar el acta de baja múltiple.'
                    : 'Se dieron de baja ' . count($this->hechos) . ' equipos. Ahora puedes generar el acta de baja múltiple.',
            ]);

            return $this->redirectRoute(
                'equipos.baja-multiple',
                ['equipos' => implode(',', $this->cola)],
                navigate: true
            );
        }

        $this->equipoId = $this->pendientes[0];

        // Datos de cada equipo; motivo, fecha y quién autoriza se conservan.
        $this->reset('comentariosBaja', 'fotoFrontal', 'fotoTrasera', 'fotoLateral1', 'fotoLateral2');
        $this->resetValidation();
        $this->cargarEquipo();

        // La dirección refleja el equipo actual (recargar no pierde el lote).
        $url = route('equipos.baja', ['equipo' => $this->equipoId, 'cola' => implode(',', $this->cola)]);
        $this->js('history.replaceState(history.state, "", ' . json_encode($url, JSON_UNESCAPED_SLASHES) . '); window.scrollTo({ top: 0, behavior: "smooth" });');

        $siguiente = 'Sigue ' . ($this->equipo['codigo_inventario'] ?: 'el siguiente equipo') . '.';

        $this->dispatch('toast', type: 'success', message: trim(($avisoHecho ?? '') . ' ' . $siguiente));

        return null;
    }

    /**
     * Saltar el equipo actual sin darlo de baja (queda fuera del lote).
     */
    public function omitir()
    {
        if (! $this->enCola()) {
            return null;
        }

        $codigo = $this->equipo['codigo_inventario'] ?? 'El equipo';

        $this->cola = array_values(array_filter($this->cola, fn (int $id) => $id !== $this->equipoId));

        if ($this->hechos === [] && count($this->pendientes) <= 1) {
            session()->flash('toast', ['type' => 'info', 'message' => 'No se dio de baja ningún equipo.']);

            return $this->redirectRoute('equipos.index', navigate: true);
        }

        return $this->avanzarCola($codigo . ' se omitió.');
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
            ->where('e.id_equipo', $this->equipoId)
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
            ->where('id_equipo', $this->equipoId)
            ->exists();

        abort_if(
            $bajaExistente || $equipo->estado_clave === 'BAJA',
            404
        );

        $this->equipo = [
            'id_equipo' => (int) $equipo->id_equipo,
            'codigo_inventario' => (string) (
                $equipo->codigo_inventario ?? ''
            ),
            'nombre_equipo' => (string) (
                $equipo->nombre_equipo ?? ''
            ),
            'numero_serie' => (string) (
                $equipo->numero_serie ?? ''
            ),
            'numero_factura' => (string) (
                $equipo->numero_factura ?? ''
            ),
            'tipo_equipo' => (string) (
                $equipo->tipo_equipo ?? ''
            ),
            'marca' => (string) (
                $equipo->marca ?? ''
            ),
            'modelo' => (string) (
                $equipo->modelo ?? ''
            ),
            'estado' => (string) (
                $equipo->estado ?? ''
            ),
            'proveedor' => (string) (
                $equipo->proveedor ?? ''
            ),
            'fecha_compra' => $this->formatearFecha(
                $equipo->fecha_compra ?? null
            ),
            'fecha_fin_garantia' => $this->formatearFecha(
                $equipo->fecha_fin_garantia ?? null
            ),
            'fecha_registro' => $this->formatearFecha(
                $equipo->fecha_registro ?? null
            ),
        ];
    }

    public function updatedFotoFrontal(): void
    {
        $this->validateOnly('fotoFrontal', [
            'fotoFrontal' => $this->photoRules(),
        ]);
    }

    public function updatedFotoTrasera(): void
    {
        $this->validateOnly('fotoTrasera', [
            'fotoTrasera' => $this->photoRules(),
        ]);
    }

    public function updatedFotoLateral1(): void
    {
        $this->validateOnly('fotoLateral1', [
            'fotoLateral1' => $this->photoRules(),
        ]);
    }

    public function updatedFotoLateral2(): void
    {
        $this->validateOnly('fotoLateral2', [
            'fotoLateral2' => $this->photoRules(),
        ]);
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

    public function confirmar()
    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

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
            'fotoFrontal' => $this->photoRules(),
            'fotoTrasera' => $this->photoRules(),
            'fotoLateral1' => $this->photoRules(),
            'fotoLateral2' => $this->photoRules(),
        ], [
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

        try {
            DB::transaction(function () use (
                $validated,
                $usuarioId,
                &$archivosGuardados
            ): void {
                $equipo = DB::table('equipos')
                    ->where('id_equipo', $this->equipoId)
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
                    ->where('id_equipo', $this->equipoId)
                    ->exists();

                if ($bajaExistente) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'Este equipo ya se encuentra dado de baja.',
                    ]);
                }

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

                $idEstadoBajaCompletada = DB::table(
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

                if ($idEstadoBajaCompletada === null) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'No se encontró el estado COMPLETADA para la baja.',
                    ]);
                }

                $idEstadoEquipoBaja = DB::table('estados_equipo')
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
                    (int) $equipo->id_estado_activo ===
                    (int) $idEstadoEquipoBaja
                ) {
                    throw ValidationException::withMessages([
                        'motivoBaja' =>
                            'El equipo ya tiene estado BAJA.',
                    ]);
                }

                $ultimoId = DB::table('bajas')
                    ->orderByDesc('id_baja')
                    ->lockForUpdate()
                    ->value('id_baja');

                $consecutivo = ((int) ($ultimoId ?? 0)) + 1;

                $folio = sprintf(
                    'BAJA-%s-%02d',
                    now()->format('YmdHisv'),
                    $consecutivo
                );

                $idBaja = DB::table('bajas')
                    ->insertGetId([
                        'folio' => $folio,
                        'id_equipo' => $this->equipoId,
                        'id_motivo_baja' => (int) $idMotivoOtro,
                        'motivo_baja' => trim(
                            (string) $validated['motivoBaja']
                        ),
                        'id_estado_baja' =>
                            (int) $idEstadoBajaCompletada,
                        'fecha_baja' => $validated['fechaBaja'],
                        'comentarios' => trim((string) (
                            $validated['comentariosBaja'] ?? ''
                        )) !== ''
                            ? trim((string) (
                                $validated['comentariosBaja'] ?? ''
                            ))
                            : null,
                        'realizado_por' => (int) $usuarioId,
                        'autoriza' => trim(
                            (string) $validated['autoriza']
                        ),
                        'ruta_pdf' => null,
                        'fecha_registro' => now(),
                    ], 'id_baja');

                $fotografias = [
                    'foto_frontal' => $this->fotoFrontal,
                    'foto_trasera' => $this->fotoTrasera,
                    'foto_lateral_1' => $this->fotoLateral1,
                    'foto_lateral_2' => $this->fotoLateral2,
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
                        'bajas/' . $idBaja,
                        'local'
                    );

                    if (!is_string($ruta) || $ruta === '') {
                        throw new \RuntimeException(
                            'No fue posible guardar la fotografía: ' .
                            $columna
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

                DB::table('equipos')
                    ->where('id_equipo', $this->equipoId)
                    ->update([
                        'id_estado_activo' =>
                            (int) $idEstadoEquipoBaja,
                    ]);

                DB::table('asignaciones')
                    ->where('id_equipo', $this->equipoId)
                    ->whereNull('fecha_fin')
                    ->update([
                        'fecha_fin' => now(),
                    ]);

                DB::table('bitacora_auditoria')
                    ->insert([
                        'id_usuario' => (int) $usuarioId,
                        'accion' => 'EQUIPO_DADO_DE_BAJA',
                        'modulo' => 'EQUIPOS',
                        'descripcion' => sprintf(
                            'Se dio de baja el equipo %s. Folio: %s. Motivo: %s',
                            $equipo->codigo_inventario ?: 'Sin código',
                            $folio,
                            trim((string) $validated['motivoBaja'])
                        ),
                        'fecha_hora' => now(),
                    ]);
            });
        } catch (Throwable $exception) {
            if ($archivosGuardados !== []) {
                Storage::disk('local')
                    ->delete($archivosGuardados);
            }

            throw $exception;
        }

        /*
         * Invalidar estadísticas y listados solo después
         * de una baja confirmada exitosamente.
         */
        $systemCache = app(SystemCacheService::class);

        $systemCache->refreshModule('dashboard');
        $systemCache->refreshModule('equipos');

        // Equipos recibidos desde el teléfono: la baja lo saca de las bandejas.
        app(\App\Services\EquiposRecibidosService::class)->atenderPorBaja((int) $this->equipoId);

        Cache::forget('equipos.edit.base.' . $this->equipoId);

        $version = (int) Cache::get(
            'equipos.index.version',
            1
        );

        Cache::forever(
            'equipos.index.version',
            $version + 1
        );

        $notificaciones = app(\App\Services\NotificacionService::class);

        $notificaciones->notificar(
            'equipos',
            'Equipo dado de baja',
            $notificaciones->nombreActor()
                . ' dio de baja el equipo '
                . $notificaciones->etiquetaEquipo($this->equipoId)
                . '.',
            route('equipos.show', $this->equipoId, false)
        );

        $this->dispatch('notificaciones-actualizadas');

        // Baja en lote: siguiente equipo sin volver a Inventario.
        if ($this->enCola()) {
            $this->hechos[] = $this->equipoId;

            return $this->avanzarCola(
                ($this->equipo['codigo_inventario'] ?: 'El equipo') . ' se dio de baja.'
            );
        }

        session()->flash(
            'success',
            'El equipo fue dado de baja correctamente.'
        );

        return $this->redirectRoute(
            'equipos.index',
            navigate: true
        );
    }

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
