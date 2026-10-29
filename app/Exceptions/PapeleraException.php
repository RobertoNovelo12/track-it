<?php

namespace App\Exceptions;

use RuntimeException;

/*
 * Algo impide mover o restaurar un elemento de la papelera. El mensaje
 * se muestra tal cual al usuario; $bloqueos detalla qué depende de él.
 */
class PapeleraException extends RuntimeException
{
    public array $bloqueos = [];

    public ?string $ayuda = null;

    public static function bloqueado(array $bloqueos, ?string $ayuda): self
    {
        $error = new self('No se puede mover a la papelera mientras otros registros dependan de él.');
        $error->bloqueos = $bloqueos;
        $error->ayuda = $ayuda;

        return $error;
    }
}
