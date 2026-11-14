<?php

namespace App\Http\Controllers;

use App\Services\OrganizacionService;
use App\Services\PlantillaInstalacionService;
use App\Services\RestablecerInstanciaService;
use App\Support\PoliticaContrasena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
            'plantillas' => PlantillaInstalacionService::definiciones(),
            'plantillaForzada' => PlantillaInstalacionService::forzada(),
            'plantillaActual' => app(PlantillaInstalacionService::class)->actual()['clave'],
            'colorActual' => app(OrganizacionService::class)->colorPrimario() ?? OrganizacionService::COLOR_PRIMARIO_PREDETERMINADO,
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
            'organizacion' => ['required', 'string', 'max:150'],
            'logo' => [
                'nullable', 'image', 'mimes:png,jpg,jpeg,webp',
                'max:'.OrganizacionService::LOGO_TAMANO_MAXIMO_KB,
                'dimensions:min_width=40,min_height=20,max_width=4000,max_height=4000',
            ],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sede' => ['required', 'string', 'max:120'],
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
            'organizacion.required' => 'Escribe el nombre de la organización.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'Utiliza una imagen PNG, JPG o WEBP.',
            'logo.max' => 'El logotipo no puede superar 1 MB.',
            'logo.dimensions' => 'La imagen debe medir entre 40×20 y 4000×4000 píxeles.',
            'color.required' => 'Elige el color principal.',
            'color.regex' => 'Elige un color válido.',
            'sede.required' => 'Escribe el nombre de la sede.',
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

        // Identidad visual: nombre, color principal y (opcional) logotipo.
        $color = strtoupper($datos['color']);
        $identidad = [
            'nombre_organizacion' => trim($datos['organizacion']),
            'color_primario' => $color === OrganizacionService::COLOR_PRIMARIO_PREDETERMINADO ? null : $color,
        ];

        if ($request->hasFile('logo')) {
            $archivo = $request->file('logo');
            $contenido = file_get_contents($archivo->getRealPath());
            $info = $contenido !== false ? @getimagesizefromstring($contenido) : false;

            if ($info === false || ! in_array($info['mime'], OrganizacionService::LOGO_MIMES, true)) {
                throw ValidationException::withMessages([
                    'logo' => 'No se pudo leer la imagen. Utiliza un PNG, JPG o WEBP válido.',
                ]);
            }

            $identidad += [
                'logo_base64' => base64_encode($contenido),
                'logo_mime' => $info['mime'],
                'logo_nombre_original' => Str::limit($archivo->getClientOriginalName(), 250, ''),
                'logo_version' => substr(sha1($contenido), 0, 20),
            ];
        }

        DB::transaction(function () use ($datos, $temporal, $forzada, $identidad) {
            // Plantilla elegida (Hoteles / Instituciones), salvo que el despliegue la fije.
            if (! $forzada) {
                app(PlantillaInstalacionService::class)->cambiar($datos['plantilla']);
            }

            app(OrganizacionService::class)->actualizar($identidad);

            // Al restablecer se borran las sedes: siempre se crea la primera.
            $idSede = DB::table('sedes')->insertGetId([
                'nombre' => trim($datos['sede']),
                'activo' => true,
            ], 'id_sede');

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
