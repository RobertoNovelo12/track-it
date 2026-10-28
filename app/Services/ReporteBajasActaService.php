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
            ->values();
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
