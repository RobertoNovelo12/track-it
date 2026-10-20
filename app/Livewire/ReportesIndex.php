<?php

namespace App\Livewire;

use App\Exports\ReporteInventarioExport;

use App\Models\CatalogoValor;

use App\Models\Marca;

use App\Models\Modelo;

use App\Models\ReporteGenerado;

use App\Models\TipoEquipo;

use App\Services\ReporteActasService;

use App\Services\ReporteAltasActaService;

use App\Services\ReporteBajasActaService;

use App\Services\ReporteDataService;

use App\Services\ReporteMantenimientoActaService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use Livewire\Component;

use Livewire\WithPagination;

use Maatwebsite\Excel\Excel as ExcelWriter;

use Maatwebsite\Excel\Facades\Excel;

use Throwable;

class ReportesIndex extends Component

{
    // Registros por página de Ajustes › Preferencias.
    use \App\Livewire\Concerns\UsaRegistrosPorPagina;


    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $tipoReporte = 'altas';

    public array $tiposReporte = ['altas'];

    public string $busquedaActivo = '';

    public string $tipoEquipo = '';

    public string $estado = '';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public string $modelo = '';

    public string $marca = '';

    public int $perPage = 10;

    public string $sort = 'desc';

    public array $previewRecords = [];

    public array $previewDocument = [];

    public int $previewTotal = 0;

    public bool $previewReady = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public array $sugerenciasEquipos = [];
    public bool $sugerenciasHayMas = false;
    public int $sugerenciasPagina = 0;
    public string $sugerenciasTermino = '';
    public string $sugerenciasTipo = '';

    /** Consulta ligera para el selector. Solo el primer lote se cachea. */
    public function obtenerSugerenciasRapidas(string $tipo, string $texto = '', int $pagina = 1): array
    {
        $tipo = app(ReporteDataService::class)->normalizarTipo($tipo);
        $texto = trim($texto);
        $pagina = max(1, $pagina);

        if ($texto !== '' || $pagina > 1) {
            return app(ReporteDataService::class)->sugerenciasEquipos($tipo, $texto, $pagina, 10);
        }

        return Cache::remember(
            'reportes:selector:v1:' . $tipo,
            now()->addSeconds(90),
            fn (): array => app(ReporteDataService::class)->sugerenciasEquipos($tipo, '', 1, 10)
        );
    }

    /** Obtiene el primer lote o el siguiente sin traer todo el inventario. */
    public function buscarSugerencias(
        string $texto = '',
        string $tipo = 'altas',
        bool $reiniciar = true
    ): void {
        $tipo = app(ReporteDataService::class)->normalizarTipo($tipo);
        $texto = trim($texto);

        if ($reiniciar || $texto !== $this->sugerenciasTermino || $tipo !== $this->sugerenciasTipo) {
            $this->sugerenciasEquipos = [];
            $this->sugerenciasPagina = 0;
            $this->sugerenciasHayMas = false;
            $this->sugerenciasTermino = $texto;
            $this->sugerenciasTipo = $tipo;
        }

        if ($this->sugerenciasPagina > 0 && !$this->sugerenciasHayMas) {
            return;
        }

        $pagina = $this->sugerenciasPagina + 1;
        $resultado = app(ReporteDataService::class)
            ->sugerenciasEquipos($tipo, $texto, $pagina, 10);

        $this->sugerenciasEquipos = array_merge(
            $this->sugerenciasEquipos,
            $resultado['registros']
        );
        $this->sugerenciasPagina = $pagina;
        $this->sugerenciasHayMas = $resultado['hayMas'];
    }

    public function seleccionarSugerencia(string $codigo): void
    {
        $this->busquedaActivo = $codigo;
        $this->sugerenciasEquipos = [];
        $this->sugerenciasHayMas = false;
        $this->dispatch('reporte-sugerencia-seleccionada');
        $this->buscarAutomaticamente();
    }

    public function mount(): void

    {

        $this->vaciarVistaPrevia();

    }

    private function vaciarVistaPrevia(): void

    {

        $this->previewRecords = [];

        $this->previewDocument = [];

        $this->previewTotal = 0;

        $this->previewReady = false;

    }

    public function seleccionarTipo(

        string $tipo,

        ReporteDataService $reportes

    ): void {

        $tipo = $reportes->normalizarTipo($tipo);

        if (

            $this->tipoReporte === $tipo

            && $this->tiposReporte === [$tipo]

        ) {

            return;

        }

        $this->tipoReporte = $tipo;

        $this->tiposReporte = [$tipo];

        $this->busquedaActivo = '';

        $this->tipoEquipo = '';

        $this->estado = '';

        $this->fechaInicio = '';

        $this->fechaFin = '';

        $this->modelo = '';

        $this->marca = '';

        $this->successMessage = '';

        $this->errorMessage = '';

        $this->vaciarVistaPrevia();

        $this->resetValidation();

        $this->resetPage(pageName: 'historialPage');

        $this->dispatch('reportes-filtros-limpiados');

    }

    public function alternarTipo(

        string $tipo,

        ReporteDataService $reportes

    ): void {

        $this->seleccionarTipo($tipo, $reportes);

    }

    public function updatedTipoEquipo(): void

    {

        $this->modelo = '';

        $this->vaciarVistaPrevia();

    }

    public function updatedMarca(): void

    {

        $this->modelo = '';

        $this->vaciarVistaPrevia();

    }

    public function updatedTiposReporte(): void

    {

        $this->tipoReporte = $this->tiposReporte[0] ?? 'altas';

        $this->vaciarVistaPrevia();

    }

    public function updated(string $property): void

    {

        if (in_array($property, [

            'busquedaActivo',

            'tipoEquipo',

            'estado',

            'fechaInicio',

            'fechaFin',

            'modelo',

            'marca',

        ], true)) {

            $this->vaciarVistaPrevia();

        }

        if (in_array($property, ['perPage', 'sort'], true)) {

            $this->resetPage(pageName: 'historialPage');

        }

    }

    public function buscarAutomaticamente(): void

    {

        $this->tipoReporte = $this->tiposReporte[0] ?? 'altas';

        $hayFiltros = collect($this->filtros())

            ->contains(fn ($valor) => filled($valor));

        if (!$hayFiltros) {

            $this->vaciarVistaPrevia();

            $this->errorMessage = '';

            $this->dispatch('reportes-previa-lista');

            return;

        }

        $this->consultarDocumento();

    }

    public function aplicarFiltros(): void

    {

        $this->tipoReporte = $this->tiposReporte[0] ?? 'altas';

        $this->consultarDocumento();

    }

    private function consultarDocumento(): void

    {

        $this->validate(

            $this->rules(),

            $this->messages()

        );

        $this->errorMessage = '';

        $this->successMessage = '';

        $tipo = $this->tiposReporte[0] ?? 'altas';

        $filtros = $this->filtros();

        try {

            $documento = match ($tipo) {

                'altas' => app(

                    ReporteAltasActaService::class

                )->obtener($filtros, 1)->first(),

                'bajas' => app(

                    ReporteBajasActaService::class

                )->obtener($filtros, 1, true)->first(),

                'asignaciones', 'reasignaciones' => app(

                    ReporteActasService::class

                )->obtener($tipo, $filtros, 1)->first(),

                'mantenimientos' => app(

                    ReporteMantenimientoActaService::class

                )->obtener($filtros, 1)->first(),

                default => null,

            };

            $this->previewDocument = is_array($documento)

                ? $documento

                : (

                    $documento === null

                        ? []

                        : (array) $documento

                );

            $this->previewRecords = $documento

                ? [['encontrado' => true]]

                : [];

            $this->previewTotal = $documento ? 1 : 0;

            $this->previewReady = true;

        } catch (Throwable $exception) {

            report($exception);

            $this->vaciarVistaPrevia();

            $this->errorMessage =

                'No fue posible consultar la información. Revisa storage/logs/laravel.log.';

        }

        $this->dispatch('reportes-previa-lista');

    }

    public function limpiarFiltros(): void

    {

        $this->reset([

            'busquedaActivo',

            'tipoEquipo',

            'estado',

            'fechaInicio',

            'fechaFin',

            'modelo',

            'marca',

            'successMessage',

            'errorMessage',

        ]);

        $this->vaciarVistaPrevia();

        $this->resetValidation();

        $this->dispatch('reportes-filtros-limpiados');

    }

    public function generarPdf(ReporteDataService $reportes)

    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');


        $this->validate(

            $this->rules(),

            $this->messages()

        );

        $this->successMessage = '';

        $this->errorMessage = '';

        if (!class_exists(Pdf::class)) {

            $this->errorMessage =

                'Falta instalar el generador PDF.';

            return null;

        }

        $filtros = [

            ...$this->filtros(),

            'tipos_reporte' => $this->tiposReporte,

        ];

        $esUnSoloTipo = count($this->tiposReporte) === 1;

        $tipoSeleccionado = $this->tiposReporte[0] ?? 'altas';

        $esReporteAltas = $esUnSoloTipo

            && $tipoSeleccionado === 'altas';

        $esReporteBajas = $esUnSoloTipo

            && $tipoSeleccionado === 'bajas';

        $tipoActa = $esUnSoloTipo

            && in_array(

                $tipoSeleccionado,

                ['asignaciones', 'reasignaciones'],

                true

            )

                ? $tipoSeleccionado

                : null;

        $esReporteMantenimiento = $esUnSoloTipo

            && $tipoSeleccionado === 'mantenimientos';

        try {

            if ($esReporteBajas) {

                $datos = app(

                    ReporteBajasActaService::class

                )->obtener($filtros);

            } elseif ($tipoActa !== null) {

                $datos = app(

                    ReporteActasService::class

                )->obtener($tipoActa, $filtros);

            } elseif ($esReporteMantenimiento) {

                $datos = app(

                    ReporteMantenimientoActaService::class

                )->obtener($filtros);

            } elseif ($esReporteAltas) {

                $datos = app(

                    ReporteAltasActaService::class

                )->obtener($filtros);

            } else {

                $datos = $reportes->datosCombinados(

                    $this->tiposReporte,

                    $filtros

                );

            }

        } catch (Throwable $exception) {

            report($exception);

            $this->errorMessage =

                'No fue posible consultar los datos del reporte. Revisa storage/logs/laravel.log.';

            return null;

        }

        if ($datos->isEmpty()) {

            $this->errorMessage =

                'No hay registros que coincidan con los filtros seleccionados.';

            return null;

        }

        if ($esReporteBajas) {

            foreach ($datos as $baja) {

                $camposRequeridos = [

                    'folio' => data_get($baja, 'folio'),

                    'fecha' => data_get($baja, 'fecha'),

                    'equipo.codigo' => data_get($baja, 'equipo.codigo'),

                    'motivo' => data_get($baja, 'motivo'),

                    'autoriza' => data_get($baja, 'autoriza'),

                ];

                $faltantes = [];

                foreach ($camposRequeridos as $campo => $valor) {

                    if (blank($valor)) {

                        $faltantes[] = $campo;

                    }

                }

                if ($faltantes !== []) {

                    $identificador = data_get(

                        $baja,

                        'id',

                        'sin identificador'

                    );

                    $this->errorMessage = sprintf(

                        'La baja %s no tiene datos completos para generar el acta. Campos faltantes: %s.',

                        $identificador,

                        implode(', ', $faltantes)

                    );

                    return null;

                }

            }

        }

        $esMultiple = count($this->tiposReporte) > 1;

        $tipoGuardado = $esMultiple

            ? 'multiple'

            : $tipoSeleccionado;

        $titulo = $esMultiple

            ? 'Reporte combinado'

            : 'Reporte de ' . $reportes->etiqueta($tipoGuardado);

        $registro = null;

        $ruta = '';

        try {

            $nombre = sprintf(

                'reporte-%s-%s-%s.pdf',

                $tipoGuardado,

                now()->format('Ymd-His'),

                Str::lower(Str::random(6))

            );

            $ruta = 'reportes/' . $nombre;

            if ($esReporteBajas) {

                $pdf = Pdf::loadView(

                    'reportes.baja-acta-pdf',

                    [

                        'bajas' => $datos,

                        'filtros' => $filtros,

                        'generadoPor' => $this->nombreUsuario(),

                        'generadoEn' => now(),

                    ]

                )->setPaper('letter', 'portrait');

            } elseif ($tipoActa !== null) {

                $pdf = Pdf::loadView(

                    'reportes.actas-pdf',

                    [

                        'actas' => $datos,

                        'tipoActa' => $tipoActa,

                        'filtros' => $filtros,

                        'generadoPor' => $this->nombreUsuario(),

                        'generadoEn' => now(),

                    ]

                )->setPaper('letter', 'portrait');

            } elseif ($esReporteMantenimiento) {

                $pdf = Pdf::loadView(

                    'reportes.mantenimiento-pdf',

                    [

                        'mantenimientos' => $datos,

                        'filtros' => $filtros,

                        'generadoPor' => $this->nombreUsuario(),

                        'generadoEn' => now(),

                    ]

                )->setPaper('letter', 'portrait');

            } elseif ($esReporteAltas) {

                $pdf = Pdf::loadView(

                    'reportes.alta-pdf',

                    [

                        'altas' => $datos,

                        'filtros' => $filtros,

                        'generadoPor' => $this->nombreUsuario(),

                        'generadoEn' => now(),

                    ]

                )->setPaper('letter', 'portrait');

            } else {

                $pdf = Pdf::loadView(

                    'reportes.pdf',

                    [

                        'titulo' => $titulo,

                        'tipoReporte' => $tipoGuardado,

                        'datos' => $datos,

                        'filtros' => $filtros,

                        'generadoPor' => $this->nombreUsuario(),

                        'generadoEn' => now(),

                    ]

                )->setPaper('a4', 'landscape');

            }

            /* Comentario original de documentación. */

            $pdf->setOption([

                'enable_font_subsetting' => true,

                'enable_remote' => false,

                'enable_php' => false,

            ]);

            $guardado = Storage::disk('local')->put(

                $ruta,

                $pdf->output()

            );

            if (!$guardado) {

                throw new \RuntimeException(

                    'No se pudo guardar el archivo PDF.'

                );

            }

            $registro = ReporteGenerado::create([

                'id_usuario' => auth()->id(),

                'tipo_reporte' => $tipoGuardado,

                'formato' => 'PDF',

                'fecha_desde' => $this->fechaInicio ?: null,

                'fecha_hasta' => $this->fechaFin ?: null,

                'filtros' => $filtros,

                'total_registros' => $datos->count(),

                'nombre_archivo' => $nombre,

                'ruta_archivo' => $ruta,

            ]);

            $this->successMessage =

                'El PDF se generó y quedó guardado en el historial.';

            $this->descargarArchivo($registro);

            return null;

        } catch (Throwable $exception) {

            if ($ruta !== '') {

                Storage::disk('local')->delete($ruta);

            }

            $registro?->delete();

            report($exception);

            $this->errorMessage =

                'No fue posible generar el PDF. Revisa storage/logs/laravel.log.';

            return null;

        }

    }

    public function generarHojaCalculo(

        string $extension,

        ReporteDataService $reportes

    ) {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');


        $this->successMessage = '';

        $this->errorMessage = '';

        $extension = strtolower(trim($extension));

        $formatos = [

            'xlsx' => [

                'formato' => 'XLSX',

                'etiqueta' => 'Excel',

                'writer' => ExcelWriter::XLSX,

                'mime' =>

                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            ],

            'csv' => [

                'formato' => 'CSV',

                'etiqueta' => 'CSV',

                'writer' => ExcelWriter::CSV,

                'mime' => 'text/csv; charset=UTF-8',

            ],

        ];

        if (!isset($formatos[$extension])) {

            $this->errorMessage =

                'El formato solicitado no es válido.';

            return null;

        }

        $this->validate(

            $this->rules(),

            $this->messages()

        );

        $configuracion = $formatos[$extension];

        $filtros = [

            ...$this->filtros(),

            'tipos_reporte' => $this->tiposReporte,

        ];

        try {

            $datos = $reportes->datosCombinados(

                $this->tiposReporte,

                $filtros

            );

        } catch (Throwable $exception) {

            report($exception);

            $this->errorMessage =

                'No fue posible consultar los datos para Excel o CSV.';

            return null;

        }

        if ($datos->isEmpty()) {

            $this->errorMessage =

                'No hay registros que coincidan con los filtros seleccionados.';

            return null;

        }

        $tipoGuardado = count($this->tiposReporte) > 1

            ? 'multiple'

            : $this->tiposReporte[0];

        $registro = null;

        $ruta = '';

        try {

            $nombre = sprintf(

                'reporte-%s-%s-%s.%s',

                $tipoGuardado,

                now()->format('Ymd-His'),

                Str::lower(Str::random(6)),

                $extension

            );

            $ruta = 'reportes/' . $nombre;

            $guardado = Excel::store(

                new ReporteInventarioExport($datos),

                $ruta,

                'local',

                $configuracion['writer']

            );

            if (!$guardado) {

                throw new \RuntimeException(

                    'No se pudo guardar la hoja de cálculo.'

                );

            }

            $registro = ReporteGenerado::create([

                'id_usuario' => auth()->id(),

                'tipo_reporte' => $tipoGuardado,

                'formato' => $configuracion['formato'],

                'fecha_desde' => $this->fechaInicio ?: null,

                'fecha_hasta' => $this->fechaFin ?: null,

                'filtros' => $filtros,

                'total_registros' => $datos->count(),

                'nombre_archivo' => $nombre,

                'ruta_archivo' => $ruta,

            ]);

            $this->successMessage = sprintf(

                'El archivo %s se generó y quedó guardado en el historial.',

                $configuracion['etiqueta']

            );

            $this->descargarArchivo($registro);

            return null;

        } catch (Throwable $exception) {

            if ($ruta !== '') {

                Storage::disk('local')->delete($ruta);

            }

            $registro?->delete();

            report($exception);

            $this->errorMessage = sprintf(

                'No fue posible generar el archivo %s.',

                $configuracion['etiqueta']

            );

            return null;

        }

    }

    /* Comentario original de documentación. */

    private function descargarArchivo(ReporteGenerado $registro): void

    {

        $url = route('reportes.download', $registro, false);

        app(\App\Services\NotificacionService::class)->notificar(
            'reportes',
            'Reporte disponible',
            'Tu reporte '
                . ($registro->nombre_archivo ?: $registro->tipo_reporte)
                . ' ('
                . $registro->formato
                . ') está listo para descargar.',
            $url,
            [(int) $registro->id_usuario],
            incluirActor: true
        );

        $this->dispatch('notificaciones-actualizadas');

        $this->js(

            '(() => {'

            . 'const a = document.createElement("a");'

            . 'a.href = ' . json_encode($url, JSON_UNESCAPED_SLASHES) . ';'

            . 'a.download = "";'

            . 'a.style.display = "none";'

            . 'document.body.appendChild(a);'

            . 'a.click();'

            . 'setTimeout(() => a.remove(), 1000);'

            . '})()'

        );

    }

    public function eliminarReporte(int $reporteId): void

    {
        // Permiso por rol (ver AppServiceProvider).
        \Illuminate\Support\Facades\Gate::authorize('gestionar');


        $reporte = ReporteGenerado::findOrFail($reporteId);

        if ($reporte->ruta_archivo) {

            Storage::disk('local')->delete(

                $reporte->ruta_archivo

            );

        }

        $reporte->delete();

        $this->successMessage =

            'El reporte fue eliminado del historial.';

        $this->resetPage(pageName: 'historialPage');

    }

    public function render()

    {

        $reportes = app(ReporteDataService::class);

        $perPage = in_array(

            $this->perPage,

            [10, 25, 50],

            true

        ) ? $this->perPage : 10;

        $sort = $this->sort === 'asc'

            ? 'asc'

            : 'desc';

        $historial = ReporteGenerado::query()

            ->with('usuario')

            ->orderBy('fecha_generacion', $sort)

            // paginate (no simplePaginate): la tabla común necesita el total
            // para mostrar la paginación numerada.
            ->paginate(

                $perPage,

                pageName: 'historialPage'

            );

        $tiposEquipo = Cache::remember(

            'reportes:v4:tipos-equipo',

            120,

            fn () => TipoEquipo::query()

                ->where('activo', true)

                ->orderBy('nombre')

                ->get(['id_tipo_equipo', 'nombre'])

                ->toArray()

        );

        $marcas = Cache::remember(

            'reportes:v4:marcas',

            120,

            fn () => Marca::query()

                ->where('activo', true)

                ->orderBy('nombre')

                ->get(['id_marca', 'nombre'])

                ->toArray()

        );

        $claveModelos = sprintf(

            'reportes:v4:modelos:%s:%s',

            $this->tipoEquipo ?: 'todos',

            $this->marca ?: 'todas'

        );

        $modelos = Cache::remember(

            $claveModelos,

            120,

            fn () => Modelo::query()

                ->where('activo', true)

                ->when(

                    $this->marca !== '',

                    fn ($query) => $query->where(

                        'id_marca',

                        (int) $this->marca

                    )

                )

                ->when(

                    $this->tipoEquipo !== '',

                    fn ($query) => $query->where(

                        'id_tipo_equipo',

                        (int) $this->tipoEquipo

                    )

                )

                ->orderBy('nombre')

                ->get(['id_modelo', 'nombre'])

                ->toArray()

        );

        // Lee solo datos ya cacheados: nunca consulta PostgreSQL durante render.
        $listasIniciales = [];
        foreach (['altas', 'bajas', 'asignaciones', 'reasignaciones', 'mantenimientos'] as $tipoLista) {
            $lote = Cache::get('reportes:selector:v1:' . $tipoLista);
            if (is_array($lote) && isset($lote['registros'])) {
                $listasIniciales[$tipoLista] = $lote;
            }
        }

        return view('livewire.reportes-index', [

            'listasIniciales' => $listasIniciales,
            'historial' => $historial,

            'tiposEquipo' => $tiposEquipo,

            'marcas' => $marcas,

            'modelos' => $modelos,

            'estados' => $this->estadosDisponibles(),

            'tipoEtiqueta' => $this->etiquetaSeleccion($reportes),

        ]);

    }

    private function etiquetaSeleccion(

        ReporteDataService $reportes

    ): string {

        if (count($this->tiposReporte) === 1) {

            return $reportes->etiqueta(

                $this->tiposReporte[0]

            );

        }

        return 'Combinado';

    }

    private function filtros(): array

    {

        return [

            'busqueda' => trim($this->busquedaActivo) ?: null,

            'tipo_equipo' => $this->tipoEquipo ?: null,

            'estado' => $this->estado ?: null,

            'fecha_inicio' => $this->fechaInicio ?: null,

            'fecha_fin' => $this->fechaFin ?: null,

            'modelo' => $this->modelo ?: null,

            'marca' => $this->marca ?: null,

        ];

    }

    private function estadosDisponibles(): array

    {

        if ($this->tipoReporte === 'asignaciones') {

            return [

                [

                    'id_valor' => 'activa',

                    'nombre' => 'Activa',

                ],

                [

                    'id_valor' => 'finalizada',

                    'nombre' => 'Finalizada',

                ],

            ];

        }

        [$tabla, $columna] = match ($this->tipoReporte) {

            'bajas' => [

                'bajas',

                'id_estado_baja',

            ],

            'mantenimientos' => [

                'mantenimientos',

                'id_estado_mantenimiento',

            ],

            default => [

                'equipos',

                'id_estado_activo',

            ],

        };

        return Cache::remember(

            'reportes:v4:estados:' . $this->tipoReporte,

            60,

            fn () => CatalogoValor::query()

                ->whereIn(

                    'id_valor',

                    DB::table($tabla)

                        ->select($columna)

                        ->whereNotNull($columna)

                )

                ->orderBy('orden')

                ->orderBy('nombre')

                ->get(['id_valor', 'nombre'])

                ->toArray()

        );

    }

    private function nombreUsuario(): string

    {

        $usuario = auth()->user();

        return trim(

            implode(' ', array_filter([

                $usuario?->nombres ?? $usuario?->name,

                $usuario?->apellido_paterno,

                $usuario?->apellido_materno,

            ]))

        ) ?: 'Usuario del sistema';

    }

    private function rules(): array

    {

        return [

            'tipoEquipo' => ['nullable'],

            'estado' => ['nullable'],

            'fechaInicio' => [

                'nullable',

                'date',

            ],

            'fechaFin' => [

                'nullable',

                'date',

                'after_or_equal:fechaInicio',

            ],

            'modelo' => ['nullable'],

            'marca' => ['nullable'],

        ];

    }

    private function messages(): array

    {

        return [

            'fechaInicio.date' =>

                'La fecha inicial no es válida.',

            'fechaFin.date' =>

                'La fecha final no es válida.',

            'fechaFin.after_or_equal' =>

                'La fecha final debe ser igual o posterior a la fecha inicial.',

        ];

    }

}
