<?php

namespace App\Livewire;

use App\Services\DevolucionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| Asignaciones › Devolver equipo
|--------------------------------------------------------------------------
|
| Cierra la asignación activa de un equipo (DevolucionService). Al
| terminar muestra la confirmación con el acta y deja el formulario
| listo para la siguiente devolución.
|
*/

class DevolucionCreate extends Component
{
    public string $idEquipo = '';
    public string $fecha = '';
    public string $motivo = '';
    public string $motivoOtro = '';
    public string $recibidoPor = '';
    public string $observaciones = '';

    // Última devolución registrada (confirmación + acta).
    public ?array $ultima = null;

    public function mount(?int $equipo = null): void
    {
        Gate::authorize('gestionar');

        $this->fecha = now()->toDateString();
        $this->recibidoPor = $this->nombreUsuarioActual();

        if ($equipo) {
            $this->idEquipo = (string) $equipo;
        }
    }

    public function guardar(DevolucionService $devoluciones): void
    {
        Gate::authorize('gestionar');

        $this->validate([
            'idEquipo' => ['required', 'integer'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'motivo' => ['required', Rule::in(array_keys(DevolucionService::MOTIVOS))],
            'motivoOtro' => ['required_if:motivo,otro', 'nullable', 'string', 'max:255'],
            'recibidoPor' => ['required', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'idEquipo.required' => 'Elige el equipo que se devuelve.',
            'fecha.before_or_equal' => 'La fecha de devolución no puede ser futura.',
            'motivo.required' => 'Elige el motivo de la devolución.',
            'motivoOtro.required_if' => 'Describe el motivo.',
            'recibidoPor.required' => 'Escribe quién recibe el equipo.',
        ]);

        $asignacion = $devoluciones->asignacionActiva((int) $this->idEquipo);

        $motivo = $this->motivo === 'otro'
            ? trim($this->motivoOtro)
            : DevolucionService::MOTIVOS[$this->motivo];

        // Fecha elegida con la hora actual (para ordenar el historial).
        $fecha = Carbon::parse($this->fecha)->setTimeFrom(now());

        $idMovimiento = $devoluciones->devolver(
            (int) $this->idEquipo,
            $motivo,
            $this->observaciones,
            $this->recibidoPor,
            $fecha
        );

        $this->ultima = [
            'movimiento' => $idMovimiento,
            'codigo' => (string) ($asignacion['codigo_inventario'] ?? ''),
            'nombre' => (string) ($asignacion['nombre_equipo'] ?? ''),
            'colaborador' => (string) ($asignacion['nombre_colaborador'] ?? ''),
            'id_equipo' => (int) $this->idEquipo,
        ];

        $this->reset(['idEquipo', 'motivo', 'motivoOtro', 'observaciones']);
        $this->fecha = now()->toDateString();
        $this->resetValidation();

        $this->dispatch('toast', type: 'success', message: 'Equipo devuelto: ya está disponible para asignarse.');
    }

    private function nombreUsuarioActual(): string
    {
        $usuario = auth()->user();

        return trim(($usuario->nombres ?? '') . ' ' . ($usuario->apellido_paterno ?? '')) ?: (string) ($usuario->username ?? '');
    }

    public function render(DevolucionService $devoluciones): View
    {
        return view('livewire.devolucion-create', [
            'disponible' => $devoluciones->disponible(),
            'equipos' => $devoluciones->equiposAsignados(),
            'asignacion' => filled($this->idEquipo) ? $devoluciones->asignacionActiva((int) $this->idEquipo) : null,
            'motivos' => DevolucionService::MOTIVOS,
        ]);
    }
}
