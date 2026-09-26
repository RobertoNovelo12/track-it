<?php
namespace App\Livewire;

use App\Exports\ReporteInventarioExport;
use App\Models\CatalogoValor;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\ReporteGenerado;
use App\Models\TipoEquipo;
use App\Services\ReporteDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ReportesIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $tipoReporte = 'general';

    public array $tiposReporte = ['general'];

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

    public int $previewTotal = 0;

    public bool $previewReady = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(
        ReporteDataService $reportes
    ): void {
        $this->actualizarVistaPrevia($reportes);
    }

    public function seleccionarTipo(
        string $tipo,
        ReporteDataService $reportes
    ): void {
        $this->tipoReporte  = $reportes->normalizarTipo($tipo);
        $this->tiposReporte = [$this->tipoReporte];

        $this->reset([
            'tipoEquipo',
            'estado',
            'fechaInicio',
            'fechaFin',
            'modelo',
            'marca',
            'successMessage',
            'errorMessage',
        ]);

        $this->resetPage(pageName: 'historialPage');
        $this->actualizarVistaPrevia($reportes);
    }

    public function alternarTipo(
        string $tipo,
        ReporteDataService $reportes
    ): void {
        $tipo = $reportes->normalizarTipo($tipo);

        if ($tipo === 'general') {
            $this->tiposReporte = ['general'];
        } elseif (in_array($tipo, $this->tiposReporte, true)) {
            $this->tiposReporte = array_values(
                array_diff($this->tiposReporte, [$tipo])
            );

            if ($this->tiposReporte === []) {
                $this->tiposReporte = ['general'];
            }
        } else {
            $sinGeneral = array_values(
                array_diff($this->tiposReporte, ['general'])
            );

            $this->tiposReporte = [ ...$sinGeneral, $tipo];
        }

        $this->tipoReporte = $this->tiposReporte[0];
        $this->estado      = '';

        $this->reset([
            'successMessage',
            'errorMessage',
        ]);

        $this->resetValidation();
        $this->previewReady = false;

    }

    public function updatedTipoEquipo(): void
    {
        $this->modelo       = '';
        $this->previewReady = false;
    }

    public function updatedTiposReporte(): void
    {
        $this->tipoReporte  = $this->tiposReporte[0] ?? 'general';
        $this->previewReady = false;
    }
    public function updatedMarca(): void
    {
        $this->modelo       = '';
        $this->previewReady = false;
    }

    public function updated(string $property): void
    {
        if (
            in_array(
                $property,
                [
                    'estado',
                    'fechaInicio',
                    'fechaFin',
                    'modelo',
                ],
                true
            )
        ) {
            $this->previewReady = false;
        }

        if (
            in_array(
                $property,
                ['perPage', 'sort'],
                true
            )
        ) {
            $this->resetPage(
                pageName: 'historialPage'
            );
        }
    }

    public function aplicarFiltros(
        ReporteDataService $reportes
    ): void {
        $this->tipoReporte = $this->tiposReporte[0] ?? 'general';

        $this->validate(
            $this->rules(),
            $this->messages()
        );

        $this->actualizarVistaPrevia($reportes);
    }

    public function limpiarFiltros(
        ReporteDataService $reportes
    ): void {
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

        $this->resetValidation();

        $this->actualizarVistaPrevia($reportes);

        $this->dispatch('reportes-filtros-limpiados');
    }

    public function generarPdf(
        ReporteDataService $reportes
    ) {
        $this->validate(
            $this->rules(),
            $this->messages()
        );

        $this->reset([
            'successMessage',
            'errorMessage',
        ]);

        if (! class_exists(Pdf::class)) {
            $this->errorMessage =
                'Falta instalar el generador PDF.';

            return null;
        }

        $filtros = [
             ...$this->filtros(),
            'tipos_reporte' => $this->tiposReporte,
        ];

        $datos = $reportes->datosCombinados(
            $this->tiposReporte,
            $filtros
        );

        if ($datos->isEmpty()) {
            $this->errorMessage =
                'No hay registros que coincidan con los filtros seleccionados.';

            return null;
        }

        $esMultiple = count($this->tiposReporte) > 1;

        $tipoGuardado = $esMultiple
            ? 'multiple'
            : $this->tiposReporte[0];

        $titulo = $esMultiple
            ? 'Reporte combinado'
            : 'Reporte de ' . $reportes->etiqueta($tipoGuardado);

        $registro = ReporteGenerado::create([
            'id_usuario'      => auth()->id(),
            'tipo_reporte'    => $tipoGuardado,
            'formato'         => 'PDF',
            'fecha_desde'     => $this->fechaInicio ?: null,
            'fecha_hasta'     => $this->fechaFin ?: null,
            'filtros'         => $filtros,
            'total_registros' => $datos->count(),
        ]);

        $nombre = sprintf(
            'reporte-%s-%s-%d.pdf',
            $tipoGuardado,
            now()->format('Ymd-His'),
            $registro->id_reporte
        );

        $ruta = 'reportes/' . $nombre;

        try {
            $pdf = Pdf::loadView('reportes.pdf', [
                'titulo'      => $titulo,
                'tipoReporte' => $tipoGuardado,
                'datos'       => $datos,
                'filtros'     => $filtros,
                'generadoPor' => $this->nombreUsuario(),
                'generadoEn'  => now(),
            ])->setPaper('a4', 'landscape');

            Storage::disk('local')->put(
                $ruta,
                $pdf->output()
            );

            $registro->update([
                'nombre_archivo' => $nombre,
                'ruta_archivo'   => $ruta,
            ]);

            $this->successMessage =
                'El PDF se generó y quedó guardado en el historial.';

            $this->actualizarVistaPrevia($reportes);

            return response()->streamDownload(
                function () use ($ruta): void {
                    echo Storage::disk('local')->get($ruta);
                },
                $nombre,
                ['Content-Type' => 'application/pdf']
            );
        } catch (Throwable $exception) {
            $registro->delete();
            report($exception);

            $this->errorMessage =
                'No fue posible generar el PDF. Revisa el registro de errores de Laravel.';

            return null;
        }
    }

    public function generarHojaCalculo(
        string $extension,
        ReporteDataService $reportes
    ) {
        $this->reset([
            'successMessage',
            'errorMessage',
        ]);

        $extension = strtolower(trim($extension));

        $formatos = [
            'xlsx' => [
                'formato'  => 'XLSX',
                'etiqueta' => 'Excel',
                'writer'   => ExcelWriter::XLSX,
                'mime'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'csv'  => [
                'formato'  => 'CSV',
                'etiqueta' => 'CSV',
                'writer'   => ExcelWriter::CSV,
                'mime'     => 'text/csv; charset=UTF-8',
            ],
        ];

        if (! isset($formatos[$extension])) {
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

        $datos = $reportes->datosCombinados(
            $this->tiposReporte,
            $filtros
        );

        if ($datos->isEmpty()) {
            $this->errorMessage =
                'No hay registros que coincidan con los filtros seleccionados.';

            return null;
        }

        $tipoGuardado = count($this->tiposReporte) > 1
            ? 'multiple'
            : $this->tiposReporte[0];

        $registro = null;
        $ruta     = '';

        try {
            $registro = ReporteGenerado::create([
                'id_usuario'      => auth()->id(),
                'tipo_reporte'    => $tipoGuardado,
                'formato'         => $configuracion['formato'],
                'fecha_desde'     => $this->fechaInicio ?: null,
                'fecha_hasta'     => $this->fechaFin ?: null,
                'filtros'         => $filtros,
                'total_registros' => $datos->count(),
            ]);

            $nombre = sprintf(
                'reporte-%s-%s-%d.%s',
                $tipoGuardado,
                now()->format('Ymd-His'),
                $registro->id_reporte,
                $extension
            );

            $ruta = 'reportes/' . $nombre;

            Excel::store(
                new ReporteInventarioExport($datos),
                $ruta,
                'local',
                $configuracion['writer']
            );

            $registro->update([
                'nombre_archivo' => $nombre,
                'ruta_archivo'   => $ruta,
            ]);

            $this->successMessage = sprintf(
                'El archivo %s se generó y quedó guardado en el historial.',
                $configuracion['etiqueta']
            );

            $this->actualizarVistaPrevia($reportes);

            return response()->streamDownload(
                function () use ($ruta): void {
                    echo Storage::disk('local')->get($ruta);
                },
                $nombre,
                ['Content-Type' => $configuracion['mime']]
            );
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

    public function eliminarReporte(
        int $reporteId
    ): void {
        $reporte = ReporteGenerado::findOrFail(
            $reporteId
        );

        if ($reporte->ruta_archivo) {
            Storage::disk('local')->delete(
                $reporte->ruta_archivo
            );
        }

        $reporte->delete();

        $this->successMessage =
            'El reporte fue eliminado del historial.';

        $this->resetPage(
            pageName: 'historialPage'
        );
    }

    public function render()
    {
        $reportes = app(
            ReporteDataService::class
        );

        $perPage = in_array(
            $this->perPage,
            [10, 25, 50],
            true
        )
            ? $this->perPage
            : 10;

        $sort =
        $this->sort === 'asc'
            ? 'asc'
            : 'desc';

        $historial = ReporteGenerado::query()
            ->with('usuario')
            ->orderBy(
                'fecha_generacion',
                $sort
            )
            ->paginate(
                $perPage,
                pageName: 'historialPage'
            );

        $modelos = Modelo::query()
            ->where('activo', true)
            ->when(
                $this->marca !== '',
                fn($query) =>
                $query->where(
                    'id_marca',
                    (int) $this->marca
                )
            )
            ->when(
                $this->tipoEquipo !== '',
                fn($query) =>
                $query->where(
                    'id_tipo_equipo',
                    (int) $this->tipoEquipo
                )
            )
            ->orderBy('nombre')
            ->get([
                'id_modelo',
                'nombre',
            ]);

        return view(
            'livewire.reportes-index',
            [
                'historial'    => $historial,

                'tiposEquipo'  =>
                TipoEquipo::where(
                    'activo',
                    true
                )
                    ->orderBy('nombre')
                    ->get([
                        'id_tipo_equipo',
                        'nombre',
                    ]),

                'marcas'       =>
                Marca::where(
                    'activo',
                    true
                )
                    ->orderBy('nombre')
                    ->get([
                        'id_marca',
                        'nombre',
                    ]),

                'modelos'      => $modelos,

                'estados'      =>
                $this->estadosDisponibles(),

                'tipoEtiqueta' => $this->etiquetaSeleccion($reportes),
            ]
        );
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

    private function actualizarVistaPrevia(
        ReporteDataService $reportes
    ): void {
        $datos = $reportes->datosCombinados(
            $this->tiposReporte,
            $this->filtros()
        );

        $this->previewTotal = $datos->count();

        $this->previewRecords = $datos
            ->take(6)
            ->map(fn($item) => (array) $item)
            ->all();

        $this->previewReady = true;
    }

    private function filtros(): array
    {
        return [
            'busqueda' => trim(
            $this->busquedaActivo) ?: null,

            'tipo_equipo'  =>
            $this->tipoEquipo ?: null,

            'estado'       =>
            $this->estado ?: null,

            'fecha_inicio' =>
            $this->fechaInicio ?: null,

            'fecha_fin'    =>
            $this->fechaFin ?: null,

            'modelo'       =>
            $this->modelo ?: null,

            'marca'        =>
            $this->marca ?: null,
        ];
    }

    private function estadosDisponibles(): array
    {
        if ($this->tipoReporte === 'asignaciones') {
            return [
                ['id_valor' => 'activa', 'nombre' => 'Activa'],
                ['id_valor' => 'finalizada', 'nombre' => 'Finalizada'],
            ];
        }

        [$tabla, $columna] = match ($this->tipoReporte) {
            'bajas'          => ['bajas', 'id_estado_baja'],
            'mantenimientos' => [
                'mantenimientos',
                'id_estado_mantenimiento',
            ],
            default          => ['equipos', 'id_estado_activo'],
        };

        return CatalogoValor::query()
            ->whereIn(
                'id_valor',
                DB::table($tabla)
                    ->select($columna)
                    ->whereNotNull($columna)
            )
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get([
                'id_valor',
                'nombre',
            ])
            ->toArray();
    }
    private function nombreUsuario(): string
    {
        $usuario = auth()->user();

        return trim(
            implode(
                ' ',
                array_filter([
                    $usuario?->nombres ??
                    $usuario?->name,

                    $usuario?->apellido_paterno,

                    $usuario?->apellido_materno,
                ])
            )
        ) ?: 'Usuario del sistema';
    }

    private function rules(): array
    {
        return [
            'tipoEquipo'  => [
                'nullable',
            ],

            'estado'      => [
                'nullable',
            ],

            'fechaInicio' => [
                'nullable',
                'date',
            ],

            'fechaFin'    => [
                'nullable',
                'date',
                'after_or_equal:fechaInicio',
            ],

            'modelo'      => [
                'nullable',
            ],

            'marca'       => [
                'nullable',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'fechaInicio.date'        =>
            'La fecha inicial no es válida.',

            'fechaFin.date'           =>
            'La fecha final no es válida.',

            'fechaFin.after_or_equal' =>
            'La fecha final debe ser igual o posterior a la fecha inicial.',
        ];
    }
}
