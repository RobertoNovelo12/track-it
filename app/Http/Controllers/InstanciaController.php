<?php

namespace App\Http\Controllers;

use App\Services\PlantillaInstalacionService;
use App\Services\RestablecerInstanciaService;
use App\Support\PoliticaContrasena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Instancia restablecida y configuración inicial
|--------------------------------------------------------------------------
|
| restablecida: muestra una sola vez el usuario temporal y su contraseña.
| configurar:   el usuario temporal crea el primer edificio, su
|               departamento y la cuenta definitiva (administrador).
|               Al guardar se elimina el usuario temporal y se pide
|               iniciar sesión con la cuenta nueva.
|
*/

class InstanciaController extends Controller
{
    public function restablecida(Request $request): View|RedirectResponse
    {
        $credenciales = $request->session()->get('credenciales_instancia');

        if (! is_array($credenciales)) {
            return redirect()->route('login');
        }

        return view('instancia.restablecida', ['credenciales' => $credenciales]);
    }

    public function configurar(Request $request): View|RedirectResponse
    {
        if (! RestablecerInstanciaService::esUsuarioTemporal($request->user())) {
            return redirect()->to('/');
        }

        return view('instancia.configurar', [
            'sedes' => DB::table('sedes')->where('activo', true)->orderBy('nombre')->get(['id_sede', 'nombre']),
            'plantillas' => PlantillaInstalacionService::definiciones(),
            'plantillaForzada' => PlantillaInstalacionService::forzada(),
            'plantillaActual' => app(PlantillaInstalacionService::class)->actual()['clave'],
        ]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $temporal = $request->user();

        if (! RestablecerInstanciaService::esUsuarioTemporal($temporal)) {
            return redirect()->to('/');
        }

        $plantillas = PlantillaInstalacionService::definiciones();
        $forzada = PlantillaInstalacionService::forzada();
        $elegida = $forzada ?? (string) $request->input('plantilla');
        $unidad = $plantillas[$elegida]['etiquetas']['unidad'] ?? PlantillaInstalacionService::t('unidad');

        $datos = $request->validate([
            'plantilla' => [$forzada ? 'nullable' : 'required', Rule::in(array_keys($plantillas))],
            'id_sede' => ['nullable', 'integer', Rule::exists('sedes', 'id_sede')],
            'sede_nueva' => ['nullable', 'required_without:id_sede', 'string', 'max:120'],
            'unidad' => ['required', 'string', 'max:150'],
            'departamento' => ['required', 'string', 'max:150'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::notIn([RestablecerInstanciaService::USUARIO_TEMPORAL]),
                Rule::unique('usuarios', 'username'),
            ],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')],
            'password' => ['required', 'string', ...PoliticaContrasena::reglas(), 'confirmed'],
        ], PoliticaContrasena::mensajes('password') + [
            'plantilla.required' => 'Elige el tipo de organización.',
            'plantilla.in' => 'Elige una plantilla válida.',
            'sede_nueva.required_without' => 'Elige una sede o escribe el nombre de una nueva.',
            'unidad.required' => "Escribe el nombre del {$unidad}.",
            'departamento.required' => 'Escribe el nombre del departamento.',
            'nombres.required' => 'Escribe tu nombre.',
            'apellido_paterno.required' => 'Escribe tu apellido paterno.',
            'username.required' => 'Elige un nombre de usuario.',
            'username.regex' => 'El usuario solo puede tener letras, números, puntos, guiones y guiones bajos, sin espacios.',
            'username.not_in' => 'Ese nombre de usuario está reservado.',
            'username.unique' => 'Ese nombre de usuario ya existe.',
            'correo.required' => 'Escribe tu correo.',
            'correo.email' => 'Escribe un correo válido.',
            'correo.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'Escribe una contraseña.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        DB::transaction(function () use ($datos, $temporal, $forzada) {
            // Plantilla elegida (Hoteles / Instituciones), salvo que el despliegue la fije.
            if (! $forzada) {
                app(PlantillaInstalacionService::class)->cambiar($datos['plantilla']);
            }

            $idSede = $datos['id_sede'] ?? null;

            if (! $idSede) {
                $idSede = DB::table('sedes')->insertGetId([
                    'nombre' => trim($datos['sede_nueva']),
                    'activo' => true,
                ], 'id_sede');
            }

            $idUnidad = DB::table('unidades_organizativas')->insertGetId([
                'id_sede' => $idSede,
                'nombre' => trim($datos['unidad']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_unidad');

            $idDepartamento = DB::table('departamentos')->insertGetId([
                'nombre' => trim($datos['departamento']),
                'activo' => true,
                'atiende_todas_unidades' => false,
            ], 'id_departamento');

            DB::table('departamento_unidad')->insert([
                'id_departamento' => $idDepartamento,
                'id_unidad' => $idUnidad,
            ]);

            $idUsuario = DB::table('usuarios')->insertGetId([
                'id_rol' => $temporal->id_rol,
                'id_estado_usuario' => $temporal->id_estado_usuario,
                'nombres' => trim($datos['nombres']),
                'apellido_paterno' => trim($datos['apellido_paterno']),
                'apellido_materno' => filled($datos['apellido_materno'] ?? null) ? trim($datos['apellido_materno']) : null,
                'username' => $datos['username'],
                'correo' => mb_strtolower(trim($datos['correo'])),
                'password_hash' => Hash::make($datos['password']),
                'id_unidad' => $idUnidad,
                'id_departamento' => $idDepartamento,
                'debe_cambiar_password' => false,
                'acceso_sistema' => true,
                'fecha_registro' => now(),
            ], 'id_usuario');

            DB::table('bitacora_auditoria')->insert([
                'id_usuario' => $idUsuario,
                'accion' => 'INSTANCIA_CONFIGURADA',
                'modulo' => 'ADMINISTRACION',
                'descripcion' => 'Se configuró la instancia: primer '
                    .PlantillaInstalacionService::t('unidad')
                    .', departamento y cuenta de administrador. Se eliminó el usuario temporal.',
                'fecha_hora' => now(),
            ]);

            DB::table('usuarios')->where('id_usuario', $temporal->id_usuario)->delete();
        });

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'La instancia quedó configurada. Inicia sesión con tu cuenta nueva.')
            ->withCookie(Cookie::forget('trackit_auth_session'));
    }
}
