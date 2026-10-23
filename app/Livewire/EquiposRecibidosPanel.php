<?php

namespace App\Livewire;

use App\Services\EquiposRecibidosService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
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
        $atendido = $recibidos->atender((int) auth()->id(), $idRecibido);

        $this->pendientes = $recibidos->contarPendientes((int) auth()->id());

        if ($atendido) {
            $this->avisoConDeshacer('Se quitó de la bandeja.', [$idRecibido]);
        }
    }

    public function atenderTodos(EquiposRecibidosService $recibidos): void
    {
        $ids = $recibidos->idsPendientes((int) auth()->id());
        $atendidos = $recibidos->atenderTodos((int) auth()->id());

        $this->pendientes = 0;

        if ($atendidos > 0) {
            $this->avisoConDeshacer(
                $atendidos === 1 ? 'Se marcó 1 equipo como atendido.' : 'Se marcaron ' . $atendidos . ' equipos como atendidos.',
                $ids
            );
        }
    }

    /*
     * Aviso con botón «Deshacer» (toast-region): vuelve a llamar a
     * deshacerAtendidos con los mismos ids.
     */
    private function avisoConDeshacer(string $mensaje, array $ids): void
    {
        $this->dispatch(
            'toast',
            type: 'success',
            message: $mensaje,
            accion: [
                'texto' => 'Deshacer',
                'evento' => 'recibidos-deshacer',
                'datos' => ['ids' => array_values($ids)],
            ],
        );
    }

    #[On('recibidos-deshacer')]
    public function deshacerAtendidos(array $ids, EquiposRecibidosService $recibidos): void
    {
        $restaurados = $recibidos->restaurar((int) auth()->id(), $ids);

        $this->pendientes = $recibidos->contarPendientes((int) auth()->id());

        $this->dispatch(
            'toast',
            type: $restaurados > 0 ? 'info' : 'warning',
            message: $restaurados > 0
                ? ($restaurados === 1 ? 'El equipo volvió a la bandeja.' : 'Los ' . $restaurados . ' equipos volvieron a la bandeja.')
                : 'Ya no se puede deshacer (pasaron más de 10 minutos o el equipo volvió a llegar).',
        );
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
