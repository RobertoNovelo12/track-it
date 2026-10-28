<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola lectura de la configuración de Organización por petición.
        $this->app->singleton(
            \App\Services\OrganizacionService::class
        );

        // Preferencias del usuario: una lectura por petición.
        $this->app->singleton(
            \App\Services\PreferenciasUsuarioService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Las comprobaciones de esquema van en caché (App\Support\Esquema): al migrar se olvidan.
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Database\Events\MigrationsEnded::class,
            fn () => \App\Support\Esquema::olvidar()
        );

        // El CSS ya se carga como stylesheet; evitar precargas redundantes al navegar con Livewire.
        \Illuminate\Support\Facades\Vite::usePreloadTagAttributes(
            fn ($src, $url): array|false => str_ends_with(parse_url($url, PHP_URL_PATH) ?? '', '.css') ? false : []
        );

        /*
        |--------------------------------------------------------------------------
        | Permisos por rol (roles.nivel)
        |--------------------------------------------------------------------------
        |
        | administrar  Solo Admin: organización (hoteles, sedes,
        |              departamentos, plantillas, organización) y
        |              Seguridad y roles.
        |
        | gestionar    Admin y Estándar: crear/editar equipos, bajas,
        |              asignaciones, mantenimientos, reportes y los
        |              catálogos de equipo (tipos, marcas, modelos).
        |
        | Lectura no tiene ninguno: solo consulta equipos.
        |
        */

        \Illuminate\Support\Facades\Gate::define(
            'administrar',
            fn ($user): bool => method_exists($user, 'esAdministrador') && $user->esAdministrador()
        );

        \Illuminate\Support\Facades\Gate::define(
            'gestionar',
            fn ($user): bool => method_exists($user, 'puedeGestionar') && $user->puedeGestionar()
        );
    }
}
