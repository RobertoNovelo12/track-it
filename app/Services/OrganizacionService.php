<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Configuración de Organización
|--------------------------------------------------------------------------
|
| Lee y actualiza la fila única de configuracion_organizacion.
|
| - La configuración (sin el binario del logotipo) se guarda en caché
|   porque se consulta en cada página (sidebar y pantallas de acceso).
| - Si la migración todavía no se ha aplicado, se devuelven los valores
|   predeterminados, que equivalen al comportamiento anterior.
|
*/

class OrganizacionService
{
    public const LOGO_PREDETERMINADO = 'images/logo-grand-palladium.png';

    public const NOMBRE_PREDETERMINADO = 'Grand Palladium Hotels & Resorts';

    public const ESCALA_MINIMA = 50;

    public const ESCALA_MAXIMA = 160;

    public const LOGO_TAMANO_MAXIMO_KB = 1024;

    public const LOGO_MIMES = [
        'image/png',
        'image/jpeg',
        'image/webp',
    ];

    public const MODOS_OSCURO = [
        'original' => [
            'etiqueta' => 'Colores originales',
            'descripcion' => 'El logotipo se muestra igual que en modo claro.',
        ],
        'invertir' => [
            'etiqueta' => 'Invertir apariencia',
            'descripcion' => 'Invierte la luminosidad y conserva el tono de los colores.',
        ],
        'blanco' => [
            'etiqueta' => 'Versión blanca',
            'descripcion' => 'Convierte el logotipo en una silueta blanca.',
        ],
    ];

    public const MODOS_CAMPO = [
        'obligatorio' => 'Obligatorio',
        'opcional' => 'Opcional',
        'oculto' => 'Oculto',
    ];

    /*
     * Campos del registro que el administrador puede configurar.
     * Los valores predeterminados reproducen el formulario anterior.
     */
    public const CAMPOS_REGISTRO = [
        'apellido_materno' => [
            'etiqueta' => 'Apellido materno',
            'grupo' => 'Información personal',
            'defecto' => 'opcional',
            'ayuda' => null,
        ],
        'telefono' => [
            'etiqueta' => 'Teléfono',
            'grupo' => 'Información personal',
            'defecto' => 'oculto',
            'ayuda' => null,
        ],
        'numero_colaborador' => [
            'etiqueta' => 'Número de colaborador',
            'grupo' => 'Información organizacional',
            'defecto' => 'obligatorio',
            'ayuda' => 'Debe ser único cuando se captura.',
        ],
        'puesto' => [
            'etiqueta' => 'Puesto',
            'grupo' => 'Información organizacional',
            'defecto' => 'obligatorio',
            'ayuda' => null,
        ],
        'id_rol' => [
            'etiqueta' => 'Rol solicitado',
            'grupo' => 'Información organizacional',
            'defecto' => 'obligatorio',
            'ayuda' => 'Si no se captura, el administrador asigna el rol al aprobar la cuenta.',
        ],
    ];

    private const CACHE_KEY = 'trackit.organizacion.configuracion.v1';

    private ?array $memoria = null;

    /*
    |--------------------------------------------------------------------------
    | Lectura
    |--------------------------------------------------------------------------
    */

    public function configuracion(): array
    {
        if ($this->memoria !== null) {
            return $this->memoria;
        }

        try {
            $config = Cache::rememberForever(
                self::CACHE_KEY,
                fn (): array => $this->leerDesdeBaseDeDatos()
            );
        } catch (QueryException) {
            // La tabla aún no existe: comportamiento anterior.
            $config = $this->predeterminada();
        }

        return $this->memoria = $config;
    }

    public function nombre(): string
    {
        return $this->configuracion()['nombre_organizacion']
            ?: self::NOMBRE_PREDETERMINADO;
    }

    /*
    |--------------------------------------------------------------------------
    | Dominios de correo
    |--------------------------------------------------------------------------
    */

    public function dominios(): array
    {
        return $this->configuracion()['dominios_correo'];
    }

    /*
     * La restricción solo aplica si hay al menos un dominio.
     */
    public function restringeDominio(): bool
    {
        return $this->configuracion()['restringir_dominio_correo']
            && $this->dominios() !== [];
    }

    public function correoPermitido(string $correo): bool
    {
        if (! $this->restringeDominio()) {
            return true;
        }

        $dominio = Str::lower(Str::afterLast(trim($correo), '@'));

        return in_array($dominio, $this->dominios(), true);
    }

    /*
     * Normaliza "@Empresa.com " a "empresa.com".
     * Devuelve null si el texto no es un dominio válido.
     */
    public static function normalizarDominio(string $dominio): ?string
    {
        $dominio = Str::lower(trim($dominio));
        $dominio = ltrim($dominio, '@');

        if ($dominio === '' || strlen($dominio) > 253) {
            return null;
        }

        $valido = preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
            $dominio
        );

        return $valido ? $dominio : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Campos del registro
    |--------------------------------------------------------------------------
    */

    public function camposRegistro(): array
    {
        return $this->configuracion()['campos_registro'];
    }

    public function modoCampo(string $campo): string
    {
        return $this->camposRegistro()[$campo]
            ?? self::CAMPOS_REGISTRO[$campo]['defecto']
            ?? 'opcional';
    }

    public function campoVisible(string $campo): bool
    {
        return $this->modoCampo($campo) !== 'oculto';
    }

    public function campoObligatorio(string $campo): bool
    {
        return $this->modoCampo($campo) === 'obligatorio';
    }

    /*
    |--------------------------------------------------------------------------
    | Logotipo
    |--------------------------------------------------------------------------
    */

    public function logoPersonalizado(): bool
    {
        return filled($this->configuracion()['logo_version']);
    }

    public function logoUrl(): string
    {
        $version = $this->configuracion()['logo_version'];

        return filled($version)
            ? route('organizacion.logo', ['version' => $version])
            : asset(self::LOGO_PREDETERMINADO);
    }

    public function logoEscala(): int
    {
        return $this->configuracion()['logo_escala'];
    }

    public function logoModoOscuro(): string
    {
        return $this->configuracion()['logo_modo_oscuro'];
    }

    /*
     * Ancho en píxeles a partir del tamaño base de cada ubicación.
     */
    public function logoAncho(int $base): int
    {
        return (int) round($base * $this->logoEscala() / 100);
    }

    /*
     * Contenido del logotipo personalizado. No se guarda en caché
     * porque el navegador lo conserva gracias a su versión.
     */
    public function logoContenido(): ?array
    {
        try {
            $fila = DB::table('configuracion_organizacion')
                ->where('id', 1)
                ->first([
                    'logo_base64',
                    'logo_mime',
                    'logo_version',
                ]);
        } catch (QueryException) {
            return null;
        }

        if (! $fila || ! filled($fila->logo_base64)) {
            return null;
        }

        $contenido = base64_decode($fila->logo_base64, true);

        if ($contenido === false) {
            return null;
        }

        return [
            'contenido' => $contenido,
            'mime' => $fila->logo_mime,
            'version' => $fila->logo_version,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Escritura
    |--------------------------------------------------------------------------
    */

    /*
     * Actualiza solo las claves permitidas que se reciban.
     */
    public function actualizar(array $cambios): void
    {
        $permitidas = [
            'nombre_organizacion',
            'restringir_dominio_correo',
            'dominios_correo',
            'campos_registro',
            'logo_base64',
            'logo_mime',
            'logo_nombre_original',
            'logo_version',
            'logo_escala',
            'logo_modo_oscuro',
        ];

        $datos = array_intersect_key($cambios, array_flip($permitidas));

        foreach (['dominios_correo', 'campos_registro'] as $json) {
            if (array_key_exists($json, $datos)) {
                $datos[$json] = json_encode($datos[$json]);
            }
        }

        $datos['actualizado_por'] = auth()->id();
        $datos['actualizado_en'] = now();

        DB::table('configuracion_organizacion')
            ->where('id', 1)
            ->update($datos);

        $this->olvidar();
    }

    public function olvidar(): void
    {
        Cache::forget(self::CACHE_KEY);

        $this->memoria = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Internos
    |--------------------------------------------------------------------------
    */

    private function leerDesdeBaseDeDatos(): array
    {
        $fila = DB::table('configuracion_organizacion')
            ->where('id', 1)
            ->first([
                'nombre_organizacion',
                'restringir_dominio_correo',
                'dominios_correo',
                'campos_registro',
                'logo_mime',
                'logo_version',
                'logo_escala',
                'logo_modo_oscuro',
                'actualizado_en',
            ]);

        if (! $fila) {
            return $this->predeterminada();
        }

        $dominios = collect(json_decode($fila->dominios_correo ?? '[]', true) ?: [])
            ->map(fn ($dominio) => is_string($dominio) ? self::normalizarDominio($dominio) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'nombre_organizacion' => $fila->nombre_organizacion,
            'restringir_dominio_correo' => (bool) $fila->restringir_dominio_correo,
            'dominios_correo' => $dominios,
            'campos_registro' => $this->normalizarCampos(
                json_decode($fila->campos_registro ?? '{}', true) ?: []
            ),
            'logo_mime' => $fila->logo_mime,
            'logo_version' => $fila->logo_version,
            'logo_escala' => max(
                self::ESCALA_MINIMA,
                min(self::ESCALA_MAXIMA, (int) $fila->logo_escala)
            ),
            'logo_modo_oscuro' => array_key_exists($fila->logo_modo_oscuro, self::MODOS_OSCURO)
                ? $fila->logo_modo_oscuro
                : 'blanco',
            'actualizado_en' => $fila->actualizado_en,
        ];
    }

    private function predeterminada(): array
    {
        return [
            'nombre_organizacion' => self::NOMBRE_PREDETERMINADO,
            'restringir_dominio_correo' => false,
            'dominios_correo' => [],
            'campos_registro' => $this->normalizarCampos([]),
            'logo_mime' => null,
            'logo_version' => null,
            'logo_escala' => 100,
            'logo_modo_oscuro' => 'blanco',
            'actualizado_en' => null,
        ];
    }

    private function normalizarCampos(array $guardados): array
    {
        $campos = [];

        foreach (self::CAMPOS_REGISTRO as $clave => $definicion) {
            $modo = $guardados[$clave] ?? null;

            $campos[$clave] = array_key_exists((string) $modo, self::MODOS_CAMPO)
                ? $modo
                : $definicion['defecto'];
        }

        return $campos;
    }
}
