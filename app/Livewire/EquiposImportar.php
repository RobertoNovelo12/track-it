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

        $this->validate(
            ['archivo' => ['required', 'file', 'max:4096', 'mimes:csv,txt']],
            [
                'archivo.mimes' => 'Sube un archivo .csv (en Excel: Guardar como › CSV UTF-8).',
                'archivo.max' => 'El archivo no puede pasar de 4 MB.',
            ]
        );

        $this->nombreArchivo = (string) $this->archivo->getClientOriginalName();

        [$encabezados, $filas] = $this->csvLeer((string) file_get_contents($this->archivo->getRealPath()));

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

        $this->reset(['archivo', 'nombreArchivo', 'clave', 'filas', 'importando', 'terminado']);
    }

    /*
     * Guarda las siguientes filas listas (de 10 en 10). La vista lo
     * llama hasta que devuelve 'pendientes' => 0.
     */
    #[Json]
    public function importarLote(): array
    {
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

            if ($procesadas >= self::POR_LOTE) {
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

        if ($pendientes === 0) {
            $this->terminar();
        }

        return [
            'pendientes' => $pendientes,
            'importadas' => collect($this->filas)->where('estado', 'importada')->count(),
        ];
    }

    private function terminar(): void
    {
        $this->importando = false;
        $this->terminado = true;

        Cache::forget($this->clave);

        $importadas = collect($this->filas)->where('estado', 'importada')->count();

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
