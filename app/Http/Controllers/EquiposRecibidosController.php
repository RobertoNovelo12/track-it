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
| enviar: agrega el equipo a la bandeja del mismo usuario («Enviar a mi
|         PC») o de otro usuario con permiso de gestionar (id_destinatario).
|         Requiere permiso de gestionar. Si el código no está en el
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

    /*
     * Usuarios a los que se puede enviar (flecha junto a «Enviar a mi PC»).
     */
    public function destinatarios(Request $request, EquiposRecibidosService $recibidos): JsonResponse
    {
        Gate::authorize('gestionar');

        return response()->json([
            'destinatarios' => $recibidos->destinatarios((int) $request->user()->getKey()),
        ]);
    }

    public function enviar(Request $request, EquiposRecibidosService $recibidos): JsonResponse
    {
        Gate::authorize('gestionar');

        abort_unless($recibidos->disponible(), 503, 'Falta ejecutar la migración de equipos recibidos.');

        $datos = $request->validate([
            'id_equipo' => ['required_without:codigo', 'nullable', 'integer', 'exists:equipos,id_equipo'],
            'codigo' => ['required_without:id_equipo', 'nullable', 'string', 'max:100'],
            // Vacío: a la propia cuenta.
            'id_destinatario' => ['nullable', 'integer'],
        ]);

        $idRemitente = (int) $request->user()->getKey();
        $idUsuario = $idRemitente;

        if (filled($datos['id_destinatario'] ?? null) && (int) $datos['id_destinatario'] !== $idRemitente) {
            abort_unless(
                $recibidos->puedeRecibir((int) $datos['id_destinatario'], $idRemitente),
                422,
                'Ese usuario no puede recibir equipos.'
            );

            $idUsuario = (int) $datos['id_destinatario'];
        }

        if (filled($datos['id_equipo'] ?? null)) {
            $estado = $recibidos->enviar($idUsuario, (int) $datos['id_equipo'], $idRemitente);
        } else {
            abort_unless($recibidos->admiteCodigos(), 503, 'Falta ejecutar la migración de códigos no registrados.');

            $estado = $recibidos->enviarCodigo($idUsuario, (string) $datos['codigo'], $idRemitente);
        }

        return response()->json([
            'estado' => $estado,
            'propio' => $idUsuario === $idRemitente,
            'pendientes' => $recibidos->contarPendientes($idUsuario),
        ]);
    }
}
