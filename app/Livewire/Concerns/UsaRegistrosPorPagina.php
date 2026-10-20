<?php

namespace App\Livewire\Concerns;

use App\Services\PreferenciasUsuarioService;

/*
|--------------------------------------------------------------------------
| Registros por página predeterminados
|--------------------------------------------------------------------------
|
| Usa la cantidad elegida en Ajustes › Preferencias como valor inicial
| de la tabla. Livewire ejecuta este método al montar el componente
| (mount + nombre del trait).
|
| Solo cambia el valor si sigue en el predeterminado (10) y la URL no
| trae ?per_page, así no pisa una elección hecha por el usuario.
|
| Un componente con otra(s) propiedad(es) sobrescribe
| propiedadesRegistrosPorPagina().
|
*/

trait UsaRegistrosPorPagina
{
    public function mountUsaRegistrosPorPagina(): void
    {
        if (filled(request()->query('per_page'))) {
            return;
        }

        $valor = app(PreferenciasUsuarioService::class)->registrosPorPagina();

        foreach ($this->propiedadesRegistrosPorPagina() as $propiedad) {
            if (
                property_exists($this, $propiedad)
                && (int) $this->{$propiedad} === 10
            ) {
                $this->{$propiedad} = $valor;
            }
        }
    }

    protected function propiedadesRegistrosPorPagina(): array
    {
        return ['perPage'];
    }
}
