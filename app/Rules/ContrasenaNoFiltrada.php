<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Contracts\Validation\ValidationRule;

/*
|--------------------------------------------------------------------------
| Contraseña no filtrada
|--------------------------------------------------------------------------
|
| Rechaza contraseñas que aparecen en filtraciones públicas (servicio
| Have I Been Pwned, el mismo que usa Password::uncompromised()).
| Solo se envían los primeros 5 caracteres del hash SHA-1, nunca la
| contraseña. Si el servicio no responde, se permite (no bloquea).
|
*/

class ContrasenaNoFiltrada implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        try {
            $segura = app(UncompromisedVerifier::class)->verify([
                'value' => $value,
                'threshold' => 0,
            ]);
        } catch (\Throwable) {
            return;
        }

        if (! $segura) {
            $fail('Esta contraseña aparece en filtraciones de datos públicas. Elige otra.');
        }
    }
}
