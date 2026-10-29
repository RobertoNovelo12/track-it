<?php

namespace App\Livewire;

use App\Exceptions\PapeleraException;
use App\Services\PapeleraService;
use App\Support\PapeleraEntidades;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

/*
|--------------------------------------------------------------------------
| «Mover a la papelera» (confirmación global)
|--------------------------------------------------------------------------
|
| Vive en el layout. Cualquier tabla abre la confirmación con:
|   $dispatch('papelera-mover', { tipo: 'marca', id: 5 })
| (ver PapeleraEntidades::accion()). Si algo depende del registro, en
| lugar de confirmar explica qué hay que cambiar o borrar primero.
| Al mover, recarga la página actual para que la lista se actualice.
|
*/

class PapeleraConfirmar extends Component
{
    public bool $abierto = false;

    public string $tipo = '';

    public int $idRegistro = 0;

    public string $nombre = '';

    public string $etiqueta = '';

    public array $bloqueos = [];

    public ?string $ayuda = null;

    public ?string $error = null;

    public string $volverA = '';

    #[On('papelera-mover')]
    public function abrir(string $tipo, int $id): void
    {
        $this->reset('bloqueos', 'ayuda', 'error');

        if (! PapeleraEntidades::existe($tipo)) {
            return;
        }

        $def = PapeleraEntidades::de($tipo);

        Gate::authorize($def['permiso']);

        $servicio = app(PapeleraService::class);

        $this->tipo = $tipo;
        $this->idRegistro = $id;
        $this->nombre = $def['nombre'];
        $this->etiqueta = (string) ($servicio->etiqueta($tipo, $id) ?? '');
        $this->volverA = (string) request()->header('Referer', '');

        if ($this->etiqueta === '') {
            $this->error = 'Ese registro ya no existe. Actualiza la página.';
        } else {
            $this->bloqueos = $servicio->bloqueos($tipo, $id);
            $this->ayuda = $this->bloqueos !== [] ? $def['ayuda'] : null;
        }

        $this->abierto = true;
    }

    public function confirmar(): void
    {
        if (! $this->abierto || $this->bloqueos !== [] || $this->error !== null) {
            return;
        }

        Gate::authorize(PapeleraEntidades::de($this->tipo)['permiso']);

        try {
            app(PapeleraService::class)->mover($this->tipo, $this->idRegistro);
        } catch (PapeleraException $e) {
            $this->bloqueos = $e->bloqueos;
            $this->ayuda = $e->ayuda;
            $this->error = $e->bloqueos === [] ? $e->getMessage() : null;

            return;
        }

        $this->dispatch(
            'toast',
            type: 'success',
            title: 'Movido a la papelera',
            message: '«'.$this->etiqueta.'» se podrá restaurar durante '.PapeleraEntidades::DIAS.' días.',
        );

        $this->abierto = false;

        // Recargar la pantalla de origen (sin recargar todo el sitio).
        $destino = $this->volverA !== '' && str_starts_with($this->volverA, url('/'))
            ? $this->volverA
            : route('dashboard');

        $this->redirect($destino, navigate: true);
    }

    public function cerrar(): void
    {
        $this->reset();
    }

    public function render(): View
    {
        return view('livewire.papelera-confirmar');
    }
}
