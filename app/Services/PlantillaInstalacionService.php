<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use LogicException;

class PlantillaInstalacionService
{
    /*
     * configuracion_instalacion es inmutable (un trigger impide
     * modificarla), así que se lee una sola vez y se guarda en caché
     * sin vencimiento. Antes se consultaba en cada etiqueta que pedía
     * una vista (p. ej. 3 consultas de ~165 ms por carga de Asignaciones).
     * `php artisan optimize:clear` / `cache:clear` la vuelve a leer.
     */
    private const CACHE_KEY = 'trackit.configuracion_instalacion.v1';

    // Memoria de la petición: evita incluso leer la caché varias veces.
    private static ?array $memoria = null;

    private const DEFINICIONES = [
        'hoteles' => [
            'nombre' => 'Hotelería',
            'version' => 1,

            'etiquetas' => [
                'unidad_singular' => 'Hotel',
                'unidad_plural' => 'Hoteles',

                'buscar_unidad' => 'Buscar hotel...',

                'seleccionar_unidad' =>
                    'Selecciona un hotel.',

                'seleccionar_unidad_primero' =>
                    'Elige un hotel primero',

                'todas_unidades' =>
                    'Todos los hoteles',

                'atiende_todas_unidades' =>
                    'Atiende todos los hoteles',

                'cobertura' =>
                    'Hoteles que atiende',

                'sin_unidades' =>
                    'Sin hoteles asignados',

                'incluye_futuras' =>
                    'Incluye los hoteles que se agreguen después.',

                'de_la_organizacion' => 'del hotel',

                'firma_gerente' => 'Gerente de Hotel / General Manager',

                'departamento_singular' => 'Departamento',
                'departamento_plural' => 'Departamentos',
            ],
        ],

        'escuelas' => [
            'nombre' => 'Educación',
            'version' => 1,

            'etiquetas' => [
                'unidad_singular' => 'Edificio',
                'unidad_plural' => 'Edificios',

                'buscar_unidad' => 'Buscar edificio...',

                'seleccionar_unidad' =>
                    'Selecciona un edificio.',

                'seleccionar_unidad_primero' =>
                    'Elige un edificio primero',

                'todas_unidades' =>
                    'Todos los edificios',

                'atiende_todas_unidades' =>
                    'Atiende todos los edificios',

                'cobertura' =>
                    'Edificios que atiende',

                'sin_unidades' =>
                    'Sin edificios asignados',

                'incluye_futuras' =>
                    'Incluye los edificios que se agreguen después.',

                'de_la_organizacion' => 'de la institución',

                'firma_gerente' => 'Responsable del edificio',

                'departamento_singular' => 'Departamento',
                'departamento_plural' => 'Departamentos',
            ],
        ],
    ];

    public function actual(): array
    {
        if (self::$memoria !== null) {
            return self::$memoria;
        }

        $fila = Cache::get(self::CACHE_KEY);

        if (! is_array($fila)) {
            $registro = DB::table('configuracion_instalacion')
                ->where('id', 1)
                ->first();

            $fila = $registro
                ? [
                    'plantilla' => (string) $registro->plantilla,
                    'version_plantilla' => (int) $registro->version_plantilla,
                    'configurada_en' => $registro->configurada_en,
                ]
                : null;

            // Solo se guarda una configuración válida (si falta, se reintenta).
            if ($fila !== null) {
                Cache::forever(self::CACHE_KEY, $fila);
            }
        }

        $config = $fila !== null ? (object) $fila : null;

        if (!$config) {
            throw new LogicException(
                'La instalación todavía no tiene una plantilla definida.'
            );
        }

        /*
         * Un despliegue puede forzar otra plantilla con
         * TRACKIT_PLANTILLA (config/plantilla.php) sin tocar la base.
         */
        $forzada = (string) config('plantilla.forzada', '');

        if ($forzada !== '' && isset(self::DEFINICIONES[$forzada])) {
            $config->plantilla = $forzada;
            $config->version_plantilla = self::DEFINICIONES[$forzada]['version'];
        }

        $definition = self::DEFINICIONES[$config->plantilla]
            ?? null;

        if (
            !$definition
            || (int) $config->version_plantilla
                !== $definition['version']
        ) {
            throw new LogicException(
                'La plantilla o su versión no está soportada por esta aplicación.'
            );
        }

        return self::$memoria = [
            'clave' => $config->plantilla,
            'nombre' => $definition['nombre'],
            'version' => $definition['version'],
            'configurada_en' => $config->configurada_en,
            'etiquetas' => self::conMinusculas($definition['etiquetas']),
        ];
    }

    public function etiquetas(): array
    {
        return $this->actual()['etiquetas'];
    }

    /**
     * Atajo para vistas y mensajes:
     * PlantillaInstalacionService::t('unidad') => «hotel» / «edificio».
     * Si la instalación aún no tiene plantilla, usa la de hoteles.
     */
    public static function t(string $clave): string
    {
        try {
            $etiquetas = app(self::class)->etiquetas();
        } catch (\Throwable $e) {
            $etiquetas = self::conMinusculas(self::DEFINICIONES['hoteles']['etiquetas']);
        }

        return (string) ($etiquetas[$clave] ?? $clave);
    }

    /**
     * Plantillas disponibles con sus etiquetas (para el asistente de configuración).
     *
     * @return array<string, array{nombre: string, etiquetas: array}>
     */
    public static function definiciones(): array
    {
        return array_map(
            fn (array $d) => ['nombre' => $d['nombre'], 'etiquetas' => self::conMinusculas($d['etiquetas'])],
            self::DEFINICIONES
        );
    }

    /** Plantilla fijada con TRACKIT_PLANTILLA (si existe, el asistente no pregunta). */
    public static function forzada(): ?string
    {
        $forzada = (string) config('plantilla.forzada', '');

        return $forzada !== '' && isset(self::DEFINICIONES[$forzada]) ? $forzada : null;
    }

    /**
     * Cambia la plantilla de la instalación. Solo lo usa el asistente de
     * configuración inicial; el trigger de configuracion_instalacion lo
     * permite únicamente con trackit.permitir_cambio_plantilla dentro de
     * esta transacción.
     */
    public function cambiar(string $clave): void
    {
        if (! isset(self::DEFINICIONES[$clave])) {
            throw new LogicException("Plantilla desconocida: {$clave}");
        }

        DB::transaction(function () use ($clave) {
            DB::statement("SET LOCAL trackit.permitir_cambio_plantilla = 'on'");

            DB::table('configuracion_instalacion')
                ->where('id', 1)
                ->update([
                    'plantilla' => $clave,
                    'version_plantilla' => self::DEFINICIONES[$clave]['version'],
                    'configurada_en' => now(),
                ]);
        });

        Cache::forget(self::CACHE_KEY);
        self::$memoria = null;
    }

    // Formas en minúsculas para usarlas dentro de frases.
    private static function conMinusculas(array $etiquetas): array
    {
        return $etiquetas + [
            'unidad' => mb_strtolower($etiquetas['unidad_singular']),
            'unidades' => mb_strtolower($etiquetas['unidad_plural']),
        ];
    }
}