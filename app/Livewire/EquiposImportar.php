<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Json;
use Livewire\WithFileUploads;

/*
|--------------------------------------------------------------------------
| Inventario › Añadir › Importar CSV
|--------------------------------------------------------------------------
|
| 1. Se sube el CSV (plantilla de Administración › Tipos de equipo o el
|    CSV exportado del inventario).
| 2. Se revisa cada fila con las mismas reglas del alta y se muestra la
|    vista previa con los errores.
| 3. «Importar» guarda las filas sin errores de 10 en 10 (barra de
|    avance) con el mismo guardado del alta de equipos.
|
| Hereda de EquipoCreate para usar su configuración de formularios y su
| guardado; las filas leídas se guardan en caché entre peticiones.
|
*/

class EquiposImportar extends EquipoCreate
{
    use WithFileUploads;

    private const MAXIMO_FILAS = 500;

    private const POR_LOTE = 10;

    public $archivo = null;

    public string $nombreArchivo = '';

    // Clave en caché de las filas leídas (payloads).
    public string $clave = '';

    /*
     * Vista previa: [fila, tipo, nombre, serie, estado, mensajes, codigo]
     * estado: lista | error | importada | fallo
     */
    public array $filas = [];

    public bool $importando = false;

    public bool $terminado = false;

    // Resultado de la última importación: ['importadas' => n, 'codigos' => [...]].
    public array $ultimoResultado = [];

    public function mount(?int $duplicar = null): void
    {
        Gate::authorize('gestionar');
    }

    public function updatedArchivo(): void
    {
        Gate::authorize('gestionar');

        $this->resetErrorBag();
        $this->filas = [];
        $this->terminado = false;
        $this->ultimoResultado = [];

        $this->validate(
            ['archivo' => ['required', 'file', 'max:4096', 'mimes:csv,txt,xlsx']],
            [
                'archivo.mimes' => 'Sube la plantilla en Excel (.xlsx) o un archivo .csv.',
                'archivo.max' => 'El archivo no puede pasar de 4 MB.',
            ]
        );

        $this->nombreArchivo = (string) $this->archivo->getClientOriginalName();

        try {
            [$encabezados, $filas] = $this->leerArchivoTabla($this->archivo->getRealPath(), $this->nombreArchivo);
        } catch (\Throwable $error) {
            report($error);
            $this->addError('archivo', 'No se pudo leer el archivo. Revisa que sea la plantilla en Excel o un CSV.');

            return;
        }

        if (! in_array('tipo de equipo', array_map(fn ($e) => $this->csvNormalizar($e), $encabezados), true)) {
            $this->addError('archivo', 'Falta la columna «Tipo de equipo». Descarga la plantilla en Administración › Equipos.');

            return;
        }

        if ($filas === []) {
            $this->addError('archivo', 'El archivo no tiene filas con datos.');

            return;
        }

        if (count($filas) > self::MAXIMO_FILAS) {
            $this->addError('archivo', 'Se pueden importar hasta '.self::MAXIMO_FILAS.' equipos por archivo; este tiene '.count($filas).'.');

            return;
        }

        $tipos = collect($this->getTiposEquipoProperty())->pluck('nombre', 'id_tipo_equipo');
        $payloads = [];
        $vista = [];
        $seriesArchivo = [];

        foreach ($filas as $i => $fila) {
            try {
                [$payload, $errores, $tipoId] = $this->csvFilaAPayload($encabezados, $fila);
            } catch (\Throwable $error) {
                report($error);
                [$payload, $errores, $tipoId] = [null, ['La configuración de este tipo de equipo no se pudo leer.'], null];
            }

            $serie = trim((string) ($payload['numeroSerie'] ?? ''));

            if ($serie !== '') {
                $normal = mb_strtolower($serie);

                if (isset($seriesArchivo[$normal])) {
                    $errores[] = "El número de serie «{$serie}» se repite en la fila {$seriesArchivo[$normal]}.";
                } else {
                    $seriesArchivo[$normal] = $i + 2;
                }
            }

            $payloads[$i] = $payload;
            $vista[] = [
                'fila' => $i + 2,
                'tipo' => $tipoId ? (string) ($tipos[$tipoId] ?? '') : '',
                'nombre' => (string) ($payload['nombreEquipo'] ?? ''),
                'serie' => $serie,
                'estado' => $errores === [] ? 'lista' : 'error',
                'mensajes' => array_values(array_unique($errores)),
                'codigo' => '',
            ];
        }

        // Series que ya existen en el inventario (una sola consulta).
        $existentes = DB::table('equipos')
            ->whereIn(DB::raw('LOWER(numero_serie)'), array_keys($seriesArchivo))
            ->pluck('numero_serie')
            ->map(fn ($s) => mb_strtolower((string) $s))
            ->flip();

        foreach ($vista as $i => $fila) {
            if ($fila['serie'] !== '' && $existentes->has(mb_strtolower($fila['serie']))) {
                $vista[$i]['estado'] = 'error';
                $vista[$i]['mensajes'][] = "Ya hay un equipo con el número de serie «{$fila['serie']}».";
            }
        }

        $this->clave = 'importar-equipos:'.auth()->id().':'.Str::random(12);
        Cache::put($this->clave, $payloads, now()->addHours(2));

        $this->filas = $vista;
    }

    public function descartar(): void
    {
        if ($this->clave !== '') {
            Cache::forget($this->clave);
        }

        $this->reset(['archivo', 'nombreArchivo', 'clave', 'filas', 'importando', 'terminado', 'ultimoResultado']);
    }

    /*
     * Guarda las siguientes filas listas (de 10 en 10). La vista lo
     * llama hasta que devuelve 'pendientes' => 0.
     */
    /*
     * Acción normal (no #[Json]): así la tabla se redibuja en cada lote
     * y se limpia al terminar, y la campana recibe el aviso final.
     */
    public function importarLote(int $cuantos = self::POR_LOTE): array
    {
        $cuantos = max(1, min(self::POR_LOTE, $cuantos));
        $inicio = microtime(true);
        Gate::authorize('gestionar');

        $payloads = $this->clave !== '' ? Cache::get($this->clave) : null;

        if (! is_array($payloads)) {
            throw ValidationException::withMessages(['archivo' => 'La lectura del archivo expiró. Vuelve a subirlo.']);
        }

        $this->importando = true;
        $this->importandoCsv = true;
        $procesadas = 0;

        foreach ($this->filas as $i => $fila) {
            if ($fila['estado'] !== 'lista') {
                continue;
            }

            if ($procesadas >= $cuantos) {
                break;
            }

            $procesadas++;

            try {
                $resultado = $this->guardarEquipo($payloads[$i] ?? []);

                if (($resultado['ok'] ?? false) !== true) {
                    throw new \RuntimeException((string) ($resultado['message'] ?? 'No se pudo guardar.'));
                }

                $this->filas[$i]['estado'] = 'importada';
                $this->filas[$i]['codigo'] = (string) ($resultado['record']['code'] ?? '');
            } catch (ValidationException $error) {
                $this->filas[$i]['estado'] = 'fallo';
                $this->filas[$i]['mensajes'] = collect($error->errors())->flatten()->values()->all();
            } catch (\Throwable $error) {
                report($error);
                $this->filas[$i]['estado'] = 'fallo';
                $this->filas[$i]['mensajes'] = ['Error del servidor al guardar esta fila.'];
            }
        }

        $pendientes = collect($this->filas)->where('estado', 'lista')->count();
        $importadas = collect($this->filas)->where('estado', 'importada')->count();

        if ($pendientes === 0) {
            $this->terminar();
        }

        $milisegundos = (int) round((microtime(true) - $inicio) * 1000);

        // Medición: cuánto tarda el servidor por lote (storage/logs/importaciones.log).
        try {
            @file_put_contents(
                storage_path('logs/importaciones.log'),
                sprintf("[%s] %s · lote de %d equipos · %d ms (%d ms por equipo) · pico de memoria %.1f MB\n",
                    now()->format('Y-m-d H:i:s'), $this->nombreArchivo, $procesadas, $milisegundos,
                    $procesadas > 0 ? intdiv($milisegundos, $procesadas) : 0, memory_get_peak_usage(true) / 1048576),
                FILE_APPEND
            );
        } catch (\Throwable) {
        }

        return [
            'pendientes' => $pendientes,
            'importadas' => $importadas,
            'procesadas' => $procesadas,
            'ms' => $milisegundos,
        ];
    }

    private function terminar(): void
    {
        $this->importando = false;
        $this->terminado = true;

        Cache::forget($this->clave);

        $importadas = collect($this->filas)->where('estado', 'importada')->count();

        // Inventario y dashboard: una sola actualización al terminar.
        if ($importadas > 0) {
            app(\App\Services\SystemCacheService::class)->refreshModule('equipos');
            app(\App\Services\SystemCacheService::class)->refreshModule('dashboard');
        }

        /*
         * Lo importado sale de la tabla; quedan solo las filas con errores
         * para corregirlas. Si no queda ninguna, se quita el archivo.
         */
        $this->ultimoResultado = [
            'importadas' => $importadas,
            'codigos' => collect($this->filas)->where('estado', 'importada')->pluck('codigo')->filter()->values()->all(),
        ];

        $this->filas = collect($this->filas)->where('estado', '!=', 'importada')->values()->all();

        if ($this->filas === []) {
            $this->reset(['archivo', 'nombreArchivo', 'clave', 'terminado']);
        }

        if ($importadas === 0) {
            return;
        }

        try {
            $notificaciones = app(\App\Services\NotificacionService::class);

            $notificaciones->notificar(
                'equipos',
                $importadas === 1 ? 'Equipo importado' : $importadas.' equipos importados',
                $notificaciones->nombreActor().' importó '.$importadas.($importadas === 1 ? ' equipo' : ' equipos').' desde «'.$this->nombreArchivo.'».',
                route('equipos.index', [], false)
            );

            $this->dispatch('notificaciones-actualizadas');
        } catch (\Throwable $error) {
            report($error);
        }
    }

    public function placeholder()
    {
        return view('livewire.placeholders.equipos-importar');
    }

    public function render(): View
    {
        $filas = collect($this->filas);

        return view('livewire.equipos-importar', [
            'tiposPlantilla' => collect($this->getTiposEquipoProperty())
                ->map(fn ($t) => ['id' => (int) $t['id_tipo_equipo'], 'nombre' => (string) $t['nombre']])
                ->values()
                ->all(),
            'cuentas' => [
                'total' => $filas->count(),
                'listas' => $filas->where('estado', 'lista')->count(),
                'errores' => $filas->whereIn('estado', ['error', 'fallo'])->count(),
                'importadas' => $filas->where('estado', 'importada')->count(),
            ],
        ]);
    }
}
