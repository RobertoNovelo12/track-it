<?php

namespace App\Livewire;

use App\Services\PlantillaInstalacionService;
use App\Services\PreferenciasUsuarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Ajustes › Preferencias
|--------------------------------------------------------------------------
|
| Componente propio (no dentro de AjustesCuenta) para no cargar más
| lógica en la pantalla de Ajustes.
|
*/

class AjustesPreferencias extends Component
{
    public string $tema = 'system';
    public string $densidad = 'comoda';
    public string $pantallaInicio = 'dashboard';
    public string $registrosPorPagina = '10';
    public ?string $idUnidadPredeterminada = null;
    public string $zonaHoraria = 'America/Mexico_City';
    public string $formatoHora = '24';

    // Accesibilidad
    public string $tamanoTexto = 'normal';
    public bool $reducirMovimiento = false;
    public bool $altoContraste = false;
    public bool $resaltarFoco = false;

    public function mount(PreferenciasUsuarioService $preferencias): void
    {
        $actuales = $preferencias->obtener();

        $this->tema = $actuales['tema'];
        $this->densidad = $actuales['densidad'];
        $this->pantallaInicio = $actuales['pantalla_inicio'];
        $this->registrosPorPagina = (string) $actuales['registros_por_pagina'];
        $this->idUnidadPredeterminada = $actuales['id_unidad_predeterminada'] !== null
            ? (string) $actuales['id_unidad_predeterminada']
            : null;
        $this->zonaHoraria = $actuales['zona_horaria'];
        $this->formatoHora = $actuales['formato_hora'];

        $this->tamanoTexto = $actuales['tamano_texto'];
        $this->reducirMovimiento = (bool) $actuales['reducir_movimiento'];
        $this->altoContraste = (bool) $actuales['alto_contraste'];
        $this->resaltarFoco = (bool) $actuales['resaltar_foco'];
    }

    public function save(PreferenciasUsuarioService $preferencias): void
    {
        $validated = $this->validate([
            'tema' => ['required', Rule::in(array_keys(PreferenciasUsuarioService::TEMAS))],
            'densidad' => ['required', Rule::in(array_keys(PreferenciasUsuarioService::DENSIDADES))],
            'pantallaInicio' => ['required', Rule::in(array_keys($this->pantallasDisponibles()))],
            'registrosPorPagina' => ['required', Rule::in(array_map('strval', PreferenciasUsuarioService::REGISTROS_POR_PAGINA))],
            'idUnidadPredeterminada' => [
                'nullable',
                'integer',
                Rule::exists('unidades_organizativas', 'id_unidad')
                    ->where(fn ($query) => $query->where('activo', true)),
            ],
            'zonaHoraria' => ['required', Rule::in(array_keys(PreferenciasUsuarioService::ZONAS_HORARIAS))],
            'formatoHora' => ['required', Rule::in(array_keys(PreferenciasUsuarioService::FORMATOS_HORA))],
            'tamanoTexto' => ['required', Rule::in(array_keys(PreferenciasUsuarioService::TAMANOS_TEXTO))],
            'reducirMovimiento' => ['boolean'],
            'altoContraste' => ['boolean'],
            'resaltarFoco' => ['boolean'],
        ], [
            'pantallaInicio.in' => 'Esa pantalla no está disponible para tu rol.',
            'idUnidadPredeterminada.exists' => 'El elemento seleccionado ya no está disponible.',
        ]);

        $guardadas = $preferencias->guardar((int) auth()->id(), [
            'tema' => $validated['tema'],
            'densidad' => $validated['densidad'],
            'pantalla_inicio' => $validated['pantallaInicio'],
            'registros_por_pagina' => (int) $validated['registrosPorPagina'],
            'id_unidad_predeterminada' => $validated['idUnidadPredeterminada'] ?? null,
            'zona_horaria' => $validated['zonaHoraria'],
            'formato_hora' => $validated['formatoHora'],
            'tamano_texto' => $validated['tamanoTexto'],
            'reducir_movimiento' => (bool) $validated['reducirMovimiento'],
            'alto_contraste' => (bool) $validated['altoContraste'],
            'resaltar_foco' => (bool) $validated['resaltarFoco'],
        ]);

        /*
         * El navegador aplica tema y densidad al instante
         * (layouts/app.blade.php escucha este evento).
         */
        $this->dispatch(
            'preferencias-actualizadas',
            tema: $guardadas['tema'],
            densidad: $guardadas['densidad'],
            accesibilidad: $preferencias->atributosAccesibilidad($guardadas),
            message: 'Tus preferencias se guardaron correctamente.'
        );
    }

    private function pantallasDisponibles(): array
    {
        return array_filter(
            PreferenciasUsuarioService::PANTALLAS_INICIO,
            fn (array $pantalla): bool => ! $pantalla['permiso'] || Gate::allows($pantalla['permiso'])
        );
    }

    public function render(PlantillaInstalacionService $plantilla): View
    {
        $unidades = DB::table('unidades_organizativas')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_unidad', 'nombre'])
            ->map(fn ($u) => ['value' => (string) $u->id_unidad, 'label' => (string) $u->nombre])
            ->all();

        return view('livewire.ajustes-preferencias', [
            'pantallas' => $this->pantallasDisponibles(),
            'unidades' => $unidades,
            'etiquetas' => $plantilla->etiquetas(),
            'accesibilidadDisponible' => app(PreferenciasUsuarioService::class)->accesibilidadDisponible(),
        ]);
    }
}
