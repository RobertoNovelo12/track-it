<?php

namespace App\Livewire;

use App\Services\EquiposRecibidosService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Equipos recibidos (barra superior, escritorio)
|--------------------------------------------------------------------------
|
| Bandeja personal de lo que el usuario envió desde su teléfono con el
| escáner. Se actualiza cada 15 s (solo con la pestaña visible) y avisa
| con un aviso breve cuando llegan equipos nuevos; no usa Notificaciones
| para no llenarlas cuando se escanea una bodega completa.
|
*/

class EquiposRecibidosPanel extends Component
{
    #[Locked]
    public int $pendientes = 0;

    public function mount(EquiposRecibidosService $recibidos): void
    {
        $this->pendientes = $recibidos->contarPendientes((int) auth()->id());
    }

    /*
     * Llamado por wire:poll: avisa si llegaron equipos nuevos.
     */
    public function refrescar(EquiposRecibidosService $recibidos): void
    {
        $actual = $recibidos->contarPendientes((int) auth()->id());

        if ($actual > $this->pendientes) {
            $nuevos = $actual - $this->pendientes;

            $this->dispatch(
                'toast',
                type: 'info',
                title: 'Equipos recibidos',
                message: $nuevos === 1
                    ? 'Llegó 1 equipo desde tu teléfono.'
                    : 'Llegaron ' . $nuevos . ' equipos desde tu teléfono.',
            );
        }

        $this->pendientes = $actual;
    }

    public function atender(int $idRecibido, EquiposRecibidosService $recibidos): void
    {
        $recibidos->atender((int) auth()->id(), $idRecibido);

        $this->pendientes = $recibidos->contarPendientes((int) auth()->id());
    }

    public function atenderTodos(EquiposRecibidosService $recibidos): void
    {
        $atendidos = $recibidos->atenderTodos((int) auth()->id());

        $this->pendientes = 0;

        if ($atendidos > 0) {
            $this->dispatch('toast', type: 'success', message: 'La bandeja quedó vacía.');
        }
    }

    public function render(EquiposRecibidosService $recibidos): View
    {
        return view('livewire.equipos-recibidos-panel', [
            'equipos' => $this->pendientes > 0
                ? $recibidos->pendientes((int) auth()->id())
                : [],
            'puedeDarDeBaja' => Gate::allows('gestionar'),
        ]);
    }
}
