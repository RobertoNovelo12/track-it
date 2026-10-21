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
|         PC»). Requiere permiso de gestionar.
|
*/

class EquiposRecibidosController extends Controller
{
    public function buscar(string $codigo, EquiposRecibidosService $recibidos): JsonResponse
    {
        $equipo = $recibidos->buscarPorCodigo($codigo);

        if (! $equipo) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'No hay ningún equipo con el código ' . $codigo . '.',
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
            'id_equipo' => ['required', 'integer', 'exists:equipos,id_equipo'],
        ]);

        $idUsuario = (int) $request->user()->getKey();

        $estado = $recibidos->enviar($idUsuario, (int) $datos['id_equipo']);

        return response()->json([
            'estado' => $estado,
            'pendientes' => $recibidos->contarPendientes($idUsuario),
        ]);
    }
}
