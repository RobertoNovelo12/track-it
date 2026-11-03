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
     * Campos generales: clave de configuración => [modelo del guardado,
     * etiqueta, tipo, obligatorio].
     */
    private function csvBase(): array
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
        $configurados = $this->getConfiguredBaseFields()[$tipoId] ?? [];
        $columnas = [];

        foreach ($this->csvBase() as $clave => [$modelo, $etiqueta, $tipo, $obligatorio]) {
            if (! $obligatorio && $clave !== 'id_modelo' && ! array_key_exists($clave, $configurados)) {
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

    private function csvEncabezado(array $columna): string
    {
        return $columna['etiqueta'].($columna['obligatorio'] ? ' *' : '');
    }

    private function csvNormalizar(?string $texto): string
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

    private function csvDescarga(string $nombre, iterable $filas): StreamedResponse
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
        $tipoId = $this->csvBuscar($tipoTexto, $this->getTiposEquipoProperty(), 'id_tipo_equipo');

        if ($tipoId === null) {
            return [null, [$tipoTexto === '' ? 'Falta «Tipo de equipo».' : "El tipo de equipo «{$tipoTexto}» no existe o está inactivo."], null];
        }

        $errores = [];
        $payload = ['idTipoEquipo' => (string) $tipoId, 'childData' => []];

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

    private function csvBuscar(string $texto, array $opciones, string $id, string $nombre = 'nombre'): ?string
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

    private function csvNoExiste(array $columna, string $texto, array $lista, string $nombre = 'nombre'): array
    {
        $opciones = collect($lista)->map(fn ($o) => (string) (((array) $o)[$nombre] ?? ''))->filter();

        return [null, "«{$texto}» no existe en {$columna['etiqueta']}"
            .($opciones->count() <= 12 ? ' (opciones: '.$opciones->implode(', ').').' : '.')];
    }

    private function csvValorBase(array $columna, string $texto, array $payload): array
    {
        $catalogo = function (array $lista, string $id) use ($columna, $texto): array {
            $valor = $this->csvBuscar($texto, $lista, $id);

            return $valor !== null ? [$valor, null] : $this->csvNoExiste($columna, $texto, $lista);
        };

        switch ($columna['clave']) {
            case 'id_estado_activo':
                return $catalogo($this->getEstadosProperty(), 'id_estado_equipo');

            case 'id_marca':
                return $catalogo($this->getMarcasProperty(), 'id_marca');

            case 'id_unidad':
                return $catalogo($this->getHotelesProperty(), 'id_unidad');

            case 'id_departamento':
                return $catalogo($this->getAllDepartamentos(), 'id_departamento');

            case 'id_proveedor':
                return $catalogo($this->getProveedoresProperty(), 'id_proveedor');

            case 'id_condicion_activo':
                return $catalogo($this->getCondicionesProperty(), 'id_valor');

            case 'id_modelo':
                $lista = array_values(array_filter(
                    $this->getAllModelos(),
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
                    app(\App\Services\UbicacionOrganizacionalService::class)->ubicaciones(),
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

                foreach (app(\App\Services\ColaboradoresService::class)->opcionesUsuarios() as $usuario) {
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

    private function csvValorTipo(array $columna, string $texto): array
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

    private function csvFecha(string $texto): ?string
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
            'id_estado_activo' => $nombres($this->getEstadosProperty(), 'id_estado_equipo'),
            'id_marca' => $nombres($this->getMarcasProperty(), 'id_marca'),
            'id_modelo' => $nombres($this->getAllModelos(), 'id_modelo'),
            'id_unidad' => $nombres($this->getHotelesProperty(), 'id_unidad'),
            'id_departamento' => $nombres($this->getAllDepartamentos(), 'id_departamento'),
            'id_proveedor' => $nombres($this->getProveedoresProperty(), 'id_proveedor'),
            'id_condicion_activo' => $nombres($this->getCondicionesProperty(), 'id_valor'),
            'id_ubicacion' => collect(app(\App\Services\UbicacionOrganizacionalService::class)->ubicaciones())->pluck('label', 'value')->all(),
        ];
        $tipos = $nombres($this->getTiposEquipoProperty(), 'id_tipo_equipo');

        $columnasPorTipo = [];
        $encabezados = ['tipo de equipo' => 'Tipo de equipo *'];

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
