<?php

namespace App\Http\Controllers;

use App\Services\PushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| «Notificaciones en este dispositivo» (Ajustes › Notificaciones)
|--------------------------------------------------------------------------
|
| El navegador entrega la suscripción (endpoint + llaves) y aquí se
| guarda para el usuario en sesión. Ver App\Services\PushService.
|
*/

class PushSuscripcionController extends Controller
{
    public function store(Request $request, PushService $push): JsonResponse
    {
        abort_unless($push->disponible(), 503, 'Las notificaciones push no están configuradas en el servidor.');

        $datos = $request->validate([
            'endpoint' => ['required', 'string', 'url', 'starts_with:https://', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        $push->guardar(
            (int) $request->user()->id_usuario,
            $datos['endpoint'],
            $datos['keys']['p256dh'],
            $datos['keys']['auth'],
            $request->userAgent()
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, PushService $push): JsonResponse
    {
        $datos = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
        ]);

        if ($push->disponible()) {
            $push->eliminar((int) $request->user()->id_usuario, $datos['endpoint']);
        }

        return response()->json(['ok' => true]);
    }

    /*
     * «Enviar prueba»: llega a todos los dispositivos activados del usuario.
     */
    public function prueba(Request $request, PushService $push): JsonResponse
    {
        abort_unless($push->disponible(), 503, 'Las notificaciones push no están configuradas en el servidor.');

        $enviados = $push->enviarAUsuarios(
            [(int) $request->user()->id_usuario],
            'Notificaciones activadas',
            'Así se verán los avisos de Track IT en este dispositivo.',
            route('ajustes.index', ['section' => 'notificaciones'], false),
            'prueba'
        );

        return response()->json([
            'ok' => $enviados > 0,
            'enviados' => $enviados,
        ]);
    }
}
