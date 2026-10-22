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

    /*
     * Destinos: «incluirme» (la propia bandeja) y/o id_destinatarios
     * (otros usuarios con permiso de gestionar). Sin ninguno de los dos,
     * va a la propia cuenta. id_destinatario (uno solo) se acepta por
     * compatibilidad con la versión anterior del escáner.
     */
    public function enviar(Request $request, EquiposRecibidosService $recibidos): JsonResponse
    {
        Gate::authorize('gestionar');

        abort_unless($recibidos->disponible(), 503, 'Falta ejecutar la migración de equipos recibidos.');

        $datos = $request->validate([
            'id_equipo' => ['required_without:codigo', 'nullable', 'integer', 'exists:equipos,id_equipo'],
            'codigo' => ['required_without:id_equipo', 'nullable', 'string', 'max:100'],
            'incluirme' => ['nullable', 'boolean'],
            'id_destinatarios' => ['nullable', 'array', 'max:50'],
            'id_destinatarios.*' => ['integer'],
            'id_destinatario' => ['nullable', 'integer'],
        ]);

        $idRemitente = (int) $request->user()->getKey();

        $otros = collect($datos['id_destinatarios'] ?? [])
            ->when(filled($datos['id_destinatario'] ?? null), fn ($ids) => $ids->push($datos['id_destinatario']))
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === $idRemitente)
            ->unique()
            ->values();

        $incluirme = array_key_exists('incluirme', $datos) && $datos['incluirme'] !== null
            ? (bool) $datos['incluirme']
            : $otros->isEmpty();

        if ($otros->isNotEmpty()) {
            $permitidos = collect($recibidos->destinatarios($idRemitente))->keyBy('id');

            abort_if(
                $otros->contains(fn (int $id) => ! $permitidos->has($id)),
                422,
                'Alguno de los usuarios elegidos no puede recibir equipos.'
            );
        }

        $ids = $otros->when($incluirme, fn ($ids) => $ids->prepend($idRemitente))->values();

        abort_if($ids->isEmpty(), 422, 'Elige al menos a una persona.');

        if (blank($datos['id_equipo'] ?? null)) {
            abort_unless($recibidos->admiteCodigos(), 503, 'Falta ejecutar la migración de códigos no registrados.');
        }

        $resultados = $ids->map(function (int $idUsuario) use ($datos, $recibidos, $idRemitente) {
            $estado = filled($datos['id_equipo'] ?? null)
                ? $recibidos->enviar($idUsuario, (int) $datos['id_equipo'], $idRemitente)
                : $recibidos->enviarCodigo($idUsuario, (string) $datos['codigo'], $idRemitente);

            return [
                'id' => $idUsuario,
                'propio' => $idUsuario === $idRemitente,
                'estado' => $estado,
                'pendientes' => $recibidos->contarPendientes($idUsuario),
            ];
        })->all();

        $primero = $resultados[0];

        return response()->json([
            'resultados' => $resultados,
            'enviados' => count(array_filter($resultados, fn ($r) => $r['estado'] === 'enviado')),
            'duplicados' => count(array_filter($resultados, fn ($r) => $r['estado'] === 'duplicado')),
            // Compatibilidad (un solo destino).
            'estado' => $primero['estado'],
            'propio' => $primero['propio'],
            'pendientes' => $primero['pendientes'],
        ]);
    }
}
