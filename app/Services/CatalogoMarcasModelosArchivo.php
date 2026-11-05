<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
|--------------------------------------------------------------------------
| Marcas y modelos en Excel / CSV
|--------------------------------------------------------------------------
|
| Exportar (con los filtros de la pantalla), plantillas e importar, igual
| que en Inventario:
|
| - Excel: hojas «Marcas» y «Modelos» (se pueden usar las dos o solo una).
|   Las marcas se guardan primero, así un modelo puede usar una marca que
|   viene en la misma importación.
| - CSV: una sola tabla; si trae la columna «Modelo» son modelos.
| - Cada fila se reconoce por su «Código» (MAR-… / MOD-…) o, sin código,
|   por su nombre (modelo: marca + tipo de equipo + nombre).
| - Al actualizar, una celda vacía conserva el dato actual.
|
*/

class CatalogoMarcasModelosArchivo
{
    private array $memo = [];

    /*
    |--------------------------------------------------------------------------
    | Columnas
    |--------------------------------------------------------------------------
    | clave => [etiqueta, obligatoria al crear, tipo]
    */

    public function columnas(string $clase): array
    {
        $unidad = app(PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] ?? 'Hotel';

        return $clase === 'marca'
            ? [
                'codigo' => ['Código', false, 'texto'],
                'nombre' => ['Nombre', true, 'texto'],
                'descripcion' => ['Descripción', false, 'texto'],
                'sitio_web' => ['Sitio web', false, 'url'],
                'id_pais_origen' => ['País de origen', false, 'lista'],
                'id_proveedor_sugerido' => ['Proveedor sugerido', false, 'lista'],
                'garantia_estandar_meses' => ['Garantía estándar (meses)', false, 'numero'],
                'comentarios' => ['Comentarios', false, 'texto'],
                'activo' => ['Estado', false, 'estado'],
            ]
            : [
                'codigo' => ['Código', false, 'texto'],
                'id_marca' => ['Marca', true, 'lista'],
                'id_tipo_equipo' => ['Tipo de equipo', true, 'lista'],
                'nombre' => ['Modelo', true, 'texto'],
                'descripcion' => ['Descripción', false, 'texto'],
                'sitio_web' => ['Sitio web', false, 'url'],
                'id_proveedor_sugerido' => ['Proveedor sugerido', false, 'lista'],
                'garantia_estandar_meses' => ['Garantía estándar (meses)', false, 'numero'],
                'id_unidad_uso_comun' => [$unidad.' de uso común', false, 'lista'],
                'comentarios' => ['Comentarios', false, 'texto'],
                'activo' => ['Estado', false, 'estado'],
            ];
    }

    private function encabezado(array $definicion): string
    {
        return $definicion[0].($definicion[1] ? ' *' : '');
    }

    public function normalizar(?string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim((string) $texto)));
        $texto = trim(str_replace('*', '', $texto));

        return preg_replace('/\s+/', ' ', $texto) ?? $texto;
    }

    /*
     * Listas de los catálogos relacionados: [id => nombre].
     */
    public function lista(string $clave): array
    {
        return $this->memo['lista.'.$clave] ??= match ($clave) {
            'id_pais_origen' => DB::table('catalogo_valores as cv')
                ->join('catalogos as c', 'c.id_catalogo', '=', 'cv.id_catalogo')
                ->where('c.clave', 'PAIS_ORIGEN')
                ->where('cv.activo', true)
                ->orderBy('cv.orden')->orderBy('cv.nombre')
                ->pluck('cv.nombre', 'cv.id_valor')->all(),
            'id_proveedor_sugerido' => DB::table('proveedores')->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id_proveedor')->all(),
            'id_unidad_uso_comun' => DB::table('unidades_organizativas')->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id_unidad')->all(),
            'id_tipo_equipo' => DB::table('tipos_equipo')->where('activo', true)->orderBy('nombre')->pluck('nombre', 'id_tipo_equipo')->all(),
            'id_marca' => DB::table('marcas')->orderBy('nombre')->pluck('nombre', 'id_marca')->all(),
            default => [],
        };
    }

    // Todos los nombres (también inactivos) para mostrar lo actual.
    private function nombres(string $clave): array
    {
        return $this->memo['nombres.'.$clave] ??= match ($clave) {
            'id_pais_origen' => DB::table('catalogo_valores')->pluck('nombre', 'id_valor')->all(),
            'id_proveedor_sugerido' => DB::table('proveedores')->pluck('nombre', 'id_proveedor')->all(),
            'id_unidad_uso_comun' => DB::table('unidades_organizativas')->pluck('nombre', 'id_unidad')->all(),
            'id_tipo_equipo' => DB::table('tipos_equipo')->pluck('nombre', 'id_tipo_equipo')->all(),
            'id_marca' => DB::table('marcas')->pluck('nombre', 'id_marca')->all(),
            default => [],
        };
    }

    private function buscarEnLista(string $clave, string $texto): ?int
    {
        $buscado = $this->normalizar($texto);

        foreach ($this->lista($clave) as $id => $nombre) {
            if ($this->normalizar((string) $nombre) === $buscado) {
                return (int) $id;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Exportar
    |--------------------------------------------------------------------------
    */

    public function registros(string $clase, array $filtros = []): \Illuminate\Support\Collection
    {
        $tabla = $clase === 'marca' ? 'marcas' : 'modelos';
        $consulta = DB::table($tabla.' as r');

        if ($clase === 'modelo') {
            $consulta->leftJoin('marcas as m', 'm.id_marca', '=', 'r.id_marca')
                ->leftJoin('tipos_equipo as te', 'te.id_tipo_equipo', '=', 'r.id_tipo_equipo')
                ->select('r.*');
        } else {
            $consulta->select('r.*');
        }

        $buscar = trim((string) ($filtros['search'] ?? ''));

        if ($buscar !== '') {
            $like = '%'.mb_strtolower($buscar).'%';
            $consulta->where(function ($q) use ($like, $clase) {
                $q->whereRaw('LOWER(r.nombre) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(COALESCE(r.descripcion, '')) LIKE ?", [$like]);

                if ($clase === 'modelo') {
                    $q->orWhereRaw("LOWER(COALESCE(m.nombre, '')) LIKE ?", [$like])
                        ->orWhereRaw("LOWER(COALESCE(te.nombre, '')) LIKE ?", [$like]);
                }
            });
        }

        $estado = (string) ($filtros['estado'] ?? 'todos');

        if (in_array($estado, ['activos', 'inactivos'], true)) {
            $consulta->where('r.activo', $estado === 'activos');
        }

        $tipo = (string) ($filtros['tipo'] ?? 'todos');

        if ($clase === 'modelo' && ctype_digit($tipo)) {
            $consulta->where('r.id_tipo_equipo', (int) $tipo);
        }

        return $consulta->orderBy('r.'.($clase === 'marca' ? 'id_marca' : 'id_modelo'))->get();
    }

    // Una fila como textos (igual que se escribe en el archivo).
    public function textos(string $clase, object $registro): array
    {
        $fila = [];

        foreach ($this->columnas($clase) as $clave => $definicion) {
            $valor = match ($clave) {
                'codigo' => (string) ($registro->codigo_interno ?? ''),
                'activo' => $registro->activo ? 'Activo' : 'Inactivo',
                default => $registro->{$clave} ?? '',
            };

            if ($definicion[2] === 'lista' && $valor !== '' && $valor !== null) {
                $valor = $this->nombres($clave)[$valor] ?? '';
            }

            $fila[$clave] = trim((string) $valor);
        }

        return $fila;
    }

    public function exportar(string $clase, string $formato, array $filtros = []): StreamedResponse
    {
        $registros = $this->registros($clase, $filtros);
        $columnas = $this->columnas($clase);
        $nombre = ($clase === 'marca' ? 'marcas' : 'modelos').'-'.now()->format('Ymd-His');

        $filas = $registros->map(fn ($r) => array_values($this->textos($clase, $r)))->all();
        $encabezados = array_map(fn ($d) => $this->encabezado($d), array_values($columnas));

        if ($formato === 'xlsx') {
            $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $libro->removeSheetByIndex(0);
            $this->hoja($libro, $clase === 'marca' ? 'Marcas' : 'Modelos', $clase, $filas);

            return $this->descargarLibro($libro, $nombre.'.xlsx');
        }

        return $this->descargarCsv($nombre.'.csv', array_merge([$encabezados], $filas));
    }

    /*
    |--------------------------------------------------------------------------
    | Plantillas
    |--------------------------------------------------------------------------
    */

    public function plantilla(string $formato, string $clase = 'ambos'): StreamedResponse
    {
        if ($formato === 'xlsx') {
            $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $libro->removeSheetByIndex(0);

            foreach ($clase === 'ambos' ? ['marca', 'modelo'] : [$clase] as $c) {
                $this->hoja($libro, $c === 'marca' ? 'Marcas' : 'Modelos', $c, []);
            }

            $libro->setActiveSheetIndex(0);

            return $this->descargarLibro($libro, 'plantilla-marcas-y-modelos.xlsx');
        }

        $c = $clase === 'marca' ? 'marca' : 'modelo';

        return $this->descargarCsv(
            'plantilla-'.($c === 'marca' ? 'marcas' : 'modelos').'.csv',
            [array_map(fn ($d) => $this->encabezado($d), array_values($this->columnas($c)))]
        );
    }

    // Hoja con encabezados (obligatorios en azul), listas desplegables y filas.
    private function hoja(\PhpOffice\PhpSpreadsheet\Spreadsheet $libro, string $titulo, string $clase, array $filas): void
    {
        $hoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($libro, $titulo);
        $libro->addSheet($hoja);

        $listas = $libro->getSheetByName('Listas');

        if (! $listas) {
            $listas = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($libro, 'Listas');
            $libro->addSheet($listas);
            $listas->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        }

        $columnaLista = (int) \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($listas->getHighestColumn());

        if (trim((string) $listas->getCell('A1')->getValue()) === '') {
            $columnaLista = 0;
        }

        $limite = max(500, count($filas) + 200);

        foreach (array_values($this->columnas($clase)) as $i => $definicion) {
            $clave = array_keys($this->columnas($clase))[$i];
            $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $encabezado = $this->encabezado($definicion);

            $hoja->setCellValue($letra.'1', $encabezado);
            $hoja->getColumnDimension($letra)->setWidth(max(14, mb_strlen($encabezado) + 4));
            $hoja->getStyle($letra.'1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $definicion[1] ? 'FFFFFF' : '1F2937']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $definicion[1] ? '2A78D6' : 'E5E7EB']],
                'alignment' => ['vertical' => 'center'],
            ]);
            $hoja->getStyle($letra.'2:'.$letra.$limite)->getNumberFormat()->setFormatCode('@');

            $opciones = match ($definicion[2]) {
                'lista' => array_values(array_unique(array_map('strval', $this->lista($clave)))),
                'estado' => ['Activo', 'Inactivo'],
                default => [],
            };

            if ($opciones === []) {
                continue;
            }

            $columnaLista++;
            $letraLista = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnaLista);
            $listas->setCellValue($letraLista.'1', $titulo.' · '.$definicion[0]);

            foreach ($opciones as $n => $opcion) {
                $listas->setCellValueExplicit($letraLista.($n + 2), $opcion, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $validacion = $hoja->getCell($letra.'2')->getDataValidation();
            $validacion->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Valor no válido')
                ->setError('Elige una opción de la lista.')
                ->setFormula1('Listas!$'.$letraLista.'$2:$'.$letraLista.'$'.(count($opciones) + 1));

            $hoja->setDataValidation($letra.'2:'.$letra.$limite, clone $validacion);
        }

        foreach ($filas as $n => $fila) {
            foreach (array_values($fila) as $i => $valor) {
                $hoja->setCellValueExplicit(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1).($n + 2),
                    (string) $valor,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
        }

        $hoja->freezePane('A2');
        $hoja->getRowDimension(1)->setRowHeight(22);
    }

    private function descargarLibro(\PhpOffice\PhpSpreadsheet\Spreadsheet $libro, string $nombre): StreamedResponse
    {
        $libro->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($libro) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro))->save('php://output');
        }, $nombre, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function descargarCsv(string $nombre, array $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ',', '"', '\\');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /*
    |--------------------------------------------------------------------------
    | Leer el archivo
    |--------------------------------------------------------------------------
    | => [['clase' => 'marca'|'modelo', 'etiqueta' => 'Marcas · fila 3',
    |      'valores' => [clave => texto]], ...]  (marcas primero)
    */

    public function leer(string $ruta, string $nombreArchivo): array
    {
        $tablas = [];

        if (in_array(strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION)), ['xlsx', 'xls'], true)) {
            $lector = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($ruta);
            $libro = $lector->load($ruta);

            foreach ($libro->getWorksheetIterator() as $hoja) {
                if ($hoja->getSheetState() !== \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VISIBLE) {
                    continue;
                }

                $tablas[] = [$hoja->getTitle(), $hoja->toArray(null, true, false, false)];
            }
        } else {
            $tablas[] = ['Archivo', $this->leerCsv((string) file_get_contents($ruta))];
        }

        $filas = [];

        foreach ($tablas as [$titulo, $datos]) {
            $encabezados = null;

            foreach ($datos as $n => $fila) {
                $fila = array_map(fn ($v) => trim((string) ($v ?? '')), (array) $fila);

                if (collect($fila)->every(fn ($v) => $v === '')) {
                    continue;
                }

                if ($encabezados === null) {
                    $encabezados = array_map(fn ($e) => $this->normalizar($e), $fila);

                    continue;
                }

                // Con columna «Modelo» son modelos; si no, marcas.
                $clase = in_array('modelo', $encabezados, true) ? 'modelo' : 'marca';
                $valores = [];

                foreach ($this->columnas($clase) as $clave => $definicion) {
                    $indice = array_search($this->normalizar($definicion[0]), $encabezados, true);
                    $valores[$clave] = $indice === false ? null : (string) ($fila[$indice] ?? '');
                }

                $filas[] = [
                    'clase' => $clase,
                    'etiqueta' => ($titulo === 'Archivo' ? 'Fila ' : $titulo.' · fila ').($n + 1),
                    'valores' => $valores,
                ];
            }
        }

        // Marcas primero: los modelos pueden usar marcas de este archivo.
        usort($filas, fn ($a, $b) => ($a['clase'] === 'marca' ? 0 : 1) <=> ($b['clase'] === 'marca' ? 0 : 1));

        return $filas;
    }

    private function leerCsv(string $contenido): array
    {
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido) ?? $contenido;

        if (! mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
        }

        $primera = preg_split('/\r\n|\r|\n/', $contenido, 2)[0] ?? '';
        $separador = collect([',', ';', "\t"])->sortByDesc(fn ($s) => substr_count($primera, $s))->first();

        $flujo = fopen('php://temp', 'r+');
        fwrite($flujo, $contenido);
        rewind($flujo);

        $filas = [];

        while (($fila = fgetcsv($flujo, 0, $separador, '"', '\\')) !== false) {
            $filas[] = $fila === [null] ? [] : $fila;
        }

        fclose($flujo);

        return $filas;
    }

    /*
    |--------------------------------------------------------------------------
    | Revisar las filas
    |--------------------------------------------------------------------------
    | Cada fila queda con:
    |  - id (registro existente o null), codigo
    |  - datos: columnas a guardar (solo celdas con valor)
    |  - nuevo: estado/mensajes si es un registro nuevo
    |  - actualizando / sinActualizar: las dos versiones si ya existe
    */

    public function revisar(array $filas): array
    {
        $this->memo = [];
        $marcasArchivo = [];
        $vistos = [];
        $salida = [];

        foreach ($filas as $numero => $fila) {
            $clase = $fila['clase'];
            $v = $fila['valores'];
            $errores = [];
            $datos = [];

            foreach ($this->columnas($clase) as $clave => $definicion) {
                $texto = trim((string) ($v[$clave] ?? ''));

                if ($texto === '' || $clave === 'codigo') {
                    continue;
                }

                [$valor, $error] = $this->convertir($clave, $definicion, $texto, $marcasArchivo);

                if ($error !== null) {
                    $errores[] = $error;
                } else {
                    $datos[$clave] = $valor;
                }
            }

            // ¿Ya existe? Por código o por nombre.
            $codigo = trim((string) ($v['codigo'] ?? ''));
            $tabla = $clase === 'marca' ? 'marcas' : 'modelos';
            $llave = $clase === 'marca' ? 'id_marca' : 'id_modelo';
            $existente = null;

            if ($codigo !== '') {
                $existente = DB::table($tabla)->whereRaw('LOWER(codigo_interno) = ?', [mb_strtolower($codigo)])->first();

                if (! $existente) {
                    $errores[] = "No hay ".($clase === 'marca' ? 'una marca' : 'un modelo')." con el código «{$codigo}». Para crear uno nuevo, deja vacío el código.";
                }
            } elseif (isset($datos['nombre']) && ($clase === 'marca' || (isset($datos['id_marca']) && is_int($datos['id_marca']) && isset($datos['id_tipo_equipo'])))) {
                $consulta = DB::table($tabla)->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$datos['nombre']]);

                if ($clase === 'modelo') {
                    $consulta->where('id_marca', $datos['id_marca'])->where('id_tipo_equipo', $datos['id_tipo_equipo']);
                }

                $existente = $consulta->first();
            }

            // Repetido dentro del archivo.
            $clave = $clase.'|'.($existente ? 'id'.$existente->{$llave} : $this->normalizar(($datos['nombre'] ?? '').'|'.json_encode($datos['id_marca'] ?? '').'|'.($datos['id_tipo_equipo'] ?? '')));

            if (isset($vistos[$clave])) {
                $errores[] = 'Se repite en '.$vistos[$clave].'.';
            } else {
                $vistos[$clave] = $fila['etiqueta'];
            }

            $base = [
                'numero' => $numero,
                'clase' => $clase,
                'etiqueta' => $fila['etiqueta'],
                'nombre' => (string) ($v['nombre'] ?? '') ?: ($existente->nombre ?? ''),
                'detalle' => $clase === 'modelo'
                    ? trim(($v['id_marca'] ?? '').' · '.($v['id_tipo_equipo'] ?? ''), ' ·')
                    : '',
                'id' => $existente ? (int) $existente->{$llave} : null,
                'codigo' => $existente ? (string) ($existente->codigo_interno ?? '') : '',
                'datos' => $datos,
                'cambios' => [],
            ];

            if (! $existente) {
                foreach ($this->columnas($clase) as $c => $definicion) {
                    if ($definicion[1] && ! array_key_exists($c, $datos) && trim((string) ($v[$c] ?? '')) === '') {
                        $errores[] = "Falta «{$definicion[0]}».";
                    }
                }

                if ($clase === 'marca' && isset($datos['nombre']) && $errores === []) {
                    $marcasArchivo[$this->normalizar($datos['nombre'])] = $datos['nombre'];
                }

                $salida[] = $base + [
                    'existe' => false,
                    'estado' => $errores === [] ? 'nueva' : 'error',
                    'mensajes' => array_values(array_unique($errores)),
                ];

                continue;
            }

            $actuales = $this->textos($clase, $existente);
            $cambios = [];

            foreach ($this->columnas($clase) as $c => $definicion) {
                $nuevo = trim((string) ($v[$c] ?? ''));

                if ($c === 'codigo' || $nuevo === '' || ! array_key_exists($c, $datos)) {
                    continue;
                }

                if ($this->comparable($definicion[2], $nuevo) !== $this->comparable($definicion[2], $actuales[$c] ?? '')) {
                    $cambios[] = ['clave' => $c, 'etiqueta' => $definicion[0], 'antes' => $actuales[$c] ?? '', 'despues' => $nuevo];
                }
            }

            // Otro registro ya tiene ese nombre (al renombrar por código).
            if (collect($cambios)->contains('clave', 'nombre') || collect($cambios)->contains('clave', 'id_marca') || collect($cambios)->contains('clave', 'id_tipo_equipo')) {
                $consulta = DB::table($tabla)->where($llave, '!=', $base['id'])
                    ->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$datos['nombre'] ?? $existente->nombre]);

                if ($clase === 'modelo') {
                    $consulta->where('id_marca', is_int($datos['id_marca'] ?? null) ? $datos['id_marca'] : $existente->id_marca)
                        ->where('id_tipo_equipo', $datos['id_tipo_equipo'] ?? $existente->id_tipo_equipo);
                }

                if ($consulta->exists()) {
                    $errores[] = 'Ya existe otro registro con ese nombre.';
                }
            }

            $salida[] = $base + [
                'existe' => true,
                'cambios' => $cambios,
                'sinActualizar' => [
                    'estado' => 'error',
                    'mensajes' => array_values(array_unique(array_merge(
                        array_filter($errores, fn ($m) => ! str_starts_with($m, 'Falta «')),
                        ["Ya existe ({$base['codigo']}). Activa «Actualizar los que ya existen» para aplicar sus cambios."]
                    ))),
                ],
                'actualizando' => [
                    'estado' => $errores !== [] ? 'error' : ($cambios === [] ? 'sin_cambios' : 'actualizar'),
                    'mensajes' => array_values(array_unique($errores)),
                ],
            ];
        }

        return $salida;
    }

    private function convertir(string $clave, array $definicion, string $texto, array $marcasArchivo): array
    {
        switch ($definicion[2]) {
            case 'lista':
                $id = $this->buscarEnLista($clave, $texto);

                if ($id !== null) {
                    return [$id, null];
                }

                // Marca nueva de este mismo archivo (se guarda antes).
                if ($clave === 'id_marca' && isset($marcasArchivo[$this->normalizar($texto)])) {
                    return ['nueva:'.$marcasArchivo[$this->normalizar($texto)], null];
                }

                $opciones = $this->lista($clave);
                $sugerencia = count($opciones) <= 12 ? ' (opciones: '.implode(', ', $opciones).')' : '';

                return [null, $clave === 'id_marca'
                    ? "La marca «{$texto}» no existe. Agrégala en la hoja «Marcas» de este archivo o créala antes."
                    : "«{$texto}» no existe en {$definicion[0]}{$sugerencia}."];

            case 'numero':
                $numero = str_replace([',', ' '], ['', ''], $texto);

                return ctype_digit($numero) && (int) $numero >= 1 && (int) $numero <= 600
                    ? [(int) $numero, null]
                    : [null, "{$definicion[0]}: escribe un número de meses entre 1 y 600."];

            case 'url':
                $url = preg_match('#^https?://#i', $texto) ? $texto : 'https://'.$texto;

                return filter_var($url, FILTER_VALIDATE_URL) && mb_strlen($url) <= 500
                    ? [$url, null]
                    : [null, "«{$texto}» no es una dirección web válida."];

            case 'estado':
                $t = $this->normalizar($texto);

                return match (true) {
                    in_array($t, ['activo', 'activa', 'si', '1'], true) => [true, null],
                    in_array($t, ['inactivo', 'inactiva', 'no', '0'], true) => [false, null],
                    default => [null, "Estado: escribe «Activo» o «Inactivo»."],
                };

            default:
                $max = $clave === 'nombre' ? 255 : ($clave === 'descripcion' ? 1000 : 5000);

                return mb_strlen($texto) <= $max
                    ? [$texto, null]
                    : [null, "{$definicion[0]}: máximo {$max} caracteres."];
        }
    }

    private function comparable(string $tipo, string $texto): string
    {
        $t = $this->normalizar($texto);

        return match ($tipo) {
            'url' => preg_replace('#^https?://#', '', rtrim($t, '/')) ?? $t,
            'estado' => in_array($t, ['activo', 'activa', 'si', '1'], true) ? 'activo' : (in_array($t, ['inactivo', 'inactiva', 'no', '0'], true) ? 'inactivo' : $t),
            'numero' => ltrim(str_replace([',', ' '], '', $t), '0'),
            default => $t,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar una fila
    |--------------------------------------------------------------------------
    */

    public function guardar(array $fila, string $origen): string
    {
        $clase = $fila['clase'];
        $tabla = $clase === 'marca' ? 'marcas' : 'modelos';
        $llave = $clase === 'marca' ? 'id_marca' : 'id_modelo';
        $datos = $fila['datos'];

        // Marca nueva de este archivo: ya se guardó (las marcas van primero).
        if (is_string($datos['id_marca'] ?? null) && str_starts_with($datos['id_marca'], 'nueva:')) {
            $nombreMarca = substr($datos['id_marca'], 6);
            $datos['id_marca'] = DB::table('marcas')->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$nombreMarca])->value('id_marca');

            if (! $datos['id_marca']) {
                throw ValidationException::withMessages(['id_marca' => "La marca «{$nombreMarca}» no se pudo crear."]);
            }
        }

        return DB::transaction(function () use ($fila, $clase, $tabla, $llave, $datos, $origen) {
            if ($fila['id'] === null) {
                $duplicado = DB::table($tabla)->whereRaw('LOWER(BTRIM(nombre)) = LOWER(BTRIM(?))', [$datos['nombre']]);

                if ($clase === 'modelo') {
                    $duplicado->where('id_marca', $datos['id_marca'])->where('id_tipo_equipo', $datos['id_tipo_equipo']);
                }

                if ($duplicado->exists()) {
                    throw ValidationException::withMessages(['nombre' => 'Ya existe; alguien lo dio de alta mientras tanto.']);
                }

                $id = DB::table($tabla)->insertGetId(array_merge(
                    ['activo' => true],
                    $datos,
                    ['fecha_creacion' => now(), 'fecha_actualizacion' => now()]
                ), $llave);

                $codigo = ($clase === 'marca' ? 'MAR-' : 'MOD-').str_pad((string) $id, 6, '0', STR_PAD_LEFT);
                DB::table($tabla)->where($llave, $id)->update(['codigo_interno' => $codigo]);

                $this->auditar($clase === 'marca' ? 'MARCA_CREADA' : 'MODELO_CREADO', 'Se creó '.($clase === 'marca' ? 'la marca ' : 'el modelo ').$datos['nombre']." ({$codigo}) al importar desde {$origen}.");

                return $codigo;
            }

            $cambios = array_column($fila['cambios'], 'clave');
            $actualizar = array_intersect_key($datos, array_flip($cambios));

            if ($actualizar !== []) {
                DB::table($tabla)->where($llave, $fila['id'])->update($actualizar + ['fecha_actualizacion' => now()]);
                $this->auditar($clase === 'marca' ? 'MARCA_ACTUALIZADA' : 'MODELO_ACTUALIZADO', 'Se actualizó '.($clase === 'marca' ? 'la marca ' : 'el modelo ')."{$fila['codigo']} al importar desde {$origen} (".implode(', ', array_column($fila['cambios'], 'etiqueta')).').');
            }

            return (string) $fila['codigo'];
        });
    }

    private function auditar(string $accion, string $descripcion): void
    {
        DB::table('bitacora_auditoria')->insert([
            'id_usuario' => auth()->id(),
            'accion' => $accion,
            'modulo' => 'CATALOGOS',
            'descripcion' => $descripcion,
            'fecha_hora' => now(),
        ]);
    }

    // Tablas de Marcas/Modelos y formularios de equipos.
    public function refrescarCaches(): void
    {
        app(SystemCacheService::class)->refreshModule('catalogos');
        Cache::forget('form.marcas.v2');
        Cache::forget('form.modelos.all.v2');
    }
}
