<?php

namespace App\Console\Commands;

use App\Services\PapeleraService;
use Illuminate\Console\Command;

/*
 * Elimina definitivamente lo que lleva 30 días en la papelera.
 * Programado a diario en routes/console.php (también corre al abrir
 * la papelera, por si el programador no está activo en el servidor).
 */
class VaciarPapelera extends Command
{
    protected $signature = 'trackit:vaciar-papelera';

    protected $description = 'Elimina definitivamente los elementos con más de 30 días en la papelera';

    public function handle(PapeleraService $papelera): int
    {
        $eliminados = $papelera->purgarVencidos();

        $this->info($eliminados === 1 ? '1 elemento eliminado.' : $eliminados.' elementos eliminados.');

        return self::SUCCESS;
    }
}
