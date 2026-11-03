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

    // Marcas y modelos creados desde esta importación (para el aviso final).
    public array $catalogosCreados = [];

    /*
     * Filas de equipos que ya existen (por código de inventario o serie):
     * apagado = se rechazan; encendido = se actualizan con lo que cambió.
     */
    public bool $actualizarExistentes = false;

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
        $this->catalogosCreados = [];

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

        // Filas crudas por número de fila del archivo (la 1 son los encabezados).
        $crudas = [];

        foreach (array_values($filas) as $i => $fila) {
            $crudas[$i + 2] = $fila;
        }

        $this->clave = 'importar-equipos:'.auth()->id().':'.Str::random(12);
        Cache::put($this->clave, ['encabezados' => $encabezados, 'filas' => $crudas, 'payloads' => []], now()->addHours(2));

        $this->analizar();
    }

    /*
     * Revisa las filas (todas o solo las indicadas por número) con las
     * reglas del alta y arma la vista previa. Se vuelve a llamar después
     * de crear marcas/modelos o de aplicar una sugerencia.
     */
    private function analizar(?array $soloFilas = null): void
    {
        $datos = $this->clave !== '' ? Cache::get($this->clave) : null;

        if (! is_array($datos) || ! isset($datos['filas'])) {
            $this->addError('archivo', 'La lectura del archivo expiró. Vuelve a subirlo.');

            return;
        }

        $this->csvMemoria = [];
        $tipos = collect($this->getTiposEquipoProperty())->pluck('nombre', 'id_tipo_equipo');
        $vista = [];
        $seriesArchivo = [];

        foreach ($datos['filas'] as $numero => $fila) {
            if ($soloFilas !== null && ! in_array((int) $numero, $soloFilas, true)) {
                continue;
            }

            $this->csvFaltantesFila = [];

            try {
                [$payload, $errores, $tipoId] = $this->csvFilaAPayload($datos['encabezados'], $fila);
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
                    $seriesArchivo[$normal] = (int) $numero;
                }
            }

            $datos['payloads'][$numero] = $payload;
            $vista[] = [
                'fila' => (int) $numero,
                'tipo' => $tipoId ? (string) ($tipos[$tipoId] ?? '') : '',
                'nombre' => (string) ($payload['nombreEquipo'] ?? ''),
                'serie' => $serie,
                'estado' => $errores === [] ? 'lista' : 'error',
                'mensajes' => array_values(array_unique($errores)),
                'faltan' => $this->csvFaltantesFila,
                'codigo' => '',
                'tipoId' => $tipoId ? (int) $tipoId : null,
            ];
        }

        $this->revisarExistentes($vista, $datos);

        Cache::put($this->clave, $datos, now()->addHours(2));

        $this->filas = $vista;
        $this->terminado = false;
    }

    /*
     * Filas de equipos que ya existen en el inventario: por «Código de
     * inventario» (CSV exportado) o, sin esa columna, por número de serie.
     * Con «Actualizar los equipos que ya existen» muestran lo que cambia.
     */
    private function revisarExistentes(array &$vista, array $datos): void
    {
        $indiceCodigo = null;

        foreach ($datos['encabezados'] as $i => $encabezado) {
            if ($this->csvNormalizar($encabezado) === 'codigo de inventario') {
                $indiceCodigo = $i;
            }
        }

        $codigoDe = fn (array $fila) => $indiceCodigo === null ? '' : trim((string) ($datos['filas'][$fila['fila']][$indiceCodigo] ?? ''));

        $codigos = collect($vista)->map($codigoDe)->filter()->map(fn ($c) => mb_strtolower($c))->unique()->values()->all();
        $series = collect($vista)->pluck('serie')->filter()->map(fn ($s) => mb_strtolower($s))->unique()->values()->all();

        $porCodigo = $codigos === [] ? [] : DB::table('equipos')->whereIn(DB::raw('LOWER(codigo_inventario)'), $codigos)
            ->get(['id_equipo', 'codigo_inventario'])->mapWithKeys(fn ($e) => [mb_strtolower((string) $e->codigo_inventario) => (int) $e->id_equipo])->all();
        $porSerie = $series === [] ? [] : DB::table('equipos')->whereIn(DB::raw('LOWER(numero_serie)'), $series)
            ->get(['id_equipo', 'numero_serie'])->mapWithKeys(fn ($e) => [mb_strtolower((string) $e->numero_serie) => (int) $e->id_equipo])->all();

        $ids = [];

        foreach ($vista as $i => $fila) {
            $codigo = $codigoDe($fila);

            if ($codigo !== '') {
                $id = $porCodigo[mb_strtolower($codigo)] ?? null;

                if ($id === null) {
                    $vista[$i]['estado'] = 'error';
                    $vista[$i]['mensajes'][] = "No hay un equipo con el código de inventario «{$codigo}». Para dar de alta uno nuevo, deja vacío el código.";

                    continue;
                }
            } else {
                $id = $fila['serie'] !== '' ? ($porSerie[mb_strtolower($fila['serie'])] ?? null) : null;
            }

            if ($id !== null) {
                $vista[$i]['id_equipo'] = $id;
                $ids[] = $id;
            }
        }

        $actuales = $ids === [] ? [] : $this->csvDatosActuales($ids);

        foreach ($vista as $i => $fila) {
            $id = $fila['id_equipo'] ?? null;

            if ($id === null || ! isset($actuales[$id])) {
                continue;
            }

            $actual = $actuales[$id];
            $codigo = (string) $actual['equipo']->codigo_inventario;
            $vista[$i]['existe'] = true;
            $vista[$i]['codigo'] = $codigo;

            /*
             * Se calculan las dos versiones de la fila: sin actualizar (se
             * rechaza) y actualizando (lo que cambia). La casilla cambia de
             * una a otra en el navegador, sin esperar al servidor.
             */
            $sinActualizar = [
                'estado' => 'error',
                'mensajes' => array_values(array_unique(array_merge(
                    array_filter($fila['mensajes'], fn ($m) => ! str_starts_with($m, 'Falta «') || str_starts_with($m, 'Falta «Tipo de equipo')),
                    ["Ya existe en el inventario ({$codigo}). Activa «Actualizar los equipos que ya existen» para aplicar sus cambios."]
                ))),
                'cambios' => [],
            ];

            // Celdas vacías conservan lo actual: «Falta…» no aplica al actualizar.
            $errores = array_values(array_filter($fila['mensajes'], fn ($m) => ! str_starts_with($m, 'Falta «') || str_starts_with($m, 'Falta «Tipo de equipo')));

            if ($actual['baja']) {
                $errores[] = "El equipo {$codigo} está dado de baja; no se actualiza.";
            }

            if ($fila['tipoId'] !== null && $fila['tipoId'] !== (int) $actual['equipo']->id_tipo_equipo) {
                $errores[] = "El equipo {$codigo} es de otro tipo; el tipo de equipo no se cambia desde el archivo.";
            }

            if ($errores !== []) {
                $actualizando = ['estado' => 'error', 'mensajes' => $errores, 'cambios' => []];
            } else {
                [$cambios, $avisos] = $this->csvCambios(
                    $datos['encabezados'],
                    $datos['filas'][$fila['fila']],
                    (int) $actual['equipo']->id_tipo_equipo,
                    $actual,
                    $datos['payloads'][$fila['fila']] ?? []
                );

                $actualizando = ['estado' => $cambios === [] ? 'sin_cambios' : 'actualizar', 'mensajes' => $avisos, 'cambios' => $cambios];
            }

            $vista[$i]['sinActualizar'] = $sinActualizar;
            $vista[$i]['actualizando'] = $actualizando;
            $vista[$i] = array_merge($vista[$i], $this->actualizarExistentes ? $actualizando : $sinActualizar);
        }
    }

    // La casilla ya no consulta al servidor (ver importarLote).
    private function aplicarModoActualizar(bool $actualizar): void
    {
        if ($this->actualizarExistentes === $actualizar) {
            return;
        }

        $this->actualizarExistentes = $actualizar;

        foreach ($this->filas as $i => $fila) {
            if (isset($fila['actualizando'], $fila['sinActualizar']) && ! in_array($fila['estado'], ['importada', 'actualizada', 'fallo'], true)) {
                $this->filas[$i] = array_merge($fila, $actualizar ? $fila['actualizando'] : $fila['sinActualizar']);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Marcas y modelos que no existen
    |--------------------------------------------------------------------------
    |
    | Se agrupan los que faltan (con cuántas filas afectan) y, si un nombre
    | se parece mucho a uno que ya existe, se sugiere usar ese en lugar de
    | crear un duplicado por un error de dedo.
    |
    */
    protected function catalogoFaltante(): array
    {
        $grupos = [];

        foreach ($this->filas as $fila) {
            if ($fila['estado'] !== 'error') {
                continue;
            }

            foreach ($fila['faltan'] ?? [] as $falta) {
                $clave = $falta['clase'] === 'marca'
                    ? 'marca:'.$this->csvNormalizar($falta['marca'])
                    : 'modelo:'.$this->csvNormalizar($falta['marca']).'|'.$falta['tipo'].'|'.$this->csvNormalizar($falta['modelo']);

                $grupos[$clave] ??= $falta + ['clave' => $clave, 'filas' => 0, 'sugerencia' => null, 'tipoNombre' => '', 'marcaNueva' => false];
                $grupos[$clave]['filas']++;
            }
        }

        if ($grupos === []) {
            return [];
        }

        $marcas = $this->csvMemo('lista.marcas', fn () => $this->getMarcasProperty());
        $modelos = $this->csvMemo('lista.modelos', fn () => $this->getAllModelos());
        $tipos = collect($this->getTiposEquipoProperty())->pluck('nombre', 'id_tipo_equipo');

        foreach ($grupos as $clave => $grupo) {
            $grupos[$clave]['tipoNombre'] = (string) ($tipos[$grupo['tipo']] ?? '');

            if ($grupo['clase'] === 'marca') {
                $grupos[$clave]['sugerencia'] = $this->nombreParecido($grupo['marca'], array_column($marcas, 'nombre'));

                continue;
            }

            $idMarca = $this->csvBuscar($grupo['marca'], $marcas, 'id_marca');
            $grupos[$clave]['marcaNueva'] = $idMarca === null;

            if ($idMarca !== null) {
                $mismos = array_filter(
                    $modelos,
                    fn ($m) => (string) $m['id_marca'] === (string) $idMarca && (int) $m['id_tipo_equipo'] === (int) $grupo['tipo']
                );
                $grupos[$clave]['sugerencia'] = $this->nombreParecido($grupo['modelo'], array_column($mismos, 'nombre'));
            }
        }

        // Marcas primero; cada modelo después de su marca.
        return collect($grupos)
            ->sortBy(fn ($g) => $this->csvNormalizar($g['marca']).($g['clase'] === 'marca' ? '0' : '1'.$this->csvNormalizar($g['modelo'])))
            ->values()
            ->all();
    }

    // El nombre existente más parecido (posible error de dedo) o null.
    private function nombreParecido(string $texto, array $nombres): ?string
    {
        $buscado = $this->csvNormalizar($texto);
        $mejor = null;
        $puntos = 0.0;

        foreach ($nombres as $nombre) {
            $actual = $this->csvNormalizar((string) $nombre);

            if ($actual === '' || $actual === $buscado) {
                continue;
            }

            $sinEspacios = str_replace([' ', '-', '_', '.'], '', $buscado) === str_replace([' ', '-', '_', '.'], '', $actual);
            similar_text($buscado, $actual, $porcentaje);
            $distancia = levenshtein($buscado, $actual);
            $limite = max(1, intdiv(max(strlen($buscado), strlen($actual)), 6));

            if ($sinEspacios || $distancia <= $limite || $porcentaje >= 85) {
                $valor = $sinEspacios ? 101 : $porcentaje;

                if ($valor > $puntos) {
                    [$mejor, $puntos] = [(string) $nombre, $valor];
                }
            }
        }

        return $mejor;
    }

    /*
     * Crea las marcas y modelos marcados (solo con nombre, marca y tipo;
     * el resto se completa en Administración) y vuelve a revisar las filas.
     */
    public function crearCatalogos(array $claves): array
    {
        Gate::authorize('gestionar');

        if ($this->importando) {
            return ['marcas' => 0, 'modelos' => 0];
        }

        $elegidos = collect($this->catalogoFaltante())->whereIn('clave', $claves);
        $creados = ['marcas' => [], 'modelos' => []];
        $origen = '«'.$this->nombreArchivo.'»';

        DB::transaction(function () use ($elegidos, &$creados, $origen): void {
            $marcaId = function (string $nombre): ?int {
                $id = DB::table('marcas')->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$nombre])->value('id_marca');

                return $id !== null ? (int) $id : null;
            };

            foreach ($elegidos->where('clase', 'marca') as $grupo) {
                $nombre = trim($grupo['marca']);

                if ($marcaId($nombre) !== null) {
                    continue;
                }

                $id = DB::table('marcas')->insertGetId([
                    'nombre' => $nombre,
                    'activo' => true,
                    'comentarios' => 'Creada al importar equipos desde '.$origen.'.',
                    'fecha_creacion' => now(),
                    'fecha_actualizacion' => now(),
                ], 'id_marca');

                $codigo = 'MAR-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
                DB::table('marcas')->where('id_marca', $id)->update(['codigo_interno' => $codigo]);
                $this->auditarCatalogo('MARCA_CREADA', "Se creó la marca {$nombre} ({$codigo}) al importar equipos desde {$origen}.");
                $creados['marcas'][] = $nombre;
            }

            foreach ($elegidos->where('clase', 'modelo') as $grupo) {
                $nombre = trim($grupo['modelo']);
                $idMarca = $marcaId($grupo['marca']);

                // Su marca no existe y no se eligió crearla.
                if ($idMarca === null) {
                    continue;
                }

                $existe = DB::table('modelos')
                    ->where('id_marca', $idMarca)
                    ->where('id_tipo_equipo', $grupo['tipo'])
                    ->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$nombre])
                    ->exists();

                if ($existe) {
                    continue;
                }

                $id = DB::table('modelos')->insertGetId([
                    'id_marca' => $idMarca,
                    'id_tipo_equipo' => $grupo['tipo'],
                    'nombre' => $nombre,
                    'activo' => true,
                    'comentarios' => 'Creado al importar equipos desde '.$origen.'.',
                    'fecha_creacion' => now(),
                    'fecha_actualizacion' => now(),
                ], 'id_modelo');

                $codigo = 'MOD-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
                DB::table('modelos')->where('id_modelo', $id)->update(['codigo_interno' => $codigo]);
                $this->auditarCatalogo('MODELO_CREADO', "Se creó el modelo {$nombre} ({$codigo}) al importar equipos desde {$origen}.");
                $creados['modelos'][] = $nombre;
            }
        });

        if ($creados['marcas'] !== [] || $creados['modelos'] !== []) {
            app(\App\Services\SystemCacheService::class)->refreshModule('catalogos');
            Cache::forget('form.marcas.v2');
            Cache::forget('form.modelos.all.v2');

            $this->catalogosCreados['marcas'] = array_values(array_unique(array_merge($this->catalogosCreados['marcas'] ?? [], $creados['marcas'])));
            $this->catalogosCreados['modelos'] = array_values(array_unique(array_merge($this->catalogosCreados['modelos'] ?? [], $creados['modelos'])));
        }

        $this->analizar(array_map(fn ($f) => (int) $f['fila'], $this->filas));

        return ['marcas' => count($creados['marcas']), 'modelos' => count($creados['modelos'])];
    }

    /*
     * «¿Quisiste decir…?»: cambia en las filas del archivo el nombre
     * escrito por el que ya existe y las vuelve a revisar.
     */
    public function usarSugerencia(string $clave): void
    {
        Gate::authorize('gestionar');

        $grupo = collect($this->catalogoFaltante())->firstWhere('clave', $clave);
        $datos = $this->clave !== '' ? Cache::get($this->clave) : null;

        if (! $grupo || ! $grupo['sugerencia'] || ! is_array($datos) || $this->importando) {
            return;
        }

        $normal = fn ($t) => $this->csvNormalizar((string) $t);
        $indices = [];

        foreach ($datos['encabezados'] as $i => $encabezado) {
            $indices[$normal($encabezado)] = $i;
        }

        $colTipo = $indices['tipo de equipo'] ?? null;
        $colMarca = $indices[$normal($grupo['clase'] === 'marca' ? $grupo['columna'] : 'Marca')] ?? null;
        $colModelo = $grupo['clase'] === 'modelo' ? ($indices[$normal($grupo['columna'])] ?? null) : null;
        $tipos = collect($this->getTiposEquipoProperty())->pluck('id_tipo_equipo', 'nombre')->mapWithKeys(fn ($id, $n) => [$normal($n) => (int) $id]);

        foreach ($datos['filas'] as $numero => $fila) {
            if ($grupo['clase'] === 'marca') {
                if ($colMarca !== null && $normal($fila[$colMarca] ?? '') === $normal($grupo['marca'])) {
                    $datos['filas'][$numero][$colMarca] = $grupo['sugerencia'];
                }

                continue;
            }

            if (
                $colModelo !== null
                && $normal($fila[$colModelo] ?? '') === $normal($grupo['modelo'])
                && ($colMarca === null || $normal($fila[$colMarca] ?? '') === $normal($grupo['marca']))
                && ($colTipo === null || ($tipos[$normal($fila[$colTipo] ?? '')] ?? null) === (int) $grupo['tipo'])
            ) {
                $datos['filas'][$numero][$colModelo] = $grupo['sugerencia'];
            }
        }

        Cache::put($this->clave, $datos, now()->addHours(2));

        $this->analizar(array_map(fn ($f) => (int) $f['fila'], $this->filas));
    }

    private function auditarCatalogo(string $accion, string $descripcion): void
    {
        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => $accion,
            'modulo' => 'CATALOGOS',
            'descripcion' => $descripcion,
            'fecha_hora' => now(),
        ]);
    }

    public function descartar(): void
    {
        if ($this->clave !== '') {
            Cache::forget($this->clave);
        }

        $this->reset(['archivo', 'nombreArchivo', 'clave', 'filas', 'importando', 'terminado', 'ultimoResultado', 'catalogosCreados', 'actualizarExistentes']);
    }

    /*
     * Guarda las siguientes filas listas (de 10 en 10). La vista lo
     * llama hasta que devuelve 'pendientes' => 0.
     */
    /*
     * Acción normal (no #[Json]): así la tabla se redibuja en cada lote
     * y se limpia al terminar, y la campana recibe el aviso final.
     */
    public function importarLote(int $cuantos = self::POR_LOTE, ?bool $actualizar = null): array
    {
        $cuantos = max(1, min(self::POR_LOTE, $cuantos));
        $inicio = microtime(true);
        Gate::authorize('gestionar');

        // La casilla «Actualizar…» llega con el primer lote.
        if ($actualizar !== null && ! $this->importando) {
            $this->aplicarModoActualizar($actualizar);
        }

        $datos = $this->clave !== '' ? Cache::get($this->clave) : null;
        $payloads = is_array($datos) ? ($datos['payloads'] ?? null) : null;

        if (! is_array($payloads)) {
            throw ValidationException::withMessages(['archivo' => 'La lectura del archivo expiró. Vuelve a subirlo.']);
        }

        $this->importando = true;
        $this->importandoCsv = true;
        $procesadas = 0;

        foreach ($this->filas as $i => $fila) {
            if (! in_array($fila['estado'], ['lista', 'actualizar'], true)) {
                continue;
            }

            if ($procesadas >= $cuantos) {
                break;
            }

            $procesadas++;

            try {
                if ($fila['estado'] === 'actualizar') {
                    $this->csvActualizarEquipo((int) $fila['id_equipo'], $payloads[$fila['fila']] ?? [], $fila['cambios'] ?? []);
                    $this->filas[$i]['estado'] = 'actualizada';

                    continue;
                }

                $resultado = $this->guardarEquipo($payloads[$fila['fila']] ?? []);

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

        $pendientes = collect($this->filas)->whereIn('estado', ['lista', 'actualizar'])->count();
        $importadas = collect($this->filas)->where('estado', 'importada')->count();
        $actualizadas = collect($this->filas)->where('estado', 'actualizada')->count();

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
            'actualizadas' => $actualizadas,
            'procesadas' => $procesadas,
            'ms' => $milisegundos,
        ];
    }

    private function terminar(): void
    {
        $this->importando = false;
        $this->terminado = true;

        $importadas = collect($this->filas)->where('estado', 'importada')->count();
        $actualizadas = collect($this->filas)->where('estado', 'actualizada')->count();

        // Inventario y dashboard: una sola actualización al terminar.
        if ($importadas + $actualizadas > 0) {
            app(\App\Services\SystemCacheService::class)->refreshModule('equipos');
            app(\App\Services\SystemCacheService::class)->refreshModule('dashboard');
        }

        /*
         * Lo importado sale de la tabla; quedan solo las filas con errores
         * para corregirlas. Si no queda ninguna, se quita el archivo.
         */
        $this->ultimoResultado = [
            'importadas' => $importadas,
            'actualizadas' => $actualizadas,
            'codigos' => collect($this->filas)->where('estado', 'importada')->pluck('codigo')->filter()->values()->all(),
        ];

        $this->filas = collect($this->filas)->whereNotIn('estado', ['importada', 'actualizada', 'sin_cambios'])->values()->all();

        // Quedan filas con errores: se conserva la lectura para corregirlas aquí.
        if ($this->filas === []) {
            Cache::forget($this->clave);
            $this->reset(['archivo', 'nombreArchivo', 'clave', 'terminado']);
        }

        if ($importadas + $actualizadas === 0) {
            return;
        }

        try {
            $notificaciones = app(\App\Services\NotificacionService::class);
            $partes = array_filter([
                $importadas ? 'importó '.$importadas.($importadas === 1 ? ' equipo' : ' equipos') : null,
                $actualizadas ? 'actualizó '.$actualizadas.($actualizadas === 1 ? ' equipo' : ' equipos') : null,
            ]);

            $notificaciones->notificar(
                'equipos',
                $importadas && $actualizadas
                    ? 'Importación de equipos'
                    : ($importadas ? ($importadas === 1 ? 'Equipo importado' : $importadas.' equipos importados') : ($actualizadas === 1 ? 'Equipo actualizado' : $actualizadas.' equipos actualizados')),
                $notificaciones->nombreActor().' '.implode(' y ', $partes).' desde «'.$this->nombreArchivo.'».'.$this->textoCatalogosCreados(),
                route('equipos.index', [], false)
            );

            $this->dispatch('notificaciones-actualizadas');
        } catch (\Throwable $error) {
            report($error);
        }
    }

    private function textoCatalogosCreados(): string
    {
        $partes = [];
        $marcas = count($this->catalogosCreados['marcas'] ?? []);
        $modelos = count($this->catalogosCreados['modelos'] ?? []);

        if ($marcas) {
            $partes[] = $marcas.($marcas === 1 ? ' marca' : ' marcas');
        }

        if ($modelos) {
            $partes[] = $modelos.($modelos === 1 ? ' modelo' : ' modelos');
        }

        return $partes === [] ? '' : ' Se crearon '.implode(' y ', $partes).' desde la importación.';
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
            'faltantes' => $this->importando ? [] : $this->catalogoFaltante(),
            'cuentas' => [
                'total' => $filas->count(),
                'listas' => $filas->whereIn('estado', ['lista', 'actualizar'])->count(),
                'nuevas' => $filas->where('estado', 'lista')->count(),
                'actualizar' => $filas->where('estado', 'actualizar')->count(),
                'sinCambios' => $filas->where('estado', 'sin_cambios')->count(),
                'existentes' => $filas->where('existe', true)->count(),
                // Con la casilla marcada (se calcula en el navegador al cambiarla).
                'conActualizar' => [
                    'actualizar' => $filas->filter(fn ($f) => ($f['actualizando']['estado'] ?? $f['estado']) === 'actualizar' && ! in_array($f['estado'], ['importada', 'actualizada'], true))->count(),
                    'sinCambios' => $filas->filter(fn ($f) => ($f['actualizando']['estado'] ?? $f['estado']) === 'sin_cambios')->count(),
                    'errores' => $filas->filter(fn ($f) => in_array($f['actualizando']['estado'] ?? $f['estado'], ['error', 'fallo'], true))->count(),
                ],
                'sinActualizar' => [
                    'errores' => $filas->filter(fn ($f) => in_array($f['sinActualizar']['estado'] ?? $f['estado'], ['error', 'fallo'], true))->count(),
                ],
                'errores' => $filas->whereIn('estado', ['error', 'fallo'])->count(),
                'importadas' => $filas->where('estado', 'importada')->count(),
            ],
        ]);
    }
}
