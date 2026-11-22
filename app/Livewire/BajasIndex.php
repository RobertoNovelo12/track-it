<?php

namespace App\Livewire;

use App\Services\BajaEquipoService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class BajasIndex extends Component
{
    use WithPagination;

    public string $estado = 'resguardo';
    public string $buscar = '';
    public string $periodo = 'mes';
    public string $inicio = '';
    public string $fin = '';
    public array $seleccionados = [];
    public bool $confirmando = false;
    public string $autoriza = '';
    public string $fechaBaja = '';
    #[Locked] public array $pendientes = [];
    #[Locked] public int $completadas = 0;
    #[Locked] public bool $procesando = false;

    public function mount(): void
    {
        Gate::authorize('gestionar');
        $this->fechaBaja = now()->toDateString();

        // Las tarjetas del dashboard abren la pestaña indicada (?estado=resguardo|completadas).
        $estadoInicial = request()->query('estado');

        if (in_array($estadoInicial, ['resguardo', 'completadas'], true)) {
            $this->estado = $estadoInicial;
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['estado', 'buscar', 'periodo', 'inicio', 'fin'], true)) {
            if ($property === 'periodo') { $this->inicio = $this->fin = ''; }
            $this->seleccionados = [];
            $this->confirmando = false;
            $this->resetPage();
            $this->resetValidation();
        }
    }

    private function rango(): array
    {
        $this->validate([
            'estado' => ['required', Rule::in(['resguardo', 'completadas'])],
            'periodo' => ['required', Rule::in(['mes', 'semana', 'fecha'])],
        ]);
        $dates = [];
        foreach (['inicio', 'fin'] as $key) {
            $value = $this->$key;
            if ($value === '') { $dates[$key] = null; continue; }
            try {
                if ($this->periodo === 'mes') {
                    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) { throw new \InvalidArgumentException; }
                    $date = CarbonImmutable::createFromFormat('!Y-m', $value);
                    $date = $key === 'inicio' ? $date->startOfMonth() : $date->endOfMonth();
                } elseif ($this->periodo === 'semana') {
                    if (!preg_match('/^(\d{4})-W(\d{2})$/', $value, $matches)) { throw new \InvalidArgumentException; }
                    $date = CarbonImmutable::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfWeek();
                    if ($date->format('o-\WW') !== $value) { throw new \InvalidArgumentException; }
                    if ($key === 'fin') { $date = $date->endOfWeek(); }
                } else {
                    $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
                    if ($date->format('Y-m-d') !== $value) { throw new \InvalidArgumentException; }
                }
                $dates[$key] = $date->toDateString();
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([$key => 'Selecciona un período válido.']);
            }
        }
        if ($dates['inicio'] && $dates['fin'] && $dates['inicio'] > $dates['fin']) {
            throw ValidationException::withMessages(['fin' => 'El período final debe ser igual o posterior al inicial.']);
        }
        return $dates;
    }

    private function consulta()
    {
        $rango = $this->rango();
        $resguardo = $this->estado === 'resguardo';
        $fecha = $resguardo ? 'b.fecha_resguardo' : 'b.fecha_baja';
        $query = DB::table($resguardo ? 'bajas_resguardo as b' : 'bajas as b')
            ->join('equipos as e', 'e.id_equipo', '=', 'b.id_equipo')
            ->leftJoin('tipos_equipo as t', 't.id_tipo_equipo', '=', 'e.id_tipo_equipo')
            ->select('e.id_equipo', 'e.codigo_inventario', 'e.nombre_equipo', 'e.numero_serie', 't.nombre as tipo')
            ->selectRaw($fecha.' as fecha, '.($resguardo ? 'b.motivo' : 'b.motivo_baja').' as motivo');
        if ($resguardo) {
            $query->whereNull('b.fecha_completada')->whereNotExists(fn ($q) => $q->selectRaw('1')->from('bajas as finalizada')->whereColumn('finalizada.id_equipo', 'e.id_equipo'));
        }
        if (trim($this->buscar) !== '') {
            $term = '%'.trim($this->buscar).'%';
            $query->where(fn ($q) => $q->where('e.nombre_equipo', 'ilike', $term)->orWhere('e.codigo_inventario', 'ilike', $term)->orWhere('e.numero_serie', 'ilike', $term));
        }
        if ($rango['inicio']) { $query->whereDate($fecha, '>=', $rango['inicio']); }
        if ($rango['fin']) { $query->whereDate($fecha, '<=', $rango['fin']); }
        return $query;
    }

    public function seleccionarTodos(): void
    {
        Gate::authorize('gestionar');
        if ($this->procesando || $this->estado !== 'resguardo') { return; }
        $this->seleccionados = $this->consulta()->pluck('e.id_equipo')->map(fn ($id) => (string) $id)->all();
    }

    public function iniciarFinalizacion(): void
    {
        Gate::authorize('gestionar');
        if ($this->procesando) { return; }
        $this->validate([
            'estado' => ['in:resguardo'], 'seleccionados' => ['required', 'array', 'min:1'],
            'seleccionados.*' => ['required', 'integer', 'distinct', 'min:1'],
            'autoriza' => ['nullable', 'string', 'max:255'], 'fechaBaja' => ['required', 'date_format:Y-m-d'],
        ]);
        $ids = $this->consulta()->whereIn('e.id_equipo', $this->seleccionados)->pluck('e.id_equipo')->all();
        if (count($ids) !== count($this->seleccionados)) {
            throw ValidationException::withMessages(['seleccionados' => 'La selección cambió o contiene bajas ya completadas. Actualiza el listado.']);
        }
        if (DB::table('bajas_resguardo')->whereIn('id_equipo', $ids)->whereDate('fecha_resguardo', '>', $this->fechaBaja)->exists()) {
            throw ValidationException::withMessages(['fechaBaja' => 'La fecha de finalización no puede ser anterior al resguardo.']);
        }
        $this->pendientes = array_map(fn ($id) => ['id' => (int) $id, 'fecha' => $this->fechaBaja, 'autoriza' => trim($this->autoriza)], $ids);
        $this->completadas = 0;
        $this->procesando = true;
        $this->confirmando = false;
    }

    public function procesarSiguiente(): void
    {
        Gate::authorize('gestionar');
        if (!$this->procesando || $this->pendientes === []) { return; }
        $item = $this->pendientes[0];
        $service = app(BajaEquipoService::class);
        try {
            $creada = DB::transaction(function () use ($item, $service) {
                // Mismo orden de bloqueo que el servicio existente: equipo antes del resguardo.
                DB::table('equipos')->where('id_equipo', $item['id'])->lockForUpdate()->first();
                $resguardo = DB::table('bajas_resguardo')->where('id_equipo', $item['id'])->whereNull('fecha_completada')->lockForUpdate()->first();
                if (!$resguardo) { return false; }
                $service->darDeBaja($item['id'], $resguardo->motivo, $item['fecha'], $resguardo->observaciones, $item['autoriza'], (int) auth()->id());
                return true;
            });
        } catch (ValidationException $e) {
            $this->procesando = false;
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->procesando = false;
            $this->addError('seleccionados', 'No se pudo continuar. Las bajas ya completadas se conservaron; revisa el listado antes de reintentar.');
            return;
        }
        array_shift($this->pendientes);
        if ($creada) {
            $this->completadas++;
            try {
                $service->despuesDeBajas([$item['id']]);
                app(\App\Services\SystemCacheService::class)->refreshModule('asignaciones');
                \Illuminate\Support\Facades\Cache::forget('reportes:resumen:v3');
            } catch (\Throwable $e) {
                report($e);
                $this->procesando = false;
                $this->addError('seleccionados', 'La baja se guardó, pero falló la actualización de avisos. Recarga el listado antes de continuar.');
            }
        }
        if ($this->pendientes === []) {
            $this->procesando = false;
            $this->seleccionados = [];
            $this->resetPage();

            if ($this->completadas > 0 && !$this->getErrorBag()->any()) {
                $this->dispatch(
                    'toast',
                    type: 'success',
                    title: 'Baja completada',
                    message: $this->completadas === 1
                        ? 'Se completó la baja del equipo. Su historial se conserva.'
                        : 'Se completó la baja de '.$this->completadas.' equipos. Su historial se conserva.'
                );
            }
        }
    }

    public function render()
    {
        Gate::authorize('gestionar');
        try { $registros = $this->consulta()->orderByDesc('fecha')->orderBy('e.id_equipo')->paginate(20); }
        catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $registros = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
        return view('livewire.bajas-index', compact('registros'));
    }
}