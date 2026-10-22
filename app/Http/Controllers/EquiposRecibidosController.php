<?php

namespace App\Http\Controllers;

use App\Services\EquiposRecibidosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/*
|--------------------------------------------------------------------------
| Escáner del teléfono
|--------------------------------------------------------------------------
|
| buscar: código escaneado → datos del equipo (para «Abrir aquí»).
| enviar: agrega el equipo a la bandeja del mismo usuario («Enviar a la
|         PC»). Requiere permiso de gestionar. Si el código no está en el
|         inventario se envía el código (para darlo de alta en la PC).
|
*/

class EquiposRecibidosController extends Controller
{
    public function buscar(string $codigo, EquiposRecibidosService $recibidos): JsonResponse
    {
        $equipo = $recibidos->buscarPorCodigo($codigo);

        if (! $equipo) {
            /*
             * No está en el inventario: probablemente se leyó el número de
             * serie de un equipo nuevo. Se ofrece darlo de alta con ese dato.
             */
            $leido = $recibidos->normalizarCodigo($codigo);
            $puedeGestionar = Gate::allows('gestionar');

            return response()->json([
                'encontrado' => false,
                'codigo' => $leido,
                'mensaje' => 'El código ' . $leido . ' no está en el inventario.',
                'puede_agregar' => $puedeGestionar && $leido !== '',
                'puede_enviar' => $puedeGestionar && $leido !== '' && $recibidos->admiteCodigos(),
                'url_agregar' => $puedeGestionar && $leido !== ''
                    ? route('equipos.create', ['numero_serie' => $leido])
                    : null,
            ], 404);
        }

        return response()->json([
            'encontrado' => true,
            'equipo' => $equipo,
            'puede_enviar' => Gate::allows('gestionar') && $recibidos->disponible(),
        ]);
    }

    public function enviar(Request $request, EquiposRecibidosService $recibidos): JsonResponse
    {
        Gate::authorize('gestionar');

        abort_unless($recibidos->disponible(), 503, 'Falta ejecutar la migración de equipos recibidos.');

        $datos = $request->validate([
            'id_equipo' => ['required_without:codigo', 'nullable', 'integer', 'exists:equipos,id_equipo'],
            'codigo' => ['required_without:id_equipo', 'nullable', 'string', 'max:100'],
        ]);

        $idUsuario = (int) $request->user()->getKey();

        if (filled($datos['id_equipo'] ?? null)) {
            $estado = $recibidos->enviar($idUsuario, (int) $datos['id_equipo']);
        } else {
            abort_unless($recibidos->admiteCodigos(), 503, 'Falta ejecutar la migración de códigos no registrados.');

            $estado = $recibidos->enviarCodigo($idUsuario, (string) $datos['codigo']);
        }

        return response()->json([
            'estado' => $estado,
            'pendientes' => $recibidos->contarPendientes($idUsuario),
        ]);
    }
}
