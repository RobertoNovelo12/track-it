<?php

namespace App\Livewire\Catalogos;

use App\Services\CatalogoMarcasModelosArchivo;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/*
|--------------------------------------------------------------------------
| Administración › Marcas y modelos › Importar
|--------------------------------------------------------------------------
|
| Igual que la importación de Inventario: se sube el Excel o CSV, se
| revisan las filas (nuevas, por actualizar, sin cambios, con errores) y
| se guardan por lotes. La lógica está en CatalogoMarcasModelosArchivo.
|
*/

class MarcasModelosImportar extends Component
{
    use WithFileUploads;

    private const MAXIMO_FILAS = 1000;

    private const POR_LOTE = 20;

    public $archivo = null;

    public string $nombreArchivo = '';

    public array $filas = [];

    public bool $importando = false;

    public bool $terminado = false;

    public array $ultimoResultado = [];

    public bool $actualizarExistentes = false;

    public function mount(): void
    {
        Gate::authorize('gestionar');
    }

    private function servicio(): CatalogoMarcasModelosArchivo
    {
        return app(CatalogoMarcasModelosArchivo::class);
    }

    public function updatedArchivo(): void
    {
        Gate::authorize('gestionar');

        $this->resetErrorBag();
        $this->filas = [];
        $this->terminado = false;
        $this->ultimoResultado = [];
        $this->actualizarExistentes = false;

        $this->validate(
            ['archivo' => ['required', 'file', 'max:4096', 'mimes:csv,txt,xlsx']],
            [
                'archivo.mimes' => 'Sube la plantilla en Excel (.xlsx) o un archivo .csv.',
                'archivo.max' => 'El archivo no puede pasar de 4 MB.',
            ]
        );

        $this->nombreArchivo = (string) $this->archivo->getClientOriginalName();

        try {
            $filas = $this->servicio()->leer($this->archivo->getRealPath(), $this->nombreArchivo);
        } catch (\Throwable $error) {
            report($error);
            $this->addError('archivo', 'No se pudo leer el archivo. Revisa que sea la plantilla en Excel o un CSV.');

            return;
        }

        if ($filas === []) {
            $this->addError('archivo', 'El archivo no tiene filas con datos (o le faltan los encabezados de la plantilla).');

            return;
        }

        if (count($filas) > self::MAXIMO_FILAS) {
            $this->addError('archivo', 'Se pueden importar hasta '.self::MAXIMO_FILAS.' filas por archivo; este tiene '.count($filas).'.');

            return;
        }

        $this->filas = $this->servicio()->revisar($filas);
        $this->aplicarModo(false, true);
    }

    // Filas que ya existen: versión «se rechaza» o «se actualiza».
    private function aplicarModo(bool $actualizar, bool $forzar = false): void
    {
        if (! $forzar && $this->actualizarExistentes === $actualizar) {
            return;
        }

        $this->actualizarExistentes = $actualizar;

        foreach ($this->filas as $i => $fila) {
            if (($fila['existe'] ?? false) && ! in_array($fila['estado'] ?? '', ['creada', 'actualizada', 'fallo'], true)) {
                $this->filas[$i] = array_merge($fila, $actualizar ? $fila['actualizando'] : $fila['sinActualizar']);
            }
        }
    }

    public function descartar(): void
    {
        $this->reset(['archivo', 'nombreArchivo', 'filas', 'importando', 'terminado', 'ultimoResultado', 'actualizarExistentes']);
    }

    public function importarLote(int $cuantos = self::POR_LOTE, ?bool $actualizar = null): array
    {
        Gate::authorize('gestionar');

        $cuantos = max(1, min(self::POR_LOTE, $cuantos));

        if ($actualizar !== null && ! $this->importando) {
            $this->aplicarModo($actualizar);
        }

        $this->importando = true;
        $procesadas = 0;
        $origen = '«'.$this->nombreArchivo.'»';

        foreach ($this->filas as $i => $fila) {
            if (! in_array($fila['estado'], ['nueva', 'actualizar'], true)) {
                continue;
            }

            if ($procesadas >= $cuantos) {
                break;
            }

            $procesadas++;

            try {
                $codigo = $this->servicio()->guardar($fila, $origen);
                $this->filas[$i]['estado'] = $fila['estado'] === 'nueva' ? 'creada' : 'actualizada';
                $this->filas[$i]['codigo'] = $codigo;
            } catch (ValidationException $error) {
                $this->filas[$i]['estado'] = 'fallo';
                $this->filas[$i]['mensajes'] = collect($error->errors())->flatten()->values()->all();
            } catch (\Throwable $error) {
                report($error);
                $this->filas[$i]['estado'] = 'fallo';
                $this->filas[$i]['mensajes'] = ['Error del servidor al guardar esta fila.'];
            }
        }

        $pendientes = collect($this->filas)->whereIn('estado', ['nueva', 'actualizar'])->count();

        if ($pendientes === 0) {
            $this->terminar();
        }

        return [
            'pendientes' => $pendientes,
            'creadas' => (int) ($this->ultimoResultado['creadas'] ?? 0),
            'actualizadas' => (int) ($this->ultimoResultado['actualizadas'] ?? 0),
        ];
    }

    private function terminar(): void
    {
        $this->importando = false;
        $this->terminado = true;

        $filas = collect($this->filas);
        $creadas = $filas->where('estado', 'creada');
        $actualizadas = $filas->where('estado', 'actualizada');

        if ($creadas->count() + $actualizadas->count() > 0) {
            $this->servicio()->refrescarCaches();
        }

        $this->ultimoResultado = [
            'creadas' => $creadas->count(),
            'actualizadas' => $actualizadas->count(),
            'marcas' => $filas->whereIn('estado', ['creada', 'actualizada'])->where('clase', 'marca')->count(),
            'modelos' => $filas->whereIn('estado', ['creada', 'actualizada'])->where('clase', 'modelo')->count(),
        ];

        // Quedan solo las filas con errores para corregirlas.
        $this->filas = $filas->whereNotIn('estado', ['creada', 'actualizada', 'sin_cambios'])->values()->all();

        if ($this->filas === []) {
            $this->reset(['archivo', 'nombreArchivo', 'terminado', 'actualizarExistentes']);
        }
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.equipos-importar');
    }

    public function render(): View
    {
        $filas = collect($this->filas);
        $variante = fn (array $f, string $modo) => ($f['existe'] ?? false) && isset($f[$modo]) && ! in_array($f['estado'], ['creada', 'actualizada', 'fallo'], true)
            ? $f[$modo]['estado']
            : $f['estado'];

        $modos = [];

        foreach (['sin' => 'sinActualizar', 'con' => 'actualizando'] as $modo => $clave) {
            $estados = $filas->map(fn ($f) => $variante($f, $clave));
            $nuevas = $estados->filter(fn ($e) => $e === 'nueva')->count();
            $actualizar = $estados->filter(fn ($e) => $e === 'actualizar')->count();
            $total = $nuevas + $actualizar;

            $modos[$modo] = [
                'listas' => $total,
                'errores' => $estados->filter(fn ($e) => in_array($e, ['error', 'fallo'], true))->count(),
                'sinCambios' => $estados->filter(fn ($e) => $e === 'sin_cambios')->count(),
                'boton' => ucfirst(collect([$nuevas ? 'crear '.$nuevas : null, $actualizar ? 'actualizar '.$actualizar : null])->filter()->implode(' y ')).($total === 1 ? ' registro' : ' registros'),
            ];
        }

        return view('livewire.catalogos.marcas-modelos-importar', [
            'modos' => $modos,
            'cuentas' => [
                'total' => $filas->count(),
                'existentes' => $filas->where('existe', true)->count(),
                'marcas' => $filas->where('clase', 'marca')->count(),
                'modelos' => $filas->where('clase', 'modelo')->count(),
            ],
        ]);
    }
}
