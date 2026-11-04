<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/*
|--------------------------------------------------------------------------
| Equipos en CSV: plantilla, lectura, importación y exportación
|--------------------------------------------------------------------------
|
| Vive en EquipoCreate (y en EquiposImportar, que lo hereda) para usar la
| misma configuración de formularios por tipo y el mismo guardado
| (guardarEquipo): un equipo importado pasa por las mismas validaciones
| que uno capturado a mano.
|
| Columnas: «Tipo de equipo» + campos generales + campos del tipo, con
| los nombres que ve el usuario (las obligatorias llevan « *»). Las
| listas se escriben con su texto (p. ej. «Disponible», «Dell»); al
| importar se convierten a su valor. Fechas: dd/mm/aaaa o aaaa-mm-dd.
| Sí/No: Sí, No, 1, 0. Separador: coma, punto y coma o tabulador.
|
*/

trait EquiposCsv
{
    // Mientras se importa no se manda un aviso por cada equipo.
    protected bool $importandoCsv = false;

    /*
     * Memoria de la petición: listas y columnas se consultan una sola vez
     * aunque el archivo tenga cientos de filas (antes cada fila repetía
     * las consultas a la base remota y 100 filas tardaban más de un minuto).
     */
    protected array $csvMemoria = [];

    /*
     * Marca o modelo que faltan en el catálogo en la última fila leída
     * (csvFilaAPayload): la importación ofrece crearlos.
     * [['clase' => 'marca'|'modelo', 'marca' => texto, 'modelo' => texto,
     *   'tipo' => id, 'columna' => etiqueta de la columna]]
     */
    protected array $csvFaltantesFila = [];

    protected function csvMemo(string $clave, callable $calcular): mixed
    {
        if (! array_key_exists($clave, $this->csvMemoria)) {
            $this->csvMemoria[$clave] = $calcular();
        }

        return $this->csvMemoria[$clave];
    }

    /*
     * Campos generales: clave de configuración => [modelo del guardado,
     * etiqueta, tipo, obligatorio].
     */
    protected function csvBase(): array
    {
        $unidad = app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'];

        return [
            'nombre_equipo' => ['nombreEquipo', 'Nombre del equipo', 'text', true],
            'id_estado_activo' => ['idEstadoActivo', 'Estado', 'select', true],
            'id_marca' => ['idMarca', 'Marca', 'select', true],
            'id_modelo' => ['idModelo', 'Modelo', 'select', false],
            'numero_serie' => ['numeroSerie', 'Número de serie', 'text', true],
            'numero_factura' => ['numeroFactura', 'Número de factura', 'text', true],
            'id_unidad' => ['idHotel', $unidad, 'select', true],
            'id_departamento' => ['idDepartamento', 'Departamento', 'select', false],
            'id_ubicacion' => ['idUbicacion', 'Ubicación', 'select', false],
            'id_proveedor' => ['idProveedor', 'Proveedor', 'select', false],
            'id_condicion_activo' => ['idCondicionActivo', 'Condición', 'select', false],
            'propietario' => ['propietario', 'Responsable', 'text', false],
            'host' => ['host', 'Host', 'text', false],
            'direccion_mac' => ['direccionMac', 'Dirección MAC', 'text', false],
            'fecha_compra' => ['fechaCompra', 'Fecha de compra', 'date', false],
            'fecha_fin_garantia' => ['fechaFinGarantia', 'Vigencia de garantía', 'date', false],
            'comentarios' => ['comentarios', 'Comentarios', 'text', false],
        ];
    }

    /*
     * Columnas de un tipo: las generales que su formulario usa (las
     * obligatorias y Modelo siempre) y después las propias del tipo.
     */
    public function csvColumnas(int $tipoId): array
    {
        return $this->csvMemo('columnas.'.$tipoId, fn () => $this->csvColumnasSinMemoria($tipoId));
    }

    protected function csvColumnasSinMemoria(int $tipoId): array
    {
        $configurados = $this->csvMemo('base', fn () => $this->getConfiguredBaseFields())[$tipoId] ?? [];
        $columnas = [];

        foreach ($this->csvBase() as $clave => [$modelo, $etiqueta, $tipo, $obligatorio]) {
            // Modelo y Responsable siempre (Responsable crea la asignación al importar).
            if (! $obligatorio && ! in_array($clave, ['id_modelo', 'propietario'], true) && ! array_key_exists($clave, $configurados)) {
                continue;
            }

            $columnas[] = [
                'origen' => 'base',
                'clave' => $clave,
                'modelo' => $modelo,
                'etiqueta' => $etiqueta,
                'tipo' => $tipo,
                'obligatorio' => $obligatorio,
            ];
        }

        foreach ($this->configuredChildFields($tipoId) as $key => $campo) {
            $columnas[] = [
                'origen' => 'tipo',
                'clave' => $key,
                'modelo' => $key,
                'etiqueta' => (string) $campo['etiqueta'],
                'tipo' => (string) $campo['tipo_control'],
                'obligatorio' => (bool) $campo['obligatorio'],
                'campo' => $campo,
            ];
        }

        return $columnas;
    }

    protected function csvEncabezado(array $columna): string
    {
        return $columna['etiqueta'].($columna['obligatorio'] ? ' *' : '');
    }

    protected function csvNormalizar(?string $texto): string
    {
        $texto = Str::ascii(mb_strtolower(trim((string) $texto)));
        $texto = trim(str_replace('*', '', $texto));

        return preg_replace('/\s+/', ' ', $texto) ?? $texto;
    }

    /*
     * Plantilla de un tipo (Administración › Tipos de equipo): encabezados
     * y una fila con el tipo ya escrito para empezar a llenar.
     */
    public function plantillaCsv(int $tipoId): StreamedResponse
    {
        $tipo = DB::table('tipos_equipo')->where('id_tipo_equipo', $tipoId)->first(['nombre']);

        abort_unless($tipo, 404);

        $encabezados = array_merge(
            ['Tipo de equipo *'],
            array_map(fn (array $c) => $this->csvEncabezado($c), $this->csvColumnas($tipoId))
        );

        $ejemplo = array_merge([$tipo->nombre], array_fill(0, count($encabezados) - 1, ''));

        return $this->csvDescarga('plantilla-'.Str::slug($tipo->nombre).'.csv', [$encabezados, $ejemplo]);
    }

    /*
     * Plantilla en Excel (.xlsx): columnas del ancho de su encabezado,
     * encabezados en negritas (obligatorios resaltados), primera fila
     * fija y listas desplegables con las opciones válidas (hoja oculta
     * «Listas»). Se importa igual que el CSV.
     */
    public function plantillaXlsx(int $tipoId): StreamedResponse
    {
        $tipo = DB::table('tipos_equipo')->where('id_tipo_equipo', $tipoId)->first(['nombre']);

        abort_unless($tipo, 404);

        $columnas = array_merge(
            [['etiqueta' => 'Tipo de equipo', 'obligatorio' => true, 'origen' => 'tipo_equipo', 'tipo' => 'text', 'nombre_tipo' => $tipo->nombre]],
            $this->csvColumnas($tipoId)
        );

        $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle('Equipos');

        $listas = $libro->createSheet();
        $listas->setTitle('Listas');
        $columnaLista = 0;

        $filasConDatos = 500;

        foreach ($columnas as $i => $columna) {
            $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $encabezado = $columna['origen'] === 'tipo_equipo' ? 'Tipo de equipo *' : $this->csvEncabezado($columna);

            $hoja->setCellValue($letra.'1', $encabezado);
            $hoja->getColumnDimension($letra)->setWidth(max(14, mb_strlen($encabezado) + 4));
            $hoja->getStyle($letra.'1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $columna['obligatorio'] ? 'FFFFFF' : '1F2937']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $columna['obligatorio'] ? '2A78D6' : 'E5E7EB']],
                'alignment' => ['vertical' => 'center'],
            ]);

            // Fechas como texto dd/mm/aaaa (así Excel no las cambia de formato).
            if (($columna['tipo'] ?? '') === 'date') {
                $hoja->getStyle($letra.'2:'.$letra.$filasConDatos)->getNumberFormat()->setFormatCode('@');
            }

            $opciones = $this->csvOpcionesColumna($columna);

            if ($opciones === []) {
                continue;
            }

            $columnaLista++;
            $letraLista = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnaLista);
            $listas->setCellValue($letraLista.'1', $columna['etiqueta']);

            foreach (array_values($opciones) as $n => $opcion) {
                $listas->setCellValueExplicit($letraLista.($n + 2), $opcion, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $validacion = $hoja->getCell($letra.'2')->getDataValidation();
            $validacion->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                ->setAllowBlank(! $columna['obligatorio'])
                ->setShowDropDown(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Valor no válido')
                ->setError('Elige una opción de la lista.')
                ->setFormula1('Listas!$'.$letraLista.'$2:$'.$letraLista.'$'.(count($opciones) + 1));

            $hoja->setDataValidation($letra.'2:'.$letra.$filasConDatos, clone $validacion);
        }

        $hoja->setCellValue('A2', $tipo->nombre);

        // Si se deja vacío «Tipo de equipo» en otras filas, al importar se
        // usa el de la plantilla (ver leerArchivoTabla).
        $listas->setCellValue('Z1', 'tipo_plantilla');
        $listas->setCellValue('Z2', $tipo->nombre);
        $hoja->freezePane('A2');
        $hoja->getRowDimension(1)->setRowHeight(22);
        $listas->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        $libro->setActiveSheetIndex(0);

        $nombre = 'plantilla-'.Str::slug($tipo->nombre).'.xlsx';

        return response()->streamDownload(function () use ($libro) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro))->save('php://output');
        }, $nombre, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /*
    |--------------------------------------------------------------------------
    | Plantilla universal (todos los tipos de equipo)
    |--------------------------------------------------------------------------
    |
    | Una sola hoja para importar equipos de distintos tipos: las columnas
    | generales y, después, las de cada tipo. Las columnas con el mismo
    | nombre en varios tipos se comparten (sus opciones se juntan). Al
    | importar, cada fila solo usa las columnas de su tipo.
    |
    */
    protected function csvColumnasUniversales(): array
    {
        $tipos = $this->csvMemo('lista.tipos', fn () => $this->getTiposEquipoProperty());
        $columnas = [];

        $columnas['tipo de equipo'] = [
            'origen' => 'tipo_equipo', 'etiqueta' => 'Tipo de equipo', 'tipo' => 'text', 'obligatorio' => true,
            'opciones' => array_values(array_map(fn ($t) => (string) ((array) $t)['nombre'], $tipos)),
            'tipos' => [], 'general' => true,
        ];

        $generales = [];
        $propias = [];

        foreach ($tipos as $tipo) {
            $tipo = (array) $tipo;

            foreach ($this->csvColumnas((int) $tipo['id_tipo_equipo']) as $columna) {
                $clave = $this->csvNormalizar($columna['etiqueta']);
                $destino = $columna['origen'] === 'base' ? 'generales' : 'propias';

                // Mismo nombre que una general: se comparte esa columna.
                if ($destino === 'propias' && isset($generales[$clave])) {
                    $destino = 'generales';
                }

                $lista = &${$destino};
                $lista[$clave] ??= $columna + ['opciones' => [], 'tipos' => [], 'general' => $columna['origen'] === 'base'];
                $lista[$clave]['tipos'][(string) $tipo['nombre']] = (bool) $columna['obligatorio'];
                $lista[$clave]['opciones'] = array_values(array_unique(array_merge(
                    $lista[$clave]['opciones'],
                    $this->csvOpcionesColumna($columna)
                )));
                unset($lista);
            }
        }

        // Generales en el orden del formulario de alta.
        $orden = array_flip(array_map(fn ($b) => $this->csvNormalizar($b[1]), array_values($this->csvBase())));
        uksort($generales, fn ($a, $b) => ($orden[$a] ?? 999) <=> ($orden[$b] ?? 999));

        foreach ($generales as $clave => $columna) {
            // Obligatoria solo si lo es en todos los tipos que la usan.
            $columna['obligatorio'] = $columna['tipos'] !== [] && ! in_array(false, $columna['tipos'], true) && count($columna['tipos']) === count($tipos);
            $columnas[$clave] = $columna;
        }

        foreach ($propias as $clave => $columna) {
            $columna['obligatorio'] = false;
            $columnas[$clave] = $columna;
        }

        return array_values($columnas);
    }

    // Texto de ayuda de una columna propia: a qué tipos aplica.
    protected function csvAyudaColumna(array $columna): string
    {
        if ($columna['general'] || $columna['tipos'] === []) {
            return '';
        }

        $obligatorio = array_keys(array_filter($columna['tipos']));
        $texto = 'Solo para: '.implode(', ', array_keys($columna['tipos'])).'.';

        return $obligatorio === [] ? $texto : $texto.' Obligatoria en: '.implode(', ', $obligatorio).'.';
    }

    public function plantillaUniversalCsv(): StreamedResponse
    {
        $encabezados = array_map(
            fn (array $c) => $c['etiqueta'].($c['obligatorio'] ? ' *' : ''),
            $this->csvColumnasUniversales()
        );

        return $this->csvDescarga('plantilla-universal-equipos.csv', [$encabezados]);
    }

    /*
     * Plantilla universal en Excel: una hoja por tipo de equipo, cada una
     * solo con sus columnas (sin columna de tipo: lo da la hoja). La hoja
     * oculta «_tipos» relaciona cada hoja con su tipo para importarla.
     */
    public function plantillaUniversalXlsx(): StreamedResponse
    {
        $filasConDatos = 500;
        $libro = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $libro->removeSheetByIndex(0);

        $listas = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($libro, 'Listas');
        $mapa = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($libro, '_tipos');
        $libro->addSheet($listas);
        $libro->addSheet($mapa);
        $columnaLista = 0;
        $usados = [];
        $filaMapa = 1;
        $hojas = [];

        foreach ($this->csvMemo('lista.tipos', fn () => $this->getTiposEquipoProperty()) as $tipo) {
            $tipo = (array) $tipo;

            // Nombre de hoja válido en Excel (31 caracteres, sin : \ / ? * [ ]).
            $titulo = trim(mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', (string) $tipo['nombre']), 0, 31)) ?: 'Tipo';
            $base = $titulo;

            for ($n = 2; isset($usados[mb_strtolower($titulo)]); $n++) {
                $titulo = mb_substr($base, 0, 28).' '.$n;
            }

            $usados[mb_strtolower($titulo)] = true;

            $hoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($libro, $titulo);
            $libro->addSheet($hoja);
            $hojas[] = $hoja;

            $mapa->setCellValueExplicit('A'.$filaMapa, $titulo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $mapa->setCellValueExplicit('B'.$filaMapa, (string) $tipo['nombre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $filaMapa++;

            foreach (array_values($this->csvColumnas((int) $tipo['id_tipo_equipo'])) as $i => $columna) {
                $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $encabezado = $this->csvEncabezado($columna);
                $propia = $columna['origen'] === 'tipo';

                $hoja->setCellValue($letra.'1', $encabezado);
                $hoja->getColumnDimension($letra)->setWidth(max(14, mb_strlen($encabezado) + 4));
                $hoja->getStyle($letra.'1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $columna['obligatorio'] ? 'FFFFFF' : ($propia ? '7A4B00' : '1F2937')]],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $columna['obligatorio'] ? '2A78D6' : ($propia ? 'FCEFD6' : 'E5E7EB')]],
                    'alignment' => ['vertical' => 'center'],
                ]);

                if (($columna['tipo'] ?? '') === 'date') {
                    $hoja->getStyle($letra.'2:'.$letra.$filasConDatos)->getNumberFormat()->setFormatCode('@');
                }

                $opciones = $this->csvOpcionesColumna($columna);

                if ($opciones === []) {
                    continue;
                }

                $columnaLista++;
                $letraLista = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnaLista);
                $listas->setCellValue($letraLista.'1', $titulo.' · '.$columna['etiqueta']);

                foreach (array_values($opciones) as $n => $opcion) {
                    $listas->setCellValueExplicit($letraLista.($n + 2), $opcion, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }

                $validacion = $hoja->getCell($letra.'2')->getDataValidation();
                $validacion->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                    ->setAllowBlank(! $columna['obligatorio'])
                    ->setShowDropDown(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Valor no válido')
                    ->setError('Elige una opción de la lista.')
                    ->setFormula1('Listas!$'.$letraLista.'$2:$'.$letraLista.'$'.(count($opciones) + 1));

                $hoja->setDataValidation($letra.'2:'.$letra.$filasConDatos, clone $validacion);
            }

            $hoja->freezePane('A2');
            $hoja->getRowDimension(1)->setRowHeight(22);
        }

        $listas->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        $mapa->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        if ($hojas !== []) {
            $libro->setActiveSheetIndex($libro->getIndex($hojas[0]));
        }

        return response()->streamDownload(function () use ($libro) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($libro))->save('php://output');
        }, 'plantilla-universal-equipos.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /*
     * Excel con una hoja por tipo (plantilla universal): se juntan todas
     * en una sola tabla con «Tipo de equipo» tomado de la hoja.
     * => [encabezados, filas, etiquetas «Laptop · fila 3»]
     */
    protected function leerHojasPorTipo(\PhpOffice\PhpSpreadsheet\Spreadsheet $libro, array $mapa): array
    {
        $encabezados = ['Tipo de equipo *'];
        $indices = ['tipo de equipo' => 0];
        $porFila = [];
        $etiquetas = [];

        foreach ($mapa as $titulo => $tipo) {
            $hoja = $libro->getSheetByName($titulo);

            if (! $hoja) {
                continue;
            }

            $propios = null;

            foreach ($hoja->toArray(null, true, false, false) as $n => $fila) {
                $fila = array_map(fn ($v) => trim((string) ($v ?? '')), $fila);

                if (collect($fila)->every(fn ($v) => $v === '')) {
                    continue;
                }

                if ($propios === null) {
                    $propios = [];

                    foreach ($fila as $i => $encabezado) {
                        $clave = $this->csvNormalizar($encabezado);

                        if ($clave === '') {
                            continue;
                        }

                        if (! isset($indices[$clave])) {
                            $indices[$clave] = count($encabezados);
                            $encabezados[] = $encabezado;
                        }

                        $propios[$i] = $indices[$clave];
                    }

                    continue;
                }

                $valores = [0 => $tipo];

                foreach ($propios as $i => $destino) {
                    $valores[$destino] = $fila[$i] ?? '';
                }

                $porFila[] = $valores;
                $etiquetas[] = $titulo.' · fila '.($n + 1);
            }
        }

        $total = count($encabezados);
        $filas = array_map(function (array $valores) use ($total) {
            $fila = array_fill(0, $total, '');

            foreach ($valores as $i => $v) {
                $fila[$i] = $v;
            }

            return $fila;
        }, $porFila);

        return [$encabezados, $filas, $etiquetas];
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar equipos existentes desde el archivo
    |--------------------------------------------------------------------------
    |
    | Una fila corresponde a un equipo existente por su «Código de
    | inventario» (columna del CSV exportado) o, si no la trae, por su
    | número de serie. Solo se aplican las celdas con valor que cambian:
    | una celda vacía conserva el dato actual.
    |
    */

    /*
     * Valores actuales de los equipos como texto (igual que en el CSV
     * exportado), por etiqueta normalizada.
     * [id => ['equipo', 'baja', 'textos' => [...], 'responsable' => ?array]]
     */
    protected function csvDatosActuales(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return [];
        }

        $equipos = DB::table('equipos')->whereIn('id_equipo', $ids)->get()->keyBy('id_equipo');
        $atributos = DB::table('atributos_equipo')->whereIn('id_equipo', $ids)->whereNotNull('id_campo')->get(['id_equipo', 'id_campo', 'valor'])->groupBy('id_equipo');
        $responsables = DB::table('asignaciones')->whereIn('id_equipo', $ids)->whereNull('fecha_fin')
            ->get(['id_equipo', 'nombre_colaborador', ...(app(\App\Services\ColaboradoresService::class)->disponible() ? ['id_usuario'] : [])])
            ->keyBy('id_equipo');
        $bajas = DB::table('bajas')->whereIn('id_equipo', $ids)->pluck('id_equipo')->map(fn ($id) => (int) $id)->flip();
        $estadoBaja = DB::table('estados_equipo')->whereRaw('UPPER(clave) = ?', ['BAJA'])->value('id_estado_equipo');

        $nombres = fn (array $lista, string $id) => collect($lista)
            ->mapWithKeys(fn ($o) => [(string) ((array) $o)[$id] => (string) (((array) $o)['nombre'] ?? '')])
            ->all();

        $catalogos = [
            'id_estado_activo' => $nombres($this->csvMemo('lista.estados', fn () => $this->getEstadosProperty()), 'id_estado_equipo'),
            'id_marca' => $nombres($this->csvMemo('lista.marcas', fn () => $this->getMarcasProperty()), 'id_marca'),
            'id_modelo' => $nombres($this->csvMemo('lista.modelos', fn () => $this->getAllModelos()), 'id_modelo'),
            'id_unidad' => $nombres($this->csvMemo('lista.hoteles', fn () => $this->getHotelesProperty()), 'id_unidad'),
            'id_departamento' => $nombres($this->csvMemo('lista.departamentos', fn () => $this->getAllDepartamentos()), 'id_departamento'),
            'id_proveedor' => $nombres($this->csvMemo('lista.proveedores', fn () => $this->getProveedoresProperty()), 'id_proveedor'),
            'id_condicion_activo' => $nombres($this->csvMemo('lista.condiciones', fn () => $this->getCondicionesProperty()), 'id_valor'),
            'id_ubicacion' => collect($this->csvMemo('lista.ubicaciones', fn () => app(\App\Services\UbicacionOrganizacionalService::class)->ubicaciones()))->pluck('label', 'value')->all(),
        ];

        $fisicas = [];
        $salida = [];

        foreach ($equipos as $id => $equipo) {
            $tipoId = (int) $equipo->id_tipo_equipo;
            $propios = $atributos->get($id, collect())->pluck('valor', 'id_campo');
            $responsable = $responsables->get($id);
            $textos = [];

            foreach ($this->csvColumnas($tipoId) as $c) {
                $clave = $this->csvNormalizar($c['etiqueta']);

                if ($c['origen'] === 'base') {
                    $valor = $c['clave'] === 'propietario' ? ($responsable->nombre_colaborador ?? '') : ($equipo->{$c['clave']} ?? '');

                    if (isset($catalogos[$c['clave']])) {
                        $valor = $catalogos[$c['clave']][(string) $valor] ?? '';
                    } elseif ($c['tipo'] === 'date' && filled($valor)) {
                        $valor = rescue(fn () => Carbon::parse($valor)->format('d/m/Y'), (string) $valor, false);
                    }

                    $textos[$clave] = (string) $valor;

                    continue;
                }

                $campo = $c['campo'];
                $tabla = (string) ($campo['tabla_destino'] ?? '');

                if ($campo['origen_dato'] === 'tabla_tipo' && $tabla !== '' && ! isset($fisicas[$tabla])) {
                    $fisicas[$tabla] = DB::table($tabla)->whereIn('id_equipo', $ids)->get()->keyBy('id_equipo');
                }

                $valor = $campo['origen_dato'] === 'atributo'
                    ? ($propios[$campo['id_campo']] ?? '')
                    : (($fisicas[$tabla] ?? collect())->get($id)?->{$campo['columna_destino']} ?? '');

                if ($valor !== '' && $valor !== null) {
                    $valor = match ($c['tipo']) {
                        'select' => collect($this->fieldOptions($campo))->firstWhere('value', (string) $valor)['label'] ?? $valor,
                        'boolean', 'checkbox' => filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No',
                        'date' => rescue(fn () => Carbon::parse($valor)->format('d/m/Y'), (string) $valor, false),
                        default => $valor,
                    };
                }

                $textos[$clave] = (string) $valor;
            }

            $salida[(int) $id] = [
                'equipo' => $equipo,
                'baja' => $bajas->has((int) $id) || ($estadoBaja !== null && (int) $equipo->id_estado_activo === (int) $estadoBaja),
                'textos' => $textos,
                'responsable' => $responsable ? [
                    'nombre' => (string) $responsable->nombre_colaborador,
                    'id_usuario' => isset($responsable->id_usuario) && $responsable->id_usuario !== null ? (int) $responsable->id_usuario : null,
                ] : null,
            ];
        }

        return $salida;
    }

    // Texto comparable de una celda o de un valor actual.
    protected function csvComparable(array $columna, string $texto): string
    {
        $t = $this->csvNormalizar($texto);

        if ($t === '') {
            return '';
        }

        if (($columna['tipo'] ?? '') === 'date') {
            return (string) ($this->csvFecha($texto) ?? $t);
        }

        if (in_array($columna['tipo'] ?? '', ['boolean', 'checkbox'], true)) {
            return match (true) {
                in_array($t, ['si', '1', 'true', 'x', 'yes'], true) => 'si',
                in_array($t, ['no', '0', 'false'], true) => 'no',
                default => $t,
            };
        }

        $numero = str_replace(',', '.', $t);

        return is_numeric($numero) ? (string) (float) $numero : $t;
    }

    /*
     * Celdas con valor que cambian respecto al equipo actual.
     * => [cambios [[etiqueta, antes, despues, clave, origen]], avisos [texto]]
     */
    protected function csvCambios(array $encabezados, array $fila, int $tipoId, array $actual, array $payload): array
    {
        $porColumna = [];

        foreach ($encabezados as $i => $encabezado) {
            $porColumna[$this->csvNormalizar($encabezado)] = trim((string) ($fila[$i] ?? ''));
        }

        $cambios = [];
        $avisos = [];

        foreach ($this->csvColumnas($tipoId) as $c) {
            $norma = $this->csvNormalizar($c['etiqueta']);
            $nuevo = $porColumna[$norma] ?? '';

            if ($nuevo === '') {
                continue; // vacía: se conserva lo actual
            }

            $antes = (string) ($actual['textos'][$norma] ?? '');

            if ($c['origen'] === 'base' && $c['clave'] === 'propietario') {
                $elegido = (string) ($payload['propietario'] ?? '');
                $responsable = $actual['responsable'];

                if ($responsable === null) {
                    $cambios[] = ['etiqueta' => $c['etiqueta'], 'antes' => '', 'despues' => $nuevo, 'clave' => 'propietario', 'origen' => 'base'];

                    continue;
                }

                $mismo = ctype_digit($elegido) && $responsable['id_usuario'] !== null
                    ? (int) $elegido === $responsable['id_usuario']
                    : $this->csvNormalizar($nuevo) === $this->csvNormalizar($responsable['nombre']);

                if (! $mismo) {
                    $avisos[] = "Ya está asignado a {$responsable['nombre']}: el responsable no se cambia desde el archivo (usa Reasignación).";
                }

                continue;
            }

            if ($this->csvComparable($c, $nuevo) !== $this->csvComparable($c, $antes)) {
                $cambios[] = ['etiqueta' => $c['etiqueta'], 'antes' => $antes, 'despues' => $nuevo, 'clave' => $c['clave'], 'origen' => $c['origen']];
            }
        }

        return [$cambios, $avisos];
    }

    /*
     * Aplica los cambios de una fila a un equipo existente.
     */
    protected function csvActualizarEquipo(int $idEquipo, array $payload, array $cambios): array
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        $claves = collect($cambios)->groupBy('origen')->map(fn ($g) => $g->pluck('clave')->all());
        $base = $claves->get('base', []);
        $propias = $claves->get('tipo', []);

        return DB::transaction(function () use ($idEquipo, $payload, $base, $propias) {
            $equipo = DB::table('equipos')->where('id_equipo', $idEquipo)->lockForUpdate()->first();

            if (! $equipo) {
                throw \Illuminate\Validation\ValidationException::withMessages(['fila' => 'El equipo ya no existe.']);
            }

            if (DB::table('bajas')->where('id_equipo', $idEquipo)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['fila' => 'El equipo está dado de baja; no se actualiza.']);
            }

            $columnas = [
                'nombre_equipo' => 'nombreEquipo', 'id_estado_activo' => 'idEstadoActivo', 'id_marca' => 'idMarca',
                'id_modelo' => 'idModelo', 'numero_serie' => 'numeroSerie', 'numero_factura' => 'numeroFactura',
                'id_proveedor' => 'idProveedor', 'id_condicion_activo' => 'idCondicionActivo', 'host' => 'host',
                'direccion_mac' => 'direccionMac', 'fecha_compra' => 'fechaCompra', 'fecha_fin_garantia' => 'fechaFinGarantia',
                'comentarios' => 'comentarios',
            ];
            $enteros = ['id_estado_activo', 'id_marca', 'id_modelo', 'id_proveedor', 'id_condicion_activo'];
            $datos = [];

            foreach ($base as $clave) {
                if (! isset($columnas[$clave])) {
                    continue;
                }

                $valor = $payload[$columnas[$clave]] ?? null;
                $datos[$clave] = in_array($clave, $enteros, true) ? (int) $valor : trim((string) $valor);
            }

            // Serie nueva: que no la tenga otro equipo.
            if (isset($datos['numero_serie'])) {
                $serie = mb_strtolower($datos['numero_serie']);
                DB::select('select pg_advisory_xact_lock(hashtext(?))', ['equipo-serie:' . $serie]);

                if (DB::table('equipos')->whereRaw('LOWER(numero_serie) = ?', [$serie])->where('id_equipo', '!=', $idEquipo)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'numeroSerie' => 'Otro equipo ya tiene el número de serie «' . $datos['numero_serie'] . '».',
                    ]);
                }
            }

            // Otra marca sin modelo nuevo: el modelo anterior ya no corresponde.
            if (isset($datos['id_marca']) && ! isset($datos['id_modelo']) && $equipo->id_modelo !== null) {
                $marcaModelo = DB::table('modelos')->where('id_modelo', $equipo->id_modelo)->value('id_marca');

                if ((int) $marcaModelo !== (int) $datos['id_marca']) {
                    $datos['id_modelo'] = null;
                }
            }

            // Organización: lo nuevo del archivo sobre lo actual, validado junto.
            if (array_intersect($base, ['id_unidad', 'id_departamento', 'id_ubicacion']) !== []) {
                $organizacion = $this->validarOrganizacion([
                    'idHotel' => in_array('id_unidad', $base, true) ? $payload['idHotel'] : $equipo->id_unidad,
                    'idDepartamento' => in_array('id_departamento', $base, true) ? $payload['idDepartamento'] : $equipo->id_departamento,
                    'idUbicacion' => in_array('id_ubicacion', $base, true) ? $payload['idUbicacion'] : ($equipo->id_ubicacion ?? null),
                ]);

                $datos['id_unidad'] = $organizacion['id_unidad'];
                $datos['id_departamento'] = $organizacion['id_departamento'];

                if (\App\Services\UbicacionOrganizacionalService::equiposTienenUbicacion()) {
                    $datos['id_ubicacion'] = $organizacion['id_ubicacion'];
                }
            }

            if ($datos !== [] || $propias !== []) {
                if (property_exists($equipo, 'fecha_actualizacion')) {
                    $datos['fecha_actualizacion'] = now();
                }

                DB::table('equipos')->where('id_equipo', $idEquipo)->update($datos);
            }

            // Campos propios del tipo.
            if ($propias !== []) {
                $tipoId = (int) $equipo->id_tipo_equipo;
                $definiciones = array_intersect_key($this->configuredChildFields($tipoId), array_flip($propias));
                $valores = $this->validatedChildData(
                    array_intersect_key($payload['childData'] ?? [], $definiciones),
                    $definiciones
                );
                $fisica = $this->childConfig()[$tipoId] ?? null;
                $fila = [];

                foreach ($definiciones as $clave => $campo) {
                    $valor = $valores[$clave];

                    if ($campo['origen_dato'] === 'tabla_tipo' && $fisica) {
                        $fila[$campo['columna_destino']] = $valor;

                        continue;
                    }

                    if ($campo['origen_dato'] === 'atributo' && $valor !== null) {
                        DB::table('atributos_equipo')->updateOrInsert(
                            ['id_equipo' => $idEquipo, 'id_campo' => (int) $campo['id_campo']],
                            ['nombre' => $campo['etiqueta'], 'valor' => is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor]
                        );
                    }
                }

                if ($fila !== []) {
                    if (! DB::table($fisica['table'])->where('id_equipo', $idEquipo)->exists()) {
                        foreach ($fisica['fields'] as $columna => $tipo) {
                            $fila[$columna] ??= $tipo === 'boolean' ? false : null;
                        }
                    }

                    DB::table($fisica['table'])->updateOrInsert(['id_equipo' => $idEquipo], $fila);
                }
            }

            // Responsable nuevo (el equipo no tenía): asignación inicial.
            if (in_array('propietario', $base, true) && filled($payload['propietario'] ?? null)) {
                $this->csvAsignarResponsable($idEquipo, (string) $payload['propietario']);
            }

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => auth()->id(),
                'accion' => 'EQUIPO_ACTUALIZADO_IMPORTACION',
                'modulo' => 'EQUIPOS',
                'descripcion' => 'Se actualizó el equipo ' . $equipo->codigo_inventario . ' desde una importación ('
                    . implode(', ', array_merge($base, $propias)) . ').',
                'fecha_hora' => now(),
            ]);

            return ['code' => (string) $equipo->codigo_inventario];
        });
    }

    protected function csvAsignarResponsable(int $idEquipo, string $seleccion): void
    {
        if (DB::table('asignaciones')->where('id_equipo', $idEquipo)->whereNull('fecha_fin')->exists()) {
            return;
        }

        $colaboradores = app(\App\Services\ColaboradoresService::class);
        $colaborador = $colaboradores->resolver($seleccion, $seleccion);

        if ($colaborador['nombre'] === '') {
            return;
        }

        $tipoAsignacion = DB::table('catalogo_valores')
            ->join('catalogos', 'catalogos.id_catalogo', '=', 'catalogo_valores.id_catalogo')
            ->where('catalogos.clave', 'tipo_asignacion')
            ->where('catalogo_valores.clave', 'ASIGNACION_INICIAL')
            ->value('catalogo_valores.id_valor');

        if (! $tipoAsignacion) {
            return;
        }

        $equipo = DB::table('equipos')->where('id_equipo', $idEquipo)->first(['id_unidad', 'id_departamento', 'id_ubicacion']);

        DB::table('asignaciones')->insert([
            'id_equipo' => $idEquipo,
            ...$colaboradores->columnasAsignacion($colaborador),
            'id_unidad' => $equipo->id_unidad,
            'id_departamento' => $equipo->id_departamento,
            'id_ubicacion' => $equipo->id_ubicacion ?? null,
            'id_tipo_asignacion' => $tipoAsignacion,
            'fecha_asignacion' => now(),
            'asignado_por' => auth()->id(),
        ]);

        $asignado = DB::table('estados_equipo')->whereRaw('LOWER(clave) = ?', ['asignado'])->value('id_estado_equipo');

        if ($asignado !== null) {
            DB::table('equipos')->where('id_equipo', $idEquipo)->update(['id_estado_activo' => $asignado]);
        }
    }

    /*
     * Textos válidos de una columna de lista (para los desplegables).
     */
    protected function csvOpcionesColumna(array $columna): array
    {
        $nombres = fn (array $lista, string $campo = 'nombre') => collect($lista)
            ->map(fn ($o) => (string) (((array) $o)[$campo] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($columna['origen'] === 'tipo_equipo') {
            // Solo el tipo de la plantilla: no se puede elegir otro.
            return [(string) ($columna['nombre_tipo'] ?? '')];
        }

        if ($columna['origen'] === 'tipo') {
            return match ($columna['tipo']) {
                'select' => $nombres($this->fieldOptions($columna['campo']), 'label'),
                'boolean', 'checkbox' => ['Sí', 'No'],
                default => [],
            };
        }

        return match ($columna['clave']) {
            'id_estado_activo' => $nombres($this->csvMemo('lista.estados', fn () => $this->getEstadosProperty())),
            'id_marca' => $nombres($this->csvMemo('lista.marcas', fn () => $this->getMarcasProperty())),
            'id_unidad' => $nombres($this->csvMemo('lista.hoteles', fn () => $this->getHotelesProperty())),
            'id_departamento' => $nombres($this->csvMemo('lista.departamentos', fn () => $this->getAllDepartamentos())),
            'id_proveedor' => $nombres($this->csvMemo('lista.proveedores', fn () => $this->getProveedoresProperty())),
            'id_condicion_activo' => $nombres($this->csvMemo('lista.condiciones', fn () => $this->getCondicionesProperty())),
            default => [],
        };
    }

    /*
     * Lee un archivo subido (.csv o .xlsx) => [encabezados, filas].
     */
    public function leerArchivoTabla(string $ruta, string $nombre): array
    {
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->csvLeer((string) file_get_contents($ruta));
        }

        $lector = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(false);
        $libro = $lector->load($ruta);

        // Plantilla universal por hojas (una por tipo de equipo).
        if ($hojaMapa = $libro->getSheetByName('_tipos')) {
            $mapa = [];

            foreach ($hojaMapa->toArray(null, true, false, false) as $par) {
                $titulo = trim((string) ($par[0] ?? ''));

                if ($titulo !== '') {
                    $mapa[$titulo] = trim((string) ($par[1] ?? ''));
                }
            }

            if ($mapa !== []) {
                return $this->leerHojasPorTipo($libro, $mapa);
            }
        }

        $hoja = $libro->getSheetByName('Equipos') ?? $libro->getSheet(0);
        $tipoPlantilla = trim((string) ($libro->getSheetByName('Listas')?->getCell('Z2')->getValue() ?? ''));

        $encabezados = null;
        $filas = [];

        foreach ($hoja->toArray(null, true, false, false) as $fila) {
            $fila = array_map(fn ($v) => trim((string) ($v ?? '')), $fila);

            if (collect($fila)->every(fn ($v) => $v === '')) {
                continue;
            }

            if ($encabezados === null) {
                $encabezados = $fila;

                continue;
            }

            // Plantilla de un solo tipo: el tipo vacío se completa solo.
            if ($tipoPlantilla !== '' && trim((string) ($fila[0] ?? '')) === '') {
                $fila[0] = $tipoPlantilla;
            }

            $filas[] = $fila;
        }

        return [$encabezados ?? [], $filas];
    }

    protected function csvDescarga(string $nombre, iterable $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');

            // BOM: Excel abre los acentos correctamente.
            fwrite($salida, "\xEF\xBB\xBF");

            foreach ($filas as $fila) {
                fputcsv($salida, $fila, ',', '"', '\\');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /*
     * Lee el texto de un CSV: detecta separador (, ; o tabulador) y
     * devuelve [encabezados, filas] (sin filas vacías).
     */
    public function csvLeer(string $contenido): array
    {
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido) ?? $contenido;

        if (! mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'Windows-1252');
        }

        $primera = preg_split('/\r\n|\r|\n/', $contenido, 2)[0] ?? '';
        $separador = collect([',', ';', "\t"])
            ->sortByDesc(fn ($s) => substr_count($primera, $s))
            ->first();

        $flujo = fopen('php://temp', 'r+');
        fwrite($flujo, $contenido);
        rewind($flujo);

        $encabezados = null;
        $filas = [];

        while (($fila = fgetcsv($flujo, 0, $separador, '"', '\\')) !== false) {
            if ($fila === [null] || collect($fila)->every(fn ($v) => trim((string) $v) === '')) {
                continue;
            }

            if ($encabezados === null) {
                $encabezados = array_map(fn ($v) => trim((string) $v), $fila);

                continue;
            }

            $filas[] = array_map(fn ($v) => trim((string) $v), $fila);
        }

        fclose($flujo);

        return [$encabezados ?? [], $filas];
    }

    /*
     * Una fila del CSV => [payload de guardarEquipo | null, errores, id del tipo].
     */
    public function csvFilaAPayload(array $encabezados, array $fila): array
    {
        $porColumna = [];

        foreach ($encabezados as $i => $encabezado) {
            $porColumna[$this->csvNormalizar($encabezado)] = $fila[$i] ?? '';
        }

        $tipoTexto = trim((string) ($porColumna['tipo de equipo'] ?? ''));
        $tipoId = $this->csvBuscar($tipoTexto, $this->csvMemo('lista.tipos', fn () => $this->getTiposEquipoProperty()), 'id_tipo_equipo');

        if ($tipoId === null) {
            return [null, [$tipoTexto === '' ? 'Falta «Tipo de equipo».' : "El tipo de equipo «{$tipoTexto}» no existe o está inactivo."], null];
        }

        $errores = [];
        $payload = ['idTipoEquipo' => (string) $tipoId, 'childData' => []];
        $this->csvFaltantesFila = [];
        $textoMarca = '';
        $marcaFalta = false;

        // Generales primero: Modelo y Ubicación dependen de Marca y Unidad.
        $columnas = collect($this->csvColumnas((int) $tipoId))
            ->sortBy(fn ($c) => $c['origen'] === 'base' ? 0 : 1)
            ->values();

        foreach ($columnas as $columna) {
            $texto = trim((string) ($porColumna[$this->csvNormalizar($columna['etiqueta'])] ?? ''));

            if ($texto === '') {
                if ($columna['obligatorio']) {
                    $errores[] = "Falta «{$columna['etiqueta']}».";
                }

                continue;
            }

            [$valor, $error] = $columna['origen'] === 'base'
                ? $this->csvValorBase($columna, $texto, $payload)
                : $this->csvValorTipo($columna, $texto);

            if ($columna['origen'] === 'base' && $columna['clave'] === 'id_marca') {
                $textoMarca = $texto;
                $marcaFalta = $error !== null;

                if ($marcaFalta) {
                    $this->csvFaltantesFila[] = ['clase' => 'marca', 'marca' => $texto, 'modelo' => '', 'tipo' => (int) $tipoId, 'columna' => $columna['etiqueta']];
                }
            }

            if ($columna['origen'] === 'base' && $columna['clave'] === 'id_modelo' && $error !== null && $textoMarca !== '') {
                $this->csvFaltantesFila[] = ['clase' => 'modelo', 'marca' => $textoMarca, 'modelo' => $texto, 'tipo' => (int) $tipoId, 'columna' => $columna['etiqueta']];

                // Sin marca, el modelo tampoco puede existir: un solo aviso.
                if ($marcaFalta) {
                    continue;
                }
            }

            /*
             * Valor que no está en la lista (Resolución «8K», Estado…): la
             * importación ofrece elegir uno válido o, en las listas propias
             * de un tipo, agregarlo como opción nueva.
             */
            if (
                $error !== null
                && $columna['tipo'] === 'select'
                && ! in_array($columna['clave'], ['id_marca', 'id_modelo', 'id_ubicacion'], true)
            ) {
                $propia = $columna['origen'] === 'tipo';
                $opciones = $this->csvOpcionesColumna($columna);

                if ($opciones !== []) {
                    $this->csvFaltantesFila[] = [
                        'clase' => 'opcion',
                        'clave' => 'opcion:'.($propia ? (int) $tipoId : 0).'|'.$this->csvNormalizar($columna['etiqueta']).'|'.$this->csvNormalizar($texto),
                        'valor' => $texto,
                        'marca' => '',
                        'modelo' => '',
                        'tipo' => (int) $tipoId,
                        'columna' => $columna['etiqueta'],
                        'propia' => $propia,
                        'opciones' => array_values($opciones),
                        // Solo las listas propias del tipo se pueden ampliar desde aquí.
                        'creable' => $propia && ($columna['campo']['fuente_opciones'] ?? '') === 'personalizado',
                        'idTipoCampo' => $propia ? (int) ($columna['campo']['id_tipo_campo'] ?? 0) : 0,
                    ];
                }
            }

            if ($error !== null) {
                $errores[] = $error;

                continue;
            }

            if ($columna['origen'] === 'base') {
                $payload[$columna['modelo']] = $valor;
            } else {
                $payload['childData'][$columna['modelo']] = $valor;
            }
        }

        return [$payload, $errores, (int) $tipoId];
    }

    protected function csvBuscar(string $texto, array $opciones, string $id, string $nombre = 'nombre'): ?string
    {
        $buscado = $this->csvNormalizar($texto);

        if ($buscado === '') {
            return null;
        }

        foreach ($opciones as $opcion) {
            $opcion = (array) $opcion;

            if ($this->csvNormalizar((string) ($opcion[$nombre] ?? '')) === $buscado) {
                return (string) $opcion[$id];
            }
        }

        return null;
    }

    protected function csvNoExiste(array $columna, string $texto, array $lista, string $nombre = 'nombre'): array
    {
        $opciones = collect($lista)->map(fn ($o) => (string) (((array) $o)[$nombre] ?? ''))->filter();

        return [null, "«{$texto}» no existe en {$columna['etiqueta']}"
            .($opciones->count() <= 12 ? ' (opciones: '.$opciones->implode(', ').').' : '.')];
    }

    protected function csvValorBase(array $columna, string $texto, array $payload): array
    {
        $catalogo = function (array $lista, string $id) use ($columna, $texto): array {
            $valor = $this->csvBuscar($texto, $lista, $id);

            return $valor !== null ? [$valor, null] : $this->csvNoExiste($columna, $texto, $lista);
        };

        switch ($columna['clave']) {
            case 'id_estado_activo':
                return $catalogo($this->csvMemo('lista.estados', fn () => $this->getEstadosProperty()), 'id_estado_equipo');

            case 'id_marca':
                return $catalogo($this->csvMemo('lista.marcas', fn () => $this->getMarcasProperty()), 'id_marca');

            case 'id_unidad':
                return $catalogo($this->csvMemo('lista.hoteles', fn () => $this->getHotelesProperty()), 'id_unidad');

            case 'id_departamento':
                return $catalogo($this->csvMemo('lista.departamentos', fn () => $this->getAllDepartamentos()), 'id_departamento');

            case 'id_proveedor':
                return $catalogo($this->csvMemo('lista.proveedores', fn () => $this->getProveedoresProperty()), 'id_proveedor');

            case 'id_condicion_activo':
                return $catalogo($this->csvMemo('lista.condiciones', fn () => $this->getCondicionesProperty()), 'id_valor');

            case 'id_modelo':
                $lista = array_values(array_filter(
                    $this->csvMemo('lista.modelos', fn () => $this->getAllModelos()),
                    fn ($m) => (string) $m['id_marca'] === (string) ($payload['idMarca'] ?? '')
                        && (string) $m['id_tipo_equipo'] === (string) ($payload['idTipoEquipo'] ?? '')
                ));
                $valor = $this->csvBuscar($texto, $lista, 'id_modelo');

                return $valor !== null
                    ? [$valor, null]
                    : [null, "El modelo «{$texto}» no existe para esa marca y tipo de equipo."];

            case 'id_ubicacion':
                $buscado = $this->csvNormalizar($texto);
                $coinciden = array_values(array_filter(
                    $this->csvMemo('lista.ubicaciones', fn () => app(\App\Services\UbicacionOrganizacionalService::class)->ubicaciones()),
                    fn ($u) => $this->csvNormalizar($u['label']) === $buscado
                ));
                $mismaUnidad = array_values(array_filter(
                    $coinciden,
                    fn ($u) => (string) $u['unidad'] === (string) ($payload['idHotel'] ?? '')
                ));
                $elegida = $mismaUnidad[0] ?? $coinciden[0] ?? null;

                return $elegida ? [(string) $elegida['value'], null] : [null, "La ubicación «{$texto}» no existe."];

            case 'propietario':
                // Usuario con cuenta (nombre, usuario, correo o número) o nombre sin cuenta.
                $buscado = $this->csvNormalizar($texto);

                foreach ($this->csvMemo('lista.usuarios', fn () => app(\App\Services\ColaboradoresService::class)->opcionesUsuarios()) as $usuario) {
                    $alias = array_merge(
                        [$usuario['nombre'] ?? '', $usuario['label'] ?? ''],
                        explode(' ', (string) ($usuario['search'] ?? ''))
                    );

                    foreach ($alias as $a) {
                        if ($a !== '' && $this->csvNormalizar($a) === $buscado) {
                            return [(string) $usuario['value'], null];
                        }
                    }
                }

                return [\App\Services\ColaboradoresService::PREFIJO_TEXTO.$texto, null];

            case 'fecha_compra':
            case 'fecha_fin_garantia':
                $fecha = $this->csvFecha($texto);

                return $fecha ? [$fecha, null] : [null, "«{$texto}» no es una fecha válida en {$columna['etiqueta']} (usa dd/mm/aaaa)."];

            default:
                return [$texto, null];
        }
    }

    protected function csvValorTipo(array $columna, string $texto): array
    {
        $campo = $columna['campo'];

        switch ($columna['tipo']) {
            case 'select':
                $opciones = $this->fieldOptions($campo);
                $valor = $this->csvBuscar($texto, $opciones, 'value', 'label');

                return $valor !== null
                    ? [$valor, null]
                    : [null, "«{$texto}» no es una opción de {$columna['etiqueta']} (opciones: ".collect($opciones)->pluck('label')->implode(', ').').'];

            case 'boolean':
            case 'checkbox':
                $t = $this->csvNormalizar($texto);

                if (in_array($t, ['si', 's', '1', 'true', 'verdadero', 'x'], true)) {
                    return [true, null];
                }

                if (in_array($t, ['no', 'n', '0', 'false', 'falso'], true)) {
                    return [false, null];
                }

                return [null, "«{$texto}» en {$columna['etiqueta']}: escribe Sí o No."];

            case 'date':
                $fecha = $this->csvFecha($texto);

                return $fecha ? [$fecha, null] : [null, "«{$texto}» no es una fecha válida en {$columna['etiqueta']} (usa dd/mm/aaaa)."];

            case 'number':
                $numero = str_replace([' ', ','], ['', '.'], $texto);

                return is_numeric($numero) ? [$numero, null] : [null, "«{$texto}» no es un número en {$columna['etiqueta']}."];

            default:
                return [$texto, null];
        }
    }

    protected function csvFecha(string $texto): ?string
    {
        $texto = trim($texto);

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'd.m.Y', 'j/n/Y'] as $formato) {
            try {
                $fecha = Carbon::createFromFormat('!'.$formato, $texto);

                if ($fecha && $fecha->format($formato) === $texto) {
                    return $fecha->format('Y-m-d');
                }
            } catch (\Throwable) {
            }
        }

        // Fecha numérica de Excel (días desde 1899-12-30).
        if (ctype_digit($texto) && (int) $texto > 20000 && (int) $texto < 80000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $texto)->format('Y-m-d');
        }

        return null;
    }

    /*
     * Alta de equipo › «Llenar desde CSV»: la primera fila se devuelve
     * como valores del formulario (igual que «Duplicar»).
     */
    #[\Livewire\Attributes\Json]
    public function leerCsvFormulario(string $contenido): array
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        [$encabezados, $filas] = $this->csvLeer(mb_substr($contenido, 0, 500000));

        return $this->primeraFilaFormulario($encabezados, $filas);
    }

    /*
     * Igual que leerCsvFormulario, para .xlsx (llega en base64).
     */
    #[\Livewire\Attributes\Json]
    public function leerArchivoFormulario(string $nombre, string $base64): array
    {
        \Illuminate\Support\Facades\Gate::authorize('gestionar');

        $contenido = base64_decode($base64, true);

        if ($contenido === false || strlen($contenido) > 2 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'No se pudo leer el archivo (máximo 2 MB).'];
        }

        $ruta = tempnam(sys_get_temp_dir(), 'trackit-');
        file_put_contents($ruta, $contenido);

        try {
            [$encabezados, $filas] = $this->leerArchivoTabla($ruta, $nombre);
        } catch (\Throwable $error) {
            report($error);

            return ['ok' => false, 'message' => 'El archivo no es un Excel o CSV válido.'];
        } finally {
            @unlink($ruta);
        }

        return $this->primeraFilaFormulario($encabezados, $filas);
    }

    protected function primeraFilaFormulario(array $encabezados, array $filas): array
    {
        if ($filas === []) {
            return ['ok' => false, 'message' => 'El archivo no tiene filas con datos (la primera fila son los encabezados).'];
        }

        [$payload, $errores] = $this->csvFilaAPayload($encabezados, $filas[0]);

        if ($payload === null) {
            return ['ok' => false, 'message' => $errores[0] ?? 'No se pudo leer la fila.'];
        }

        $childData = $payload['childData'];
        unset($payload['childData']);

        return [
            'ok' => true,
            'base' => $payload,
            'childData' => $childData,
            'errores' => $errores,
            'total' => count($filas),
        ];
    }

    /*
     * Inventario en CSV con el formato de importación: se puede editar en
     * Excel y volver a importar. Columnas = unión de las de cada tipo
     * presente (una misma etiqueta comparte columna).
     */
    public function exportarCsv(array $ids): StreamedResponse
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        $equipos = collect();
        $atributos = collect();
        $responsables = collect();

        foreach (array_chunk($ids, 500) as $lote) {
            $equipos = $equipos->merge(DB::table('equipos')->whereIn('id_equipo', $lote)->get());
            $atributos = $atributos->merge(
                DB::table('atributos_equipo')->whereIn('id_equipo', $lote)->whereNotNull('id_campo')->get(['id_equipo', 'id_campo', 'valor'])
            );
            $responsables = $responsables->merge(
                DB::table('asignaciones')->whereIn('id_equipo', $lote)->whereNull('fecha_fin')->get(['id_equipo', 'nombre_colaborador'])
            );
        }

        // Mismo orden en que llegaron (filtros y orden del inventario).
        $orden = array_flip($ids);
        $equipos = $equipos->sortBy(fn ($e) => $orden[(int) $e->id_equipo] ?? PHP_INT_MAX)->values();
        $atributos = $atributos->groupBy('id_equipo');
        $responsables = $responsables->pluck('nombre_colaborador', 'id_equipo');

        $nombres = fn (array $lista, string $id) => collect($lista)
            ->mapWithKeys(fn ($o) => [(string) ((array) $o)[$id] => (string) (((array) $o)['nombre'] ?? '')])
            ->all();

        $catalogos = [
            'id_estado_activo' => $nombres($this->csvMemo('lista.estados', fn () => $this->getEstadosProperty()), 'id_estado_equipo'),
            'id_marca' => $nombres($this->csvMemo('lista.marcas', fn () => $this->getMarcasProperty()), 'id_marca'),
            'id_modelo' => $nombres($this->csvMemo('lista.modelos', fn () => $this->getAllModelos()), 'id_modelo'),
            'id_unidad' => $nombres($this->csvMemo('lista.hoteles', fn () => $this->getHotelesProperty()), 'id_unidad'),
            'id_departamento' => $nombres($this->csvMemo('lista.departamentos', fn () => $this->getAllDepartamentos()), 'id_departamento'),
            'id_proveedor' => $nombres($this->csvMemo('lista.proveedores', fn () => $this->getProveedoresProperty()), 'id_proveedor'),
            'id_condicion_activo' => $nombres($this->csvMemo('lista.condiciones', fn () => $this->getCondicionesProperty()), 'id_valor'),
            'id_ubicacion' => collect($this->csvMemo('lista.ubicaciones', fn () => app(\App\Services\UbicacionOrganizacionalService::class)->ubicaciones()))->pluck('label', 'value')->all(),
        ];
        $tipos = $nombres($this->csvMemo('lista.tipos', fn () => $this->getTiposEquipoProperty()), 'id_tipo_equipo');

        $columnasPorTipo = [];
        // Código de inventario: al volver a importar, identifica al equipo.
        $encabezados = ['tipo de equipo' => 'Tipo de equipo *', 'codigo de inventario' => 'Código de inventario'];

        foreach ($equipos->pluck('id_tipo_equipo')->unique() as $tipoId) {
            try {
                $columnasPorTipo[(int) $tipoId] = $this->csvColumnas((int) $tipoId);
            } catch (\Throwable $error) {
                report($error);
                $columnasPorTipo[(int) $tipoId] = [];
            }

            foreach ($columnasPorTipo[(int) $tipoId] as $c) {
                $encabezados[$this->csvNormalizar($c['etiqueta'])] ??= $this->csvEncabezado($c);
            }
        }

        // Tablas propias de los tipos (laptops, impresoras…), una consulta cada una.
        $fisicas = [];

        foreach ($columnasPorTipo as $columnas) {
            foreach ($columnas as $c) {
                $tabla = (string) ($c['campo']['tabla_destino'] ?? '');

                if (($c['campo']['origen_dato'] ?? '') === 'tabla_tipo' && $tabla !== '' && ! isset($fisicas[$tabla])) {
                    $fisicas[$tabla] = DB::table($tabla)->whereIn('id_equipo', $ids)->get()->keyBy('id_equipo');
                }
            }
        }

        $filas = (function () use ($equipos, $columnasPorTipo, $encabezados, $catalogos, $tipos, $atributos, $responsables, $fisicas) {
            yield array_values($encabezados);

            foreach ($equipos as $equipo) {
                $tipoId = (int) $equipo->id_tipo_equipo;
                $fila = array_fill_keys(array_keys($encabezados), '');
                $fila['tipo de equipo'] = $tipos[(string) $tipoId] ?? '';
                $fila['codigo de inventario'] = (string) ($equipo->codigo_inventario ?? '');
                $propios = $atributos->get($equipo->id_equipo, collect())->pluck('valor', 'id_campo');

                foreach ($columnasPorTipo[$tipoId] ?? [] as $c) {
                    $clave = $this->csvNormalizar($c['etiqueta']);

                    if ($c['origen'] === 'base') {
                        $valor = $c['clave'] === 'propietario'
                            ? ($responsables[$equipo->id_equipo] ?? '')
                            : ($equipo->{$c['clave']} ?? '');

                        if (isset($catalogos[$c['clave']])) {
                            $valor = $catalogos[$c['clave']][(string) $valor] ?? '';
                        } elseif ($c['tipo'] === 'date' && filled($valor)) {
                            $valor = rescue(fn () => Carbon::parse($valor)->format('d/m/Y'), (string) $valor, false);
                        }

                        $fila[$clave] = (string) $valor;

                        continue;
                    }

                    $campo = $c['campo'];
                    $valor = $campo['origen_dato'] === 'atributo'
                        ? ($propios[$campo['id_campo']] ?? '')
                        : ($fisicas[(string) $campo['tabla_destino']] ?? collect())->get($equipo->id_equipo)?->{$campo['columna_destino']} ?? '';

                    if ($valor !== '' && $valor !== null) {
                        $valor = match ($c['tipo']) {
                            'select' => collect($this->fieldOptions($campo))->firstWhere('value', (string) $valor)['label'] ?? $valor,
                            'boolean', 'checkbox' => filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No',
                            'date' => rescue(fn () => Carbon::parse($valor)->format('d/m/Y'), (string) $valor, false),
                            default => $valor,
                        };
                    }

                    $fila[$clave] = (string) $valor;
                }

                yield array_values($fila);
            }
        })();

        return $this->csvDescarga('inventario-'.now()->format('Ymd-His').'.csv', $filas);
    }
}
