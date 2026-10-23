<?php

namespace App\Http\Controllers;

use App\Services\DevolucionService;
use Barryvdh\DomPDF\Facade\Pdf;

/*
|--------------------------------------------------------------------------
| Acta de devolución (PDF)
|--------------------------------------------------------------------------
|
| Se genera al momento a partir del movimiento: no se guarda un archivo.
|
*/

class DevolucionController extends Controller
{
    public function acta(int $movimiento, DevolucionService $devoluciones)
    {
        $acta = $devoluciones->datosActa($movimiento);

        abort_unless($acta, 404, 'El movimiento no es una devolución.');

        $nombre = 'acta-devolucion-' . ($acta['codigo_inventario'] ?: $movimiento) . '.pdf';

        return Pdf::loadView('reportes.devolucion-acta-pdf', ['acta' => $acta])
            ->setPaper('letter', 'portrait')
            ->stream($nombre);
    }
}
