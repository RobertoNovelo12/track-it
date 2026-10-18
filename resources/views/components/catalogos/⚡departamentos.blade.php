<?php

use App\Models\Departamento;
use App\Models\Hotel;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = 'todos';
    public string $hotelId = '';

    public function updatedSearch(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedEstado(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function updatedHotelId(): void
    {
        $this->resetPage(pageName: 'departamentosPage');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->estado = 'todos';
        $this->hotelId = '';

        $this->resetPage(pageName: 'departamentosPage');
    }

    public function departamentos()
    {
        $search = trim($this->search);

        return Departamento::query()
            ->principales()
            ->with([
                'hoteles' => fn ($query) => $query
                    ->orderBy('unidades_organizativas.nombre'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter
                        ->where('departamentos.nombre', 'ilike', "%{$search}%")
                        ->orWhere('departamentos.codigo', 'ilike', "%{$search}%")
                        ->orWhere('departamentos.responsable', 'ilike', "%{$search}%");
                });
            })
            ->when(
                in_array($this->estado, ['activos', 'inactivos'], true),
                function ($query) {
                    $query->where(
                        'departamentos.activo',
                        $this->estado === 'activos'
                    );
                }
            )
            ->when($this->hotelId !== '', function ($query) {
                $hotelId = (int) $this->hotelId;

                $query->where(function ($coverage) use ($hotelId) {
                    $coverage
                        ->where('departamentos.atiende_todas_unidades', true)
                        ->orWhereHas('hoteles', function ($hoteles) use ($hotelId) {
                            $hoteles->where('unidades_organizativas.id_unidad', $hotelId);
                        });
                });
            })
            ->orderBy('departamentos.nombre')
            ->orderBy('departamentos.id_departamento')
            ->paginate(
                perPage: 15,
                pageName: 'departamentosPage'
            );
    }

    public function hoteles()
    {
        return Hotel::query()
            ->orderBy('nombre')
            ->get([
                'id_unidad',
                'nombre',
                'activo',
            ]);
    }
};
?>

<div>
    @php
        $departamentos = $this->departamentos();
        $hoteles = $this->hoteles();
    @endphp

    <section class="overflow-hidden rounded-xl border border-[var(--theme-border)] bg-[var(--theme-surface)]">
        {{-- ENCABEZADO --}}
        <div class="border-b border-[var(--theme-border)] px-4 py-4 sm:px-5">
            <h2 class="text-sm font-semibold text-[var(--theme-text-strong)]">
                Departamentos
            </h2>

            <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                Consulta los departamentos y los hoteles que atiende cada uno.
            </p>
        </div>

        {{-- FILTROS --}}
        <div class="space-y-4 border-b border-[var(--theme-border)] p-4 sm:p-5">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="min-w-0 xl:col-span-2">
                    <label
                        for="departamentos-search"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Buscar departamento
                    </label>

                    <input
                        id="departamentos-search"
                        type="search"
                        wire:model.live.debounce.350ms="search"
                        placeholder="Nombre, código o responsable..."
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    >
                </div>

                <div>
                    <label
                        for="departamentos-hotel"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['unidad_singular'] }}
                    </label>

                    <select
                        id="departamentos-hotel"
                        wire:model.live="hotelId"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    >
                        <option value="">Todos los hoteles</option>

                        @foreach ($hoteles as $hotel)
                            <option value="{{ $hotel->id_unidad }}">
                                {{ $hotel->nombre }}{{ $hotel->activo ? '' : ' (inactivo)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="departamentos-estado"
                        class="mb-1.5 block text-xs text-[var(--theme-text-muted)]"
                    >
                        Estado
                    </label>

                    <select
                        id="departamentos-estado"
                        wire:model.live="estado"
                        class="w-full rounded-md border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-3 py-2.5 text-sm text-[var(--theme-text)] outline-none focus:border-[var(--theme-primary)]"
                    >
                        <option value="todos">Todos los estados</option>
                        <option value="activos">Activos</option>
                        <option value="inactivos">Inactivos</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-[var(--theme-text-muted)]">
                    {{ $departamentos->total() }}
                    {{ $departamentos->total() === 1 ? 'departamento encontrado' : 'departamentos encontrados' }}
                </p>

                @if ($search !== '' || $estado !== 'todos' || $hotelId !== '')
                    <button
                        type="button"
                        wire:click="limpiarFiltros"
                        class="text-xs font-medium text-[var(--theme-primary)] hover:underline"
                    >
                        Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        {{-- TABLA --}}
        <div
            class="overflow-x-auto"
            wire:loading.class="opacity-50"
            wire:target="search,estado,hotelId,limpiarFiltros,gotoPage,nextPage,previousPage"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-[var(--theme-border)] bg-[var(--theme-surface-soft)] text-xs text-[var(--theme-text-muted)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-5">
                            Departamento
                        </th>

                        <th scope="col" class="px-4 py-3 font-medium">
                            {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['cobertura'] }}
                        </th>

                        <th scope="col" class="px-4 py-3 font-medium">
                            Responsable
                        </th>

                        <th scope="col" class="px-4 py-3 font-medium">
                            Estado
                        </th>

                        <th scope="col" class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($departamentos as $departamento)
                        <tr
                            wire:key="departamento-{{ $departamento->id_departamento }}"
                            class="border-b border-[var(--theme-border)] last:border-b-0"
                        >
                            <td class="px-4 py-4 align-top sm:px-5">
                                <p class="font-semibold text-[var(--theme-text-strong)]">
                                    {{ $departamento->nombre }}
                                </p>

                                @if ($departamento->codigo)
                                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                        {{ $departamento->codigo }}
                                    </p>
                                @endif

                                @if ($departamento->descripcion)
                                    <p class="mt-1 max-w-sm text-xs text-[var(--theme-text-muted)]">
                                        {{ $departamento->descripcion }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-4 py-4 align-top">
                                @if ($departamento->atiende_todas_unidades)
                                    <span class="inline-flex rounded-md bg-[var(--theme-primary-soft)] px-2 py-1 text-xs font-medium text-[var(--theme-primary)]">
                                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['todas_unidades'] }}
                                    </span>

                                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['incluye_futuras'] }}
                                    </p>
                                @elseif ($departamento->hoteles->isNotEmpty())
                                    <div class="flex max-w-md flex-wrap gap-1.5">
                                        @foreach ($departamento->hoteles as $hotel)
                                            <span class="inline-flex rounded-md border border-[var(--theme-border)] px-2 py-1 text-xs text-[var(--theme-text)]">
                                                {{ $hotel->nombre }}

                                                @unless ($hotel->activo)
                                                    (inactivo)
                                                @endunless
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--theme-text-muted)]">
                                        {{ app(\App\Services\PlantillaInstalacionService::class)->etiquetas()['sin_unidades'] }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 align-top">
                                <p class="text-[var(--theme-text)]">
                                    {{ $departamento->responsable ?: 'Sin responsable' }}
                                </p>

                                @if ($departamento->correo)
                                    <p class="mt-1 break-all text-xs text-[var(--theme-text-muted)]">
                                        {{ $departamento->correo }}
                                    </p>
                                @endif

                                @if ($departamento->extension_telefono)
                                    <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                        Ext. {{ $departamento->extension_telefono }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-4 py-4 align-top">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-500/10 text-emerald-500' => $departamento->activo,
                                    'bg-[var(--theme-surface-soft)] text-[var(--theme-text-muted)]' => !$departamento->activo,
                                ])>
                                    {{ $departamento->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-right align-top">
                                <a
                                    href="{{ route('catalogos.departamentos.edit', [
                                        'departamento' => $departamento->id_departamento,
                                    ]) }}"
                                    class="inline-flex rounded-md border border-[var(--theme-border-strong)] px-3 py-2 text-xs font-medium text-[var(--theme-primary)] hover:bg-[var(--theme-primary-soft)]"
                                >
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-[var(--theme-text-strong)]">
                                    No se encontraron departamentos.
                                </p>

                                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                                    Prueba con otro nombre o cambia los filtros.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departamentos->hasPages())
            <div class="border-t border-[var(--theme-border)] px-4 py-4 sm:px-5">
                {{ $departamentos->links() }}
            </div>
        @endif
    </section>
</div>