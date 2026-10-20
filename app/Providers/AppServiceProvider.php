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