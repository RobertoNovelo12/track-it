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
| Varios a la vez: $dispatch('papelera-mover-varios', { tipo, ids }).
| Se revisan todos primero; se muestran cuántos se moverán y cuáles
| no se pueden todavía (y por qué).
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

    /* Modo «varios» */
    public bool $varios = false;

    /** @var array<int, int> ids que sí se pueden mover */
    public array $movibles = [];

    /** @var array<int, array{etiqueta: string, motivo: string}> */
    public array $noMovibles = [];

    public int $noEncontrados = 0;

    public int $excedente = 0;

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

    #[On('papelera-mover-varios')]
    public function abrirVarios(string $tipo, array $ids): void
    {
        $this->reset();

        if (! PapeleraEntidades::existe($tipo)) {
            return;
        }

        $def = PapeleraEntidades::de($tipo);

        Gate::authorize($def['permiso']);

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id): bool => $id > 0)));

        if ($ids === []) {
            return;
        }

        $this->excedente = max(0, count($ids) - PapeleraService::MAXIMO_LOTE);
        $ids = array_slice($ids, 0, PapeleraService::MAXIMO_LOTE);

        $revision = app(PapeleraService::class)->revisarVarios($tipo, $ids);

        $this->varios = true;
        $this->tipo = $tipo;
        $this->nombre = $def['nombre'];
        $this->volverA = (string) request()->header('Referer', '');

        foreach ($ids as $id) {
            if (! isset($revision['etiquetas'][$id])) {
                $this->noEncontrados++;

                continue;
            }

            if (! empty($revision['bloqueos'][$id])) {
                $this->noMovibles[] = [
                    'etiqueta' => $revision['etiquetas'][$id],
                    'motivo' => ucfirst(implode(', ', array_column($revision['bloqueos'][$id], 'que'))),
                ];

                continue;
            }

            $this->movibles[] = $id;
        }

        $this->ayuda = $this->noMovibles !== [] ? $def['ayuda'] : null;
        $this->abierto = true;
    }

    public function confirmarVarios(): void
    {
        if (! $this->abierto || ! $this->varios || $this->movibles === []) {
            return;
        }

        Gate::authorize(PapeleraEntidades::de($this->tipo)['permiso']);

        try {
            $movidos = app(PapeleraService::class)->moverVarios($this->tipo, $this->movibles);
        } catch (PapeleraException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->dispatch(
            'toast',
            type: $movidos > 0 ? 'success' : 'warning',
            title: $movidos > 0 ? 'Movidos a la papelera' : 'No se movió nada',
            message: $movidos > 0
                ? ($movidos === 1 ? 'Se movió 1 elemento.' : 'Se movieron '.$movidos.' elementos.').' Se podrán restaurar durante '.PapeleraEntidades::DIAS.' días.'
                : 'Los elementos cambiaron mientras tanto. Actualiza la página e intenta de nuevo.',
        );

        $this->abierto = false;

        $this->redirect($this->destino(), navigate: true);
    }

    private function destino(): string
    {
        return $this->volverA !== '' && str_starts_with($this->volverA, url('/'))
            ? $this->volverA
            : route('dashboard');
    }

    public function confirmar(): void
    {
        if (! $this->abierto || $this->varios || $this->bloqueos !== [] || $this->error !== null) {
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
        $this->redirect($this->destino(), navigate: true);
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
