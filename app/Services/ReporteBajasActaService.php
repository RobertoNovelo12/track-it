<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReporteBajasActaService
{
    /**
     * @param bool $miniaturas true para la vista previa (fotos muy
     *                         pequeñas y ligeras); false para el PDF.
     */
    public function obtener(
        array $filtros = [],
        ?int $limite = null,
        bool $miniaturas = false
    ): Collection {
        $registros = app(ReporteDataService::class)
            ->datosFormatoBajas($filtros, $limite);

        $ids = $registros
            ->pluck('id_baja')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $bajas = DB::table('bajas as b')
            ->join(
                'equipos as e',
                'e.id_equipo',
                '=',
                'b.id_equipo'
            )
            ->leftJoin(
                'tipos_equipo as te',
                'te.id_tipo_equipo',
                '=',
                'e.id_tipo_equipo'
            )
            ->leftJoin(
                'marcas as ma',
                'ma.id_marca',
                '=',
                'e.id_marca'
            )
            ->leftJoin(
                'modelos as mo',
                'mo.id_modelo',
                '=',
                'e.id_modelo'
            )
            ->leftJoin(
                'proveedores as p',
                'p.id_proveedor',
                '=',
                'e.id_proveedor'
            )
            ->whereIn('b.id_baja', $ids->all())
            ->select([
                'b.id_baja',
                'b.id_equipo',
                'b.folio',
                'b.numero_ticket',
                'b.fecha_baja',
                'b.motivo_baja',
                'b.comentarios',
                'b.autoriza',
                'b.firmas',
                'e.codigo_inventario',
                'e.nombre_equipo',
                'e.numero_serie',
                'e.numero_factura',
                'e.fecha_registro',
                'e.fecha_fin_garantia',
                'te.nombre as tipo_equipo',
                'ma.nombre as marca',
                'mo.nombre as modelo',
                'p.nombre as proveedor',
            ])
            ->get()
            ->keyBy('id_baja');

        $evidencias = collect();

        if ($this->existeTablaEvidencias()) {
            $evidencias = DB::table('baja_evidencias')
                ->whereIn('id_baja', $ids->all())
                ->get([
                    'id_baja',
                    'foto_frontal',
                    'foto_trasera',
                    'foto_lateral_1',
                    'foto_lateral_2',
                ])
                ->keyBy('id_baja');
        }

        return $ids
            ->map(function (int $id) use (
                $bajas,
                $evidencias,
                $miniaturas
            ) {
                $baja = $bajas->get($id);

                if (!$baja) {
                    return null;
                }

                $fotos = $evidencias->get($id);

                return [
                    'id' => $id,
                    'id_equipo' => (int) $baja->id_equipo,
                    'fecha_baja' => $baja->fecha_baja,
                    'folio' => $baja->folio,
                    'numero_ticket' => $baja->numero_ticket,

                    'fecha' => $this->fecha(
                        $baja->fecha_baja
                    ),

                    'motivo' => $baja->motivo_baja,
                    'comentarios' => $baja->comentarios,
                    'autoriza' => $baja->autoriza,
                    'firmas' => json_decode($baja->firmas ?? '{}', true) ?: [],

                    'equipo' => [
                        'codigo' => $baja->codigo_inventario,
                        'nombre' => $baja->nombre_equipo,
                        'serie' => $baja->numero_serie,
                        'tipo' => $baja->tipo_equipo,
                        'marca' => $baja->marca,
                        'modelo' => $baja->modelo,
                        'factura' => $baja->numero_factura,
                        'proveedor' => $baja->proveedor,

                        'fecha_registro' => $this->fecha(
                            $baja->fecha_registro
                        ),

                        'garantia' => $this->fecha(
                            $baja->fecha_fin_garantia
                        ),
                    ],

                    'fotos' => [
                        'frontal' => $this->imagen(
                            $fotos->foto_frontal ?? null,
                            $miniaturas
                        ),

                        'trasera' => $this->imagen(
                            $fotos->foto_trasera ?? null,
                            $miniaturas
                        ),

                        'lateral_1' => $this->imagen(
                            $fotos->foto_lateral_1 ?? null,
                            $miniaturas
                        ),

                        'lateral_2' => $this->imagen(
                            $fotos->foto_lateral_2 ?? null,
                            $miniaturas
                        ),
                    ],
                ];
            })
            ->filter()
            ->values()
            ->pipe(fn (Collection $bajas) => $this->conjuntas($bajas, $miniaturas));
    }

    /*
     * Bajas hechas juntas (baja múltiple conjunta): en lugar de un acta
     * por equipo sale el acta conjunta con TODOS sus equipos, aunque se
     * haya buscado solo uno de ellos. Esa acta se guardó al darlos de
     * baja (reportes_generados, filtros.baja_multiple) con su selección,
     * firmas y fotografías. Cada acta conjunta aparece una sola vez, en
     * el lugar del primer equipo que le pertenece.
     */
    private function conjuntas(Collection $bajas, bool $miniaturas): Collection
    {
        if ($bajas->isEmpty()) {
            return $bajas;
        }

        $fechas = $bajas->pluck('fecha_baja')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->toDateString())
            ->unique()
            ->values()
            ->all();

        if ($fechas === []) {
            return $bajas;
        }

        try {
            $reportes = DB::table('reportes_generados')
                ->where('tipo_reporte', 'bajas')
                ->whereIn(DB::raw('CAST(fecha_desde AS DATE)'), $fechas)
                ->orderByDesc('id_reporte')
                ->get(['id_reporte', 'filtros']);
        } catch (\Throwable $error) {
            report($error);

            return $bajas;
        }

        // id_equipo => [id_reporte, documento]
        $porEquipo = [];

        foreach ($reportes as $reporte) {
            $documento = (json_decode((string) $reporte->filtros, true) ?: [])['baja_multiple'] ?? null;

            if (! is_array($documento) || empty($documento['equipos'])) {
                continue;
            }

            foreach ($documento['equipos'] as $equipo) {
                $idEquipo = (int) ($equipo['id_equipo'] ?? 0);

                if ($idEquipo > 0 && ! isset($porEquipo[$idEquipo])) {
                    $porEquipo[$idEquipo] = [(int) $reporte->id_reporte, $documento];
                }
            }
        }

        if ($porEquipo === []) {
            return $bajas;
        }

        $imagenes = app(ImagenPdfService::class);
        $vistos = [];
        $resultado = [];

        foreach ($bajas as $baja) {
            $conjunta = $porEquipo[(int) ($baja['id_equipo'] ?? 0)] ?? null;

            if ($conjunta === null) {
                $resultado[] = $baja;

                continue;
            }

            [$idReporte, $documento] = $conjunta;

            if (isset($vistos[$idReporte])) {
                continue;
            }

            $vistos[$idReporte] = true;

            // Logotipo y fotografías como imágenes incrustadas (igual que al generarla).
            $documento['logo'] = $imagenes->logoDataUri();
            $rutas = $documento['fotos'] ?? [];
            $documento['fotos'] = [];

            foreach (['fotoFrontal' => 'Vista frontal', 'fotoTrasera' => 'Vista trasera', 'fotoLateral1' => 'Vista lateral 1', 'fotoLateral2' => 'Vista lateral 2'] as $campo => $titulo) {
                $imagen = $imagenes->dataUri($rutas[$campo] ?? null, $miniaturas ? 320 : 900, $miniaturas ? 60 : 75);

                if ($imagen !== null) {
                    $documento['fotos'][$titulo] = $imagen;
                }
            }

            $resultado[] = [
                'conjunta' => true,
                'id_reporte' => $idReporte,
                'documento' => $documento,
            ];
        }

        return collect($resultado);
    }

    private function fecha($fecha): ?string
    {
        return filled($fecha)
            ? Carbon::parse($fecha)->format('d/m/Y')
            : null;
    }

    private function imagen(
        ?string $ruta,
        bool $miniatura = false
    ): ?string {
        /*
         * Las fotos se reducen una sola vez y se guardan en caché de
         * disco. PDF: 700 px / vista previa: 320 px.
         */
        return app(ImagenPdfService::class)->dataUri(
            $ruta,
            $miniatura ? 320 : 700,
            $miniatura ? 60 : 70
        );
    }

    private function existeTablaEvidencias(): bool
    {
        // Evita una consulta remota al esquema en cada descarga.
        if (Cache::get('schema:baja_evidencias') === true) {
            return true;
        }

        $existe = \App\Support\Esquema::tieneTabla('baja_evidencias');

        if ($existe) {
            Cache::forever('schema:baja_evidencias', true);
        }

        return $existe;
    }
}
