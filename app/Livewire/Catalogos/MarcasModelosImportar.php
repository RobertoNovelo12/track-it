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

    private const POR_LOTE = 10;

    public $archivo = null;

    public string $nombreArchivo = '';

    public array $filas = [];

    public bool $importando = false;

    public bool $terminado = false;

    public array $ultimoResultado = [];

    public bool $actualizarExistentes = false;

    // Filas tal como vienen en el archivo (para corregirlas y revisar de nuevo).
    public array $crudas = [];

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

        $this->crudas = $filas;
        $this->filas = $this->servicio()->revisar($filas);
        $this->aplicarModo(false, true);
    }

    /*
     * Valor fuera de lista: se cambia por una opción válida (o se quita,
     * '__quitar__') en todas las filas que lo traen y se revisa de nuevo.
     */
    public function corregir(string $clave, string $nuevo, ?bool $actualizar = null): void
    {
        Gate::authorize('gestionar');

        if ($this->importando) {
            return;
        }

        $correccion = collect($this->filas)->pluck('correcciones')->flatten(1)->firstWhere('clave', $clave);

        if (! $correccion) {
            return;
        }

        $servicio = $this->servicio();
        $quitar = $nuevo === '__quitar__';

        if (! $quitar && ! in_array($servicio->normalizar($nuevo), array_map(fn ($o) => $servicio->normalizar($o), $correccion['opciones']), true)) {
            return;
        }

        foreach ($this->crudas as $i => $cruda) {
            if (
                $cruda['clase'] === $correccion['clase']
                && $servicio->normalizar((string) ($cruda['valores'][$correccion['columna']] ?? '')) === $servicio->normalizar($correccion['valor'])
            ) {
                $this->crudas[$i]['valores'][$correccion['columna']] = $quitar ? '' : $nuevo;
            }
        }

        $this->revisarDeNuevo($actualizar ?? $this->actualizarExistentes);
    }

    /*
     * Antes de guardar: las correcciones hechas en el navegador se aplican
     * una sola vez (petición propia, rápida) y la vista toma el resultado.
     */
    public function prepararImportacion(array $correcciones, bool $actualizar): array
    {
        Gate::authorize('gestionar');
        @set_time_limit(120);

        if ($correcciones !== []) {
            $this->aplicarCorrecciones($correcciones, $actualizar);
        } else {
            $this->aplicarModo($actualizar);
        }

        return ['pendientes' => collect($this->filas)->whereIn('estado', ['nueva', 'actualizar'])->count()];
    }

    // {clave de corrección: valor nuevo | '__quitar__'} → filas del archivo.
    private function aplicarCorrecciones(array $mapa, bool $actualizar): void
    {
        $servicio = $this->servicio();
        $todas = collect($this->filas)->pluck('correcciones')->flatten(1)->keyBy('clave');

        foreach ($mapa as $clave => $nuevo) {
            $correccion = $todas->get($clave);
            $nuevo = (string) $nuevo;

            if (! $correccion) {
                continue;
            }

            $quitar = $nuevo === '__quitar__';

            if (! $quitar && ! in_array($servicio->normalizar($nuevo), array_map(fn ($o) => $servicio->normalizar($o), $correccion['opciones']), true)) {
                continue;
            }

            foreach ($this->crudas as $i => $cruda) {
                if (
                    $cruda['clase'] === $correccion['clase']
                    && $servicio->normalizar((string) ($cruda['valores'][$correccion['columna']] ?? '')) === $servicio->normalizar($correccion['valor'])
                ) {
                    $this->crudas[$i]['valores'][$correccion['columna']] = $quitar ? '' : $nuevo;
                }
            }
        }

        $this->revisarDeNuevo($actualizar);
    }

    private function revisarDeNuevo(bool $actualizar): void
    {
        $this->filas = $this->servicio()->revisar($this->crudas);
        $this->actualizarExistentes = $actualizar;
        $this->aplicarModo($actualizar, true);
        $this->terminado = false;
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
        $this->reset(['archivo', 'nombreArchivo', 'filas', 'crudas', 'importando', 'terminado', 'ultimoResultado', 'actualizarExistentes']);
    }

    public function importarLote(int $cuantos = self::POR_LOTE, ?bool $actualizar = null, array $correcciones = []): array
    {
        Gate::authorize('gestionar');

        $cuantos = max(1, min(self::POR_LOTE, $cuantos));

        // Varias consultas por fila a la base remota: margen sobre los 30 s.
        @set_time_limit(120);

        if (! $this->importando && $actualizar !== null) {
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

        /*
         * Como en Inventario: salen de la tabla las guardadas, las que no
         * tenían cambios y las que ya existían (sin «Actualizar…»). Quedan
         * solo las filas con errores para corregirlas.
         */
        $omitidas = $filas->where('estado', 'existe')->count();
        $this->ultimoResultado['omitidas'] = $omitidas;
        $hechas = $filas->whereIn('estado', ['creada', 'actualizada', 'sin_cambios', 'existe'])->pluck('numero')->flip();
        $this->crudas = array_values(array_filter($this->crudas, fn ($c, $i) => ! $hechas->has($i), ARRAY_FILTER_USE_BOTH));
        $this->filas = $filas->whereNotIn('estado', ['creada', 'actualizada', 'sin_cambios', 'existe'])->values()->all();

        if ($this->filas === []) {
            $this->reset(['archivo', 'nombreArchivo', 'terminado', 'actualizarExistentes', 'crudas']);
        } else {
            // Con las marcas ya creadas, sus modelos pendientes se revisan otra vez.
            $this->revisarDeNuevo($this->actualizarExistentes);
            $this->terminado = true;
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
