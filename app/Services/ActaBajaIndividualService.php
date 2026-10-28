<?php

namespace App\Services;

use App\Models\ReporteGenerado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ActaBajaIndividualService
{
    /**
     * Genera y registra un PDF para una sola baja.
     *
     * Debe ejecutarse dentro de la transacción
     * que registra la baja del equipo.
     *
     * @param array<string, mixed> $equipo
     * @param array<string, mixed> $datos
     * @param array<string, string> $firmas
     * @param array<string, string|null> $fotos
     *
     * @return array{reporte_id:int,ruta_pdf:string}
     */
    public function generar(
        int $idBaja,
        string $folio,
        array $equipo,
        array $datos,
        array $firmas,
        array $fotos,
        int $usuarioId
    ): array {
        $organizacion = app(OrganizacionService::class);

        /*
         * Logo y fotos reducidos al tamaño en que se imprimen (ver
         * ImagenPdfService): dompdf tarda mucho con fotos de teléfono
         * a resolución completa.
         */
        $imagenesPdf = app(ImagenPdfService::class);

        $logoData = $imagenesPdf->logoDataUri();

        $imagenes = [];

        foreach ([
            'foto_frontal' => 'Vista frontal',
            'foto_trasera' => 'Vista trasera',
            'foto_lateral_1' => 'Vista lateral 1',
            'foto_lateral_2' => 'Vista lateral 2',
        ] as $clave => $etiqueta) {
            $ruta = $fotos[$clave] ?? null;

            $imagen = $imagenesPdf->dataUri($ruta, 900, 75);

            if ($imagen !== null) {
                $imagenes[$etiqueta] = $imagen;
            }
        }

        $documento = [
            'folio' => $folio,
            'numero_ticket' => 'GPRVM-'.$datos['numeroTicket'],
            'logo' => $logoData,
            'organizacion' => $organizacion->nombre(),
            'equipo' => $equipo,
            'fecha' => $datos['fechaBaja'],
            'motivo' => trim(
                (string) $datos['motivoBaja']
            ),
            'comentarios' => trim(
                (string) ($datos['comentariosBaja'] ?? '')
            ),
            'firmas' => $firmas,
            'fotos' => $imagenes,
        ];

        $pdf = Pdf::loadView(
            'reportes.baja-individual-pdf',
            compact('documento')
        )
            ->setPaper('letter')
            ->output();

        $nombre = 'BAJA-EQUIPO-'.$idBaja.'.pdf';

        $rutaPdf = 'reportes/bajas-individuales/'
            .$idBaja
            .'/'
            .$nombre;

        if (
            !Storage::disk('local')->put(
                $rutaPdf,
                $pdf
            )
        ) {
            throw new RuntimeException(
                'No fue posible guardar el PDF de la baja.'
            );
        }

        try {
            DB::table('bajas')
                ->where('id_baja', $idBaja)
                ->update([
                    'ruta_pdf' => $rutaPdf
                ]);

            $reporte = ReporteGenerado::create([
                'id_usuario' => $usuarioId,
                'tipo_reporte' => 'bajas',
                'formato' => 'PDF',
                'fecha_desde' => $datos['fechaBaja'],
                'fecha_hasta' => $datos['fechaBaja'],
                'filtros' => [
                    'baja_individual' => [
                        'id_baja' => $idBaja,
                        'id_equipo' => (int) (
                            $equipo['id_equipo'] ?? 0
                        ),
                        'folio' => $folio,
                    ],
                ],
                'total_registros' => 1,
                'nombre_archivo' => $nombre,
                'ruta_archivo' => $rutaPdf,
            ]);

        } catch (Throwable $error) {
            Storage::disk('local')->delete($rutaPdf);

            throw $error;
        }

        return [
            'reporte_id' => (int) $reporte->getKey(),
            'ruta_pdf' => $rutaPdf
        ];
    }
}
