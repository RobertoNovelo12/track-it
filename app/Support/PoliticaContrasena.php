<?php

namespace App\Support;

use App\Rules\ContrasenaNoFiltrada;

/*
|--------------------------------------------------------------------------
| Política de contraseñas (una sola para toda la app)
|--------------------------------------------------------------------------
|
| Mínimo 10 caracteres, con mayúscula, minúscula, número y carácter
| especial, sin espacios, y que no aparezca en filtraciones públicas.
| Se usa al crear usuarios, al cambiar la contraseña (Ajustes y
| contraseña temporal) y en el registro.
|
*/

class PoliticaContrasena
{
    public const MINIMO = 10;

    public const DESCRIPCION = 'Al menos 10 caracteres, con mayúscula, minúscula, número y carácter especial, sin espacios.';

    public static function reglas(): array
    {
        return [
            'min:'.self::MINIMO,
            'max:255',
            'regex:/^(?=.*\p{Lu})(?=.*\p{Ll})(?=.*[0-9])(?=.*[^\p{L}\p{N}\s])\S+$/u',
            new ContrasenaNoFiltrada,
        ];
    }

    public static function mensajes(string $campo): array
    {
        return [
            $campo.'.min' => 'La contraseña debe tener al menos '.self::MINIMO.' caracteres.',
            $campo.'.max' => 'La contraseña es demasiado larga.',
            $campo.'.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y un carácter especial, sin espacios.',
        ];
    }
}
