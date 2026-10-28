<?php

namespace App\Livewire;

use App\Models\Departamento;
use App\Models\ReporteGenerado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class EquipoBajaMultiple extends Component
{
    use \App\Livewire\Concerns\RecogeFirmasBaja;
    use \App\Livewire\Concerns\FiltraEquipos;
    use WithFileUploads, WithPagination;

    public ?string $tipoActivo = null;
    public ?string $nombreEquipo = null;
    public ?string $marca = null;
    public ?string $modelo = null;
    public ?string $numeroSerie = null;
    public ?string $numeroFactura = null;
    public ?string $estado = null;
    public ?string $area = null;
    public ?string $situacion = null;
    public ?string $serviceTag = null;
    public ?string $direccionIp = null;
    public array $camposTipo = [];

    #[Locked]
    public bool $detalles = false;

    #[Locked]
    public ?int $reporteId = null;

    #[Locked]
    public bool $desdeInventario = false;

    #[Locked]
    public string $modo = '';

    public array $seleccionados = [];
    public string $buscar = '';
    public int $perPage = 10;
    // Por defecto lo más reciente primero (último equipo registrado arriba).
    public string $sort = 'desc';
    public string $sortField = 'recientes';
    public string $filtroHotel = '';
    public bool $mostrarFiltros = false;

    public string $solicitante = '';
    public string $hotelId = '';
    public string $departamentoId = '';
    public string $fechaBaja = '';
    public string $numeroTicket = '';
    public string $motivoBaja = '';
    public string $observaciones = '';
    public string $autoriza = '';
    public string $puesto = '';

    public $fotoFrontal = null;
    public $fotoTrasera = null;
    public $fotoLateral1 = null;
    public $fotoLateral2 = null;

    public function mount()
    {
        Gate::authorize('gestionar');

        $this->fechaBaja = now('America/Cancun')
            ->format('Y-m-d');

        return $this->cargarDesdeInventario(
            (string) request()->query('equipos', '')
        );
    }

    protected function cargarDesdeInventario(string $equipos)
    {
        $ids = collect(explode(',', $equipos))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(10)
            ->values()
            ->all();

        if ($ids === []) {
            return null;
        }

        $dadosDeBaja = $this->consulta()
            ->whereIn('e.id_equipo', $ids)
            ->pluck('e.id_equipo')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existentes = DB::table('equipos')
            ->whereIn('id_equipo', $ids)
            ->pluck('id_equipo')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($dadosDeBaja !== []) {
            session()->flash('toast', [
                'type' => 'warning',
                'message' => 'La selección contiene equipos que ya están dados de baja. Selecciona únicamente equipos activos.',
            ]);

            return $this->redirectRoute('equipos.index', navigate: true);
        }

        $activos = array_values(
            array_diff($existentes, $dadosDeBaja)
        );

        /*
         * Un solo equipo: abrir baja individual.
         */
        if (
            $activos !== [] &&
            count($existentes) === 1
        ) {
            return $this->redirectRoute(
                'equipos.baja',
                ['equipo' => $activos[0]],
                navigate: true
            );
        }

        /*
         * Varios equipos activos:
         * identificar la modalidad seleccionada.
         */
        if ($activos !== []) {
            $this->seleccionados = array_map(
                'strval',
                $existentes
            );

            $this->desdeInventario = true;
            $this->modo = 'elegir';

            return match (
                (string) request()->query('modo', '')
            ) {
                'lote' => $this->unaPorUna(),
                'conjunta' => $this->todasEnUnDocumento(),
                default => null,
            };
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | FILTROS
    |--------------------------------------------------------------------------
    */

    public function updatedTipoActivo(): void
    {
        $this->camposTipo = [];
        $this->resetPage();
    }

    public function updating(string $property): void
    {
        if (in_array(
            explode('.', $property)[0],
            [
                'tipoActivo',
                'nombreEquipo',
                'marca',
                'modelo',
                'numeroSerie',
                'numeroFactura',
                'estado',
                'area',
                'situacion',
                'serviceTag',
                'direccionIp',
                'camposTipo',
            ],
            true
        )) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(
            'tipoActivo',
            'nombreEquipo',
            'marca',
            'modelo',
            'numeroSerie',
            'numeroFactura',
            'estado',
            'area',
            'situacion',
            'serviceTag',
            'direccionIp',
            'camposTipo',
            'buscar',
            'filtroHotel'
        );

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTAS
    |--------------------------------------------------------------------------
    */

    protected function consulta()
    {
        return DB::table('equipos as e')
            ->join(
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
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
                'unidades_organizativas as u',
                'u.id_unidad',
                '=',
                'e.id_unidad'
            )
            ->where('ee.clave', 'BAJA')
            ->whereExists(
                fn ($q) => $q
                    ->selectRaw('1')
                    ->from('bajas as b')
                    ->whereColumn(
                        'b.id_equipo',
                        'e.id_equipo'
                    )
            )
            ->select(
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.id_unidad',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'u.nombre as hotel',
                'ee.nombre as estado'
            );
    }

    protected function consultaSeleccion()
    {
        return DB::table('equipos as e')
            ->leftJoin(
                'estados_equipo as ee',
                'ee.id_estado_equipo',
                '=',
                'e.id_estado_activo'
            )
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
                'unidades_organizativas as u',
                'u.id_unidad',
                '=',
                'e.id_unidad'
            )
            ->select(
                'e.id_equipo',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.id_unidad',
                'e.id_tipo_equipo',
                'te.nombre as tipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'u.nombre as hotel',
                'ee.nombre as estado'
            )
            ->selectRaw(
                "CASE
                    WHEN ee.clave = 'BAJA'
                    OR EXISTS (
                        SELECT 1
                        FROM bajas b
                        WHERE b.id_equipo = e.id_equipo
                    )
                    THEN 1
                    ELSE 0
                END AS es_baja"
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ORDENAMIENTO Y PAGINACIÓN
    |--------------------------------------------------------------------------
    */

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroHotel(): void
    {
        $this->resetPage();
    }

    public function updatedHotelId(): void
    {
        $this->departamentoId = '';
    }

    protected function columnasOrden(): array
    {
        return [
            'recientes' => 'e.id_equipo',
            'equipo' => 'e.nombre_equipo',
            'marca' => 'ma.nombre',
            'hotel' => 'u.nombre',
            'estado' => 'ee.nombre',
        ];
    }

    protected function cantidadPagina(int $cantidad): int
    {
        return in_array(
            $cantidad,
            [10, 25, 50, 100],
            true
        ) ? $cantidad : 10;
    }

    public function sortBy(string $campo): void
    {
        if (
            !array_key_exists(
                $campo,
                $this->columnasOrden()
            )
        ) {
            return;
        }

        $this->sort = (
            $this->sortField === $campo &&
            $this->sort === 'asc'
        ) ? 'desc' : 'asc';

        $this->sortField = $campo;
        $this->resetPage();
    }

    public function updatedSeleccionados(): void
    {
        $this->seleccionados = array_values(
            array_unique($this->seleccionados)
        );

        if (count($this->seleccionados) > 10) {
            $this->seleccionados = array_slice(
                $this->seleccionados,
                0,
                10
            );

            $this->addError(
                'seleccionados',
                'Puedes seleccionar como máximo 10 equipos.'
            );
        } else {
            $this->resetValidation('seleccionados');
        }
    }

    protected function listado()
    {
        $query = $this->consulta();
        $this->applyFilters($query);

        return $query
            ->when(
                trim($this->buscar) !== '',
                function ($q) {
                    $texto = '%'
                        .mb_strtolower(trim($this->buscar))
                        .'%';

                    $q->where(
                        fn ($w) => $w
                            ->whereRaw(
                                'LOWER(e.codigo_inventario) LIKE ?',
                                [$texto]
                            )
                            ->orWhereRaw(
                                'LOWER(e.nombre_equipo) LIKE ?',
                                [$texto]
                            )
                            ->orWhereRaw(
                                'LOWER(e.numero_serie) LIKE ?',
                                [$texto]
                            )
                    );
                }
            )
            ->when(
                $this->filtroHotel !== '',
                fn ($q) => $q->where(
                    'e.id_unidad',
                    $this->filtroHotel
                )
            )
            ->orderBy(
                $this->columnasOrden()[$this->sortField]
                    ?? 'e.nombre_equipo',
                $this->sort === 'desc' ? 'desc' : 'asc'
            )
            ->orderBy('e.id_equipo');
    }

    public function seleccionarPagina(): void
    {
        Gate::authorize('gestionar');

        $ids = $this->listado()
            ->paginate(
                $this->cantidadPagina($this->perPage)
            )
            ->pluck('id_equipo')
            ->map(fn ($id) => (string) $id)
            ->all();

        $this->seleccionados = array_values(
            array_intersect(
                $ids,
                $this->seleccionados
            ) !== []
                ? array_diff(
                    $this->seleccionados,
                    $ids
                )
                : array_unique(
                    array_merge(
                        $this->seleccionados,
                        $ids
                    )
                )
        );

        $this->updatedSeleccionados();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN DE EQUIPOS
    |--------------------------------------------------------------------------
    */

    protected function validarSeleccion()
    {
        $this->validate([
            'seleccionados' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],
            'seleccionados.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ], [
            'seleccionados.required' =>
                'Selecciona al menos un equipo.',
            'seleccionados.max' =>
                'Puedes seleccionar como máximo 10 equipos.',
        ]);

        $equipos = (
            $this->modo === 'conjunta'
                ? $this->consultaSeleccion()
                : $this->consulta()
        )
            ->whereIn(
                'e.id_equipo',
                $this->seleccionados
            )
            ->orderBy('e.codigo_inventario')
            ->get();

        if ($this->modo === 'conjunta' && $equipos->contains(fn ($equipo) => (int) $equipo->es_baja === 1)) {
            throw ValidationException::withMessages(['seleccionados' => 'La selección contiene equipos que ya están dados de baja.']);
        }

        if (
            $equipos->count() !==
            count($this->seleccionados)
        ) {
            throw ValidationException::withMessages([
                'seleccionados' =>
                    'La selección contiene equipos no disponibles para este tipo de acta.',
            ]);
        }

        return $equipos;
    }

    public function siguiente(): void
    {
        Gate::authorize('gestionar');

        $equipos = $this->validarSeleccion();

        if (
            $this->hotelId === '' &&
            $equipos->pluck('id_unidad')
                ->unique()
                ->count() === 1
        ) {
            $this->hotelId = (string)
                $equipos->first()->id_unidad;
        }

        $this->detalles = true;
    }

    /*
    |--------------------------------------------------------------------------
    | BAJA UNO POR UNO
    |--------------------------------------------------------------------------
    */

    public function unaPorUna()
    {
        Gate::authorize('gestionar');

        if ($this->modo !== 'elegir') {
            return null;
        }

        $ids = array_map(
            'intval',
            $this->seleccionados
        );

        $disponibles = $this->consultaSeleccion()
            ->whereIn('e.id_equipo', $ids)
            ->get()
            ->filter(
                fn ($equipo) =>
                    (int) $equipo->es_baja === 0
            )
            ->pluck('id_equipo')
            ->map(fn ($id) => (int) $id)
            ->all();

        $primero = collect($ids)->first(
            fn ($id) => in_array(
                $id,
                $disponibles,
                true
            )
        );

        if (!$primero) {
            return null;
        }

        return $this->redirectRoute(
            'equipos.baja',
            [
                'equipo' => (int) $primero,
                'cola' => implode(',', $ids),
            ],
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BAJA CONJUNTA
    |--------------------------------------------------------------------------
    */

    public function todasEnUnDocumento(): void
    {
        Gate::authorize('gestionar');

        if ($this->modo !== 'elegir') {
            return;
        }

        $this->modo = 'conjunta';
        $this->siguiente();
    }

    public function volver(): void
    {
        if ($this->modo === 'conjunta') {
            $this->modo = 'elegir';
            $this->detalles = false;
            return;
        }

        if ($this->desdeInventario) {
            return;
        }

        $this->detalles = false;
    }

    /*
    |--------------------------------------------------------------------------
    | FOTOGRAFÍAS
    |--------------------------------------------------------------------------
    */

    protected function reglasFoto(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
        ];
    }

    public function updatedFotoFrontal(): void
    {
        $this->validateOnly(
            'fotoFrontal',
            ['fotoFrontal' => $this->reglasFoto()],
            $this->mensajesValidacion()
        );
    }

    public function updatedFotoTrasera(): void
    {
        $this->validateOnly(
            'fotoTrasera',
            ['fotoTrasera' => $this->reglasFoto()],
            $this->mensajesValidacion()
        );
    }

    public function updatedFotoLateral1(): void
    {
        $this->validateOnly(
            'fotoLateral1',
            ['fotoLateral1' => $this->reglasFoto()],
            $this->mensajesValidacion()
        );
    }

    public function updatedFotoLateral2(): void
    {
        $this->validateOnly(
            'fotoLateral2',
            ['fotoLateral2' => $this->reglasFoto()],
            $this->mensajesValidacion()
        );
    }

    public function eliminarFoto(string $tipo): void
    {
        if (in_array(
            $tipo,
            [
                'fotoFrontal',
                'fotoTrasera',
                'fotoLateral1',
                'fotoLateral2',
            ],
            true
        )) {
            $this->{$tipo} = null;
            $this->resetValidation($tipo);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INFORMACIÓN DEL DOCUMENTO
    |--------------------------------------------------------------------------
    */

    protected function documento($equipos): array
    {
        $organizacion = app(
            \App\Services\OrganizacionService::class
        );

        return [
            'logo' => $organizacion->logoUrl(),
            'organizacion' => $organizacion->nombre(),
            'solicitante' => $this->solicitante,

            'hotel' => DB::table(
                'unidades_organizativas'
            )
                ->where(
                    'id_unidad',
                    $this->hotelId ?: 0
                )
                ->value('nombre') ?? '',

            'departamento' => DB::table(
                'departamentos'
            )
                ->where(
                    'id_departamento',
                    $this->departamentoId ?: 0
                )
                ->value('nombre') ?? '',

            'fecha' => $this->fechaBaja,
            'motivo' => $this->motivoBaja,
            'observaciones' => $this->observaciones,
            'autoriza' => $this->autoriza,
            'puesto' => $this->puesto,

            'equipos' => $equipos
                ->map(fn ($e) => (array) $e)
                ->all(),

            'folio' => 'Por asignar',
            'numero_ticket' => $this->numeroTicket !== '' ? 'GPRVM-'.$this->numeroTicket : 'Por capturar',
            'fotos' => [],
            'firmas' => $this->nombresFirmas(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR Y GENERAR PDF CONJUNTO
    |--------------------------------------------------------------------------
    */

    public function guardar()
    {
        Gate::authorize('gestionar');

        if ($this->reporteId !== null) {
            return;
        }

        $equipos = $this->validarSeleccion();

        $departamentos = Departamento::disponiblesParaUnidad(
            (int) $this->hotelId
        )
            ->pluck('id_departamento')
            ->all();

        $this->validate([
            'solicitante' => [
                'required', 'string', 'max:255'
            ],
            'hotelId' => [
                'required',
                'integer',
                Rule::exists(
                    'unidades_organizativas',
                    'id_unidad'
                )->where('activo', true),
            ],
            'departamentoId' => [
                'required',
                'integer',
                Rule::in($departamentos),
            ],
            'fechaBaja' => [
                'required', 'date_format:Y-m-d'
            ],
            'numeroTicket' => ['required', 'regex:/^[0-9]+$/', 'max:20'],
            'motivoBaja' => [
                'required', 'string', 'max:500'
            ],
            'observaciones' => [
                'nullable', 'string', 'max:500'
            ],
            'autoriza' => [
                'required', 'string', 'max:255'
            ],
            'puesto' => [
                'required', 'string', 'max:255'
            ],
            'fotoFrontal' => $this->reglasFoto(),
            'fotoTrasera' => $this->reglasFoto(),
            'fotoLateral1' => $this->reglasFoto(),
            'fotoLateral2' => $this->reglasFoto(),
            ...$this->reglasFirmas(),
        ], $this->mensajesValidacion());

        $documento = $this->documento($equipos);

        // Logo y fotos reducidos al tamaño en que se imprimen (más rápido en dompdf).
        $imagenesPdf = app(\App\Services\ImagenPdfService::class);

        $documento['logo'] = $imagenesPdf->logoDataUri();

        $documento['folio'] = 'BM-'
            .now('America/Cancun')->format('Ymd')
            .'-'
            .strtoupper(Str::random(10));

        $directorio = 'reportes/bajas-multiples/'
            .Str::uuid();

        try {
            $rutas = [];

            foreach ([
                'fotoFrontal' => 'Vista frontal',
                'fotoTrasera' => 'Vista trasera',
                'fotoLateral1' => 'Vista lateral 1',
                'fotoLateral2' => 'Vista lateral 2',
            ] as $campo => $titulo) {

                if (!$this->{$campo}) {
                    continue;
                }

                $ruta = $this->{$campo}->store(
                    $directorio,
                    'local'
                );

                if (!$ruta) {
                    throw new \RuntimeException(
                        'No fue posible guardar la fotografía.'
                    );
                }

                $rutas[$campo] = $ruta;

                $imagen = $imagenesPdf->dataUri($ruta, 900, 75);

                if ($imagen !== null) {
                    $documento['fotos'][$titulo] = $imagen;
                }
            }

            $nombre = $documento['folio'].'.pdf';
            $rutaPdf = $directorio.'/'.$nombre;

            $contenido = Pdf::loadView(
                'reportes.baja-multiple-pdf',
                compact('documento')
            )
                ->setPaper('letter')
                ->output();

            if (!Storage::disk('local')->put(
                $rutaPdf,
                $contenido
            )) {
                throw new \RuntimeException(
                    'No fue posible guardar el acta.'
                );
            }

            $idsDadosDeBaja = [];

            $registro = DB::transaction(
                function () use (
                    $documento,
                    $rutas,
                    $nombre,
                    $rutaPdf,
                    &$idsDadosDeBaja
                ) {
                    $seleccion = $this->validarSeleccion();

                    if ($this->modo === 'conjunta') {
                        $bajas = app(
                            \App\Services\BajaEquipoService::class
                        );

                        foreach ($seleccion as $equipo) {
                            if (
                                (int) ($equipo->es_baja ?? 0)
                                === 1
                            ) {
                                continue;
                            }

                            $bajas->darDeBaja(
                                (int) $equipo->id_equipo,
                                $this->motivoBaja,
                                $this->fechaBaja,
                                $this->observaciones,
                                $this->autoriza,
                                (int) auth()->id(),
                                $documento['folio'],
                                $this->nombresFirmas(),
                                $documento['numero_ticket']
                            );

                            $idsDadosDeBaja[] =
                                (int) $equipo->id_equipo;
                        }
                    }

                    $datos = $documento;
                    unset($datos['logo']);
                    $datos['fotos'] = $rutas;

                    return ReporteGenerado::create([
                        'id_usuario' => auth()->id(),
                        'tipo_reporte' => 'bajas',
                        'formato' => 'PDF',
                        'fecha_desde' => $this->fechaBaja,
                        'fecha_hasta' => $this->fechaBaja,
                        'filtros' => [
                            'baja_multiple' => $datos,
                        ],
                        'total_registros' => count(
                            $this->seleccionados
                        ),
                        'nombre_archivo' => $nombre,
                        'ruta_archivo' => $rutaPdf,
                    ]);
                }
            );

            $this->reporteId = (int)
                $registro->getKey();

        } catch (ValidationException $exception) {
            Storage::disk('local')
                ->deleteDirectory($directorio);

            throw $exception;

        } catch (Throwable $exception) {
            Storage::disk('local')
                ->deleteDirectory($directorio);

            report($exception);

            $this->addError(
                'guardar',
                'No fue posible completar la baja múltiple o generar el PDF. Consulta el registro de errores del sistema.'
            );

            return;
        }

        try {
            app(
                \App\Services\BajaEquipoService::class
            )->despuesDeBajas($idsDadosDeBaja);
        } catch (Throwable $exception) {
            report($exception);
        }

        session()->flash(
            'descargar_reporte',
            $this->reporteId
        );

        if ($this->modo === 'conjunta') {
            session()->flash('toast', [
                'type' => 'success',
                'message' =>
                    'Se dieron de baja los equipos y se generó el acta de baja múltiple.',
            ]);
        }

        return $this->redirectRoute('equipos.index');
    }

    /*
    |--------------------------------------------------------------------------
    | MENSAJES DE VALIDACIÓN
    |--------------------------------------------------------------------------
    */

    protected function mensajesValidacion(): array
    {
        return [
            'numeroTicket.regex' => 'Ingresa solo los números del tiket, sin el prefijo GPRVM-.',
            'required' =>
                'Este campo es obligatorio.',
            'integer' =>
                'Selecciona una opción válida.',
            'exists' =>
                'La opción seleccionada ya no está disponible.',
            'in' =>
                'Selecciona una opción válida para el hotel.',
            'date_format' =>
                'Ingresa una fecha válida.',
            'max.string' =>
                'Este campo no debe superar :max caracteres.',
            'image' =>
                'Selecciona una fotografía válida.',
            'mimes' =>
                'Utiliza una fotografía JPG, PNG o WEBP.',
            'max.file' =>
                'La fotografía no debe superar 5 MB.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA LIVEWIRE
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        Gate::authorize('gestionar');

        $elegidos = (
            $this->desdeInventario
                ? $this->consultaSeleccion()
                : $this->consulta()
        )
            ->whereIn(
                'e.id_equipo',
                array_slice(
                    $this->seleccionados,
                    0,
                    10
                )
            )
            ->orderBy('e.codigo_inventario')
            ->get();

        $tiposSeleccion = $elegidos
            ->pluck('tipo')
            ->map(fn ($tipo) => $tipo ?: 'Sin tipo')
            ->unique()
            ->values();

        $mostrarSeleccion = !$this->detalles && $this->modo !== 'elegir';
        $documento = $this->detalles ? $this->documento($elegidos) : [];

        foreach ([
            'fotoFrontal' => 'Vista frontal',
            'fotoTrasera' => 'Vista trasera',
            'fotoLateral1' => 'Vista lateral 1',
            'fotoLateral2' => 'Vista lateral 2',
        ] as $campo => $titulo) {
            if (
                $this->detalles && $this->{$campo} &&
                !$this->getErrorBag()->has($campo)
            ) {
                $documento['fotos'][$titulo] =
                    $this->{$campo}->temporaryUrl();
            }
        }

        return view(
            'livewire.equipo-baja-multiple',
            [
                'tiposActivo' => $mostrarSeleccion ? $this->getTiposActivo() : collect(),
                'marcas' => $mostrarSeleccion ? $this->getMarcas() : collect(),
                'modelos' => $mostrarSeleccion ? $this->getModelos() : collect(),
                'estados' => $mostrarSeleccion ? $this->getEstados() : collect(),
                'areas' => $mostrarSeleccion ? $this->getAreas() : collect(),

                'camposTipoFiltros' => $mostrarSeleccion && $this->tipoActivo
                    ? $this->getCamposFiltrables(
                        (int) $this->tipoActivo
                    )
                    : [],

                'situaciones' => collect(
                    EquiposIndex::SITUACIONES
                )->map(
                    fn ($label, $value) => [
                        'value' => $value,
                        'label' => $label,
                    ]
                )->values()->all(),

                'equipos' => $mostrarSeleccion ? $this->listado()->paginate(
                    $this->cantidadPagina($this->perPage)
                ) : null,

                'elegidos' => $elegidos,
                'documento' => $documento,
                'tiposSeleccion' => $tiposSeleccion,

                'activosSeleccion' => $elegidos
                    ->filter(
                        fn ($e) =>
                            (int) ($e->es_baja ?? 1) === 0
                    )
                    ->count(),

                'hoteles' => ($mostrarSeleccion || $this->detalles) ? DB::table(
                    'unidades_organizativas'
                )
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get([
                        'id_unidad',
                        'nombre',
                    ]) : collect(),

                'departamentos' => $this->detalles && $this->hotelId
                    ? Departamento::disponiblesParaUnidad(
                        (int) $this->hotelId
                    )
                        ->orderBy('nombre')
                        ->get()
                    : collect(),
            ]
        );
    }
}
