<?php

namespace App\Livewire;

use App\Exceptions\PapeleraException;
use App\Services\PapeleraService;
use App\Support\PapeleraEntidades;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/*
|--------------------------------------------------------------------------
| Papelera (solo administradores)
|--------------------------------------------------------------------------
|
| Tabla con selección (como Inventario): restaurar o eliminar
| definitivamente uno o varios elementos. Al abrirla se eliminan los
| que ya cumplieron 30 días (además del comando programado).
|
*/

class PapeleraIndex extends Component
{
    use WithPagination;

    private const ORDENES = [
        'etiqueta' => 'etiqueta',
        'tipo' => 'tipo',
        'eliminado_por' => 'eliminado_por_nombre',
        'eliminado' => 'eliminado_en',
        'expira' => 'expira_en',
    ];

    #[Url(as: 'buscar')]
    public string $buscar = '';

    #[Url(as: 'tipo')]
    public string $tipo = '';

    public int $perPage = 10;

    public string $sortField = 'eliminado';

    public string $sort = 'desc';

    /** @var array<int, string> */
    public array $seleccionados = [];

    public ?string $mensaje = null;

    public string $tipoMensaje = 'success';

    public function mount(): void
    {
        Gate::authorize('administrar');

        app(PapeleraService::class)->purgarVencidos();
    }

    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'tipo', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function sortBy(string $campo): void
    {
        if (! array_key_exists($campo, self::ORDENES)) {
            return;
        }

        if ($this->sortField === $campo) {
            $this->sort = $this->sort === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $campo;
            $this->sort = 'asc';
        }

        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset('buscar', 'tipo');
        $this->resetPage();
    }

    /*
     * Casilla del encabezado: marca o desmarca toda la página.
     */
    public function seleccionarPagina(): void
    {
        $ids = $this->consulta()
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('id_papelera')
            ->map(fn ($id) => (string) $id)
            ->all();

        $todos = $ids !== [] && array_diff($ids, $this->seleccionados) === [];

        $this->seleccionados = $todos
            ? array_values(array_diff($this->seleccionados, $ids))
            : array_values(array_unique(array_merge($this->seleccionados, $ids)));
    }

    public function restaurar(int $id): void
    {
        $this->restaurarVarios([$id]);
    }

    /* La selección vive en el navegador (Alpine) y llega aquí. */
    public function restaurarSeleccion(array $ids = []): void
    {
        $this->restaurarVarios(array_map('intval', $ids));
    }

    public function eliminar(int $id): void
    {
        $this->eliminarVarios([$id]);
    }

    public function eliminarSeleccion(array $ids = []): void
    {
        $this->eliminarVarios(array_map('intval', array_slice($ids, 0, 500)));
    }

    private function restaurarVarios(array $ids): void
    {
        Gate::authorize('administrar');

        // Cada uno por separado (si uno falla, los demás se restauran);
        // la caché se actualiza una sola vez al final.
        ['restaurados' => $restaurados, 'errores' => $errores] = app(PapeleraService::class)->restaurarVarios($ids);

        $this->seleccionados = [];

        if ($errores !== []) {
            $this->avisar(
                ($restaurados !== [] ? 'Se restauraron '.count($restaurados).'. ' : '').implode(' ', $errores),
                'error'
            );

            return;
        }

        $this->avisar(
            count($restaurados) === 1
                ? '«'.$restaurados[0].'» se restauró correctamente.'
                : 'Se restauraron '.count($restaurados).' elementos.'
        );
    }

    private function eliminarVarios(array $ids): void
    {
        Gate::authorize('administrar');

        // Por lotes: unas cuantas consultas para todos, no tres por elemento.
        $eliminados = app(PapeleraService::class)->eliminarDefinitivoVarios($ids);

        $this->seleccionados = [];

        $this->avisar(
            $eliminados === 1
                ? 'Se eliminó definitivamente.'
                : 'Se eliminaron definitivamente '.$eliminados.' elementos.'
        );
    }

    private function avisar(string $mensaje, string $tipo = 'success'): void
    {
        $this->mensaje = $mensaje;
        $this->tipoMensaje = $tipo;

        $this->dispatch(
            'toast',
            type: $tipo,
            title: $tipo === 'success' ? 'Papelera' : 'No se pudo completar',
            message: $mensaje,
        );

        $this->resetPage();
    }

    private function consulta()
    {
        $buscar = trim($this->buscar);

        return DB::table('papelera')
            ->when($this->tipo !== '' && PapeleraEntidades::existe($this->tipo), fn ($q) => $q->where('tipo', $this->tipo))
            ->when($buscar !== '', fn ($q) => $q->where(function ($sub) use ($buscar): void {
                $patron = '%'.addcslashes($buscar, '%_\\').'%';
                $sub->where('etiqueta', 'ILIKE', $patron)
                    ->orWhere('eliminado_por_nombre', 'ILIKE', $patron);
            }))
            ->orderBy(self::ORDENES[$this->sortField] ?? 'eliminado_en', $this->sort === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id_papelera');
    }

    public function render(): View
    {
        $nombres = PapeleraEntidades::nombres();

        $elementos = $this->consulta()
            ->select(['id_papelera', 'tipo', 'id_registro', 'etiqueta', 'detalle', 'eliminado_por_nombre', 'eliminado_en', 'expira_en'])
            ->paginate($this->perPage);

        $elementos->getCollection()->transform(function ($fila) use ($nombres) {
            $fila->tipo_nombre = $nombres[$fila->tipo] ?? ucfirst(str_replace('_', ' ', $fila->tipo));
            $dias = (int) ceil(now()->diffInHours(Carbon::parse($fila->expira_en), false) / 24);
            $fila->dias_restantes = max(0, $dias);

            return $fila;
        });

        return view('livewire.papelera-index', [
            'elementos' => $elementos,
            'tipos' => collect($nombres)->map(fn ($nombre, $clave) => ['value' => $clave, 'label' => $nombre])->sortBy('label')->values()->all(),
            'total' => DB::table('papelera')->count(),
        ]);
    }
}
