<div>

    {{-- ============================================================
        FILTROS DE BÚSQUEDA
    ============================================================ --}}
    <form wire:submit.prevent="$refresh" class="bg-white border border-[#50514F]/10 rounded-lg p-6 mb-6">

        <div class="flex items-center gap-2 mb-5">
            <svg class="w-4 h-4 text-[#50514F]/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 6h16"/>
                <path d="M7 6v2a2 2 0 002 2h6a2 2 0 002-2V6"/>
                <path d="M4 18h16"/>
                <path d="M9 18v-2a2 2 0 012-2h2a2 2 0 012 2v2"/>
            </svg>
            <span class="text-sm font-semibold text-[#50514F]">Filtros de búsqueda</span>

            <svg wire:loading wire:target="tipoActivo,marca,modelo,numeroSerie,serviceTag,direccionIp,estado,area,resetFilters"
                 class="w-4 h-4 animate-spin text-[#247BA0] ml-1" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.25"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2"/>
            </svg>
        </div>

        <div class="grid grid-cols-4 gap-4 mb-4">

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Tipo de activo</label>
                <select wire:model="tipoActivo" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($tiposActivo as $tipo)
                        <option value="{{ $tipo['id_tipo_equipo'] }}">{{ $tipo['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Marca</label>
                <select wire:model="marca" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($marcas as $m)
                        <option value="{{ $m['id_marca'] }}">{{ $m['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Modelo</label>
                <select wire:model="modelo" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($modelos as $mod)
                        <option value="{{ $mod['id_modelo'] }}">{{ $mod['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Número de serie</label>
                <input type="text" wire:model="numeroSerie" placeholder="Buscar..."
                       class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 placeholder:text-[#50514F]/40 focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
            </div>

        </div>

        <div class="grid grid-cols-4 gap-4">

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Service Tag</label>
                <select wire:model="serviceTag" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($serviceTags as $tag)
                        <option value="{{ $tag }}">{{ $tag }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Dirección IP</label>
                <select wire:model="direccionIp" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($direccionesIp as $ip)
                        <option value="{{ $ip }}">{{ $ip }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Estado</label>
                <select wire:model="estado" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($estados as $est)
                        <option value="{{ $est['id_valor'] }}">{{ $est['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#50514F]/60 mb-1.5">Área / Departamento</label>
                <select wire:model="area" class="w-full text-sm border border-[#50514F]/15 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-[#247BA0] focus:border-[#247BA0]">
                    <option value="">Todos</option>
                    @foreach ($areas as $a)
                        <option value="{{ $a['id_area'] }}">{{ $a['nombre'] }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3 mt-5">
            <button type="button" wire:click="resetFilters"
                    class="border border-[#50514F]/20 rounded-md px-4 py-2 text-sm font-medium text-[#50514F]/70 hover:bg-[#50514F]/5 transition-colors">
                Limpiar filtros
            </button>

            <button type="submit"
                    class="flex items-center gap-2 bg-[#247BA0] rounded-md px-4 py-2 text-sm font-medium text-white hover:bg-[#1d6688] transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-4-4"/>
                </svg>
                Aplicar filtros
            </button>
        </div>

    </form>


    {{-- ============================================================
        RESULTADOS
    ============================================================ --}}
    <div class="bg-white border border-[#50514F]/10 rounded-lg overflow-hidden relative">

        {{-- Overlay sutil mientras Livewire recarga la tabla --}}
        <div wire:loading.delay class="absolute inset-0 bg-white/50 z-10"></div>

        <div class="flex items-center justify-between px-5 py-4 border-b border-[#50514F]/10">

            <p class="text-sm text-[#50514F]/80">
                Resultados: <span class="font-medium">{{ number_format($equipos->total()) }}</span> Equipos encontrados
            </p>

            <div class="flex items-center gap-4">

                <div class="flex items-center gap-2">
                    <span class="text-xs text-[#50514F]/60">Mostrar</span>
                    <select wire:model.live="perPage" class="text-xs border border-[#50514F]/15 rounded-md px-2 py-1.5 bg-white focus:ring-1 focus:ring-[#247BA0]">
                        @foreach ([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs text-[#50514F]/60">Por página</span>
                </div>

                <select wire:model.live="sort" class="text-xs border border-[#50514F]/15 rounded-md px-2 py-1.5 bg-white uppercase focus:ring-1 focus:ring-[#247BA0]">
                    <option value="asc">ASC</option>
                    <option value="desc">DESC</option>
                </select>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>
                    <tr class="border-b border-[#50514F]/10 text-left">
                        <th class="px-5 py-3 w-10">
                            <input type="checkbox" onclick="toggleAllRows(this)" class="rounded border-[#50514F]/30">
                        </th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">ID Equipo</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Tipo de Equipo</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Marca</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Modelo</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Número de serie</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Service Tag</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Dirección IP</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Estado</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70">Área / Departamento</th>
                        <th class="px-3 py-3 font-medium text-[#50514F]/70 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($equipos as $equipo)
                        <tr wire:key="equipo-{{ $equipo->id_equipo }}" class="border-b border-[#50514F]/5 hover:bg-[#247BA0]/5 transition-colors">

                            <td class="px-5 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $equipo->id_equipo }}" class="row-checkbox rounded border-[#50514F]/30">
                            </td>
                            <td class="px-3 py-3 font-medium text-[#50514F]">{{ $equipo->codigo_inventario }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->tipo_equipo_nombre ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->marca_nombre ?? '—' }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->modelo_nombre ?? 'N/A' }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->numero_serie ?? 'NA' }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->service_tag ?? 'NA' }}</td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->direccion_ip ?? 'NA' }}</td>
                            <td class="px-3 py-3">
                                <x-status-badge :status="$equipo->estado_nombre ?? 'Sin estado'" />
                            </td>
                            <td class="px-3 py-3 text-[#50514F]/80">{{ $equipo->ubicacion_organizacional }}</td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-2 relative">
                                    <a href="{{ Route::has('equipos.show') ? route('equipos.show', $equipo->id_equipo) : '#' }}"
                                       class="text-[#247BA0] hover:text-[#1d6688]" title="Ver detalle">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <button type="button" onclick="toggleRowMenu(event, 'menu-{{ $equipo->id_equipo }}')"
                                            class="text-[#50514F]/60 hover:text-[#50514F]" title="Más acciones">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="5" r="1.5"/>
                                            <circle cx="12" cy="12" r="1.5"/>
                                            <circle cx="12" cy="19" r="1.5"/>
                                        </svg>
                                    </button>

                                    <div id="menu-{{ $equipo->id_equipo }}" class="row-menu hidden absolute right-0 top-6 w-40 bg-white border border-[#50514F]/10 rounded-md shadow-md z-20 text-left">
                                        <a href="{{ Route::has('equipos.edit') ? route('equipos.edit', $equipo->id_equipo) : '#' }}" class="block px-4 py-2 text-xs text-[#50514F]/80 hover:bg-[#50514F]/5">
                                            Editar
                                        </a>
                                        <a href="{{ Route::has('asignaciones.create') ? route('asignaciones.create', ['equipo' => $equipo->id_equipo]) : '#' }}" class="block px-4 py-2 text-xs text-[#50514F]/80 hover:bg-[#50514F]/5">
                                            Asignar
                                        </a>
                                        <button type="button" onclick="confirmarBaja('{{ $equipo->id_equipo }}')"
                                                class="w-full text-left block px-4 py-2 text-xs text-red-600 hover:bg-red-50">
                                            Dar de baja
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center text-sm text-[#50514F]/50">
                                No se encontraron equipos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>

        {{-- Paginación --}}
        @if ($equipos->hasPages())
            <div class="flex items-center justify-end gap-1 px-5 py-4 border-t border-[#50514F]/10">

                <button type="button" wire:click="previousPage" @disabled($equipos->onFirstPage())
                        class="w-8 h-8 flex items-center justify-center rounded-md text-[#50514F]/50 {{ $equipos->onFirstPage() ? 'opacity-40' : 'hover:bg-[#50514F]/5' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M15 6l-6 6 6 6"/>
                    </svg>
                </button>

                @php
                    $current = $equipos->currentPage();
                    $last = $equipos->lastPage();
                    $window = 2;
                @endphp

                @for ($page = 1; $page <= min(2, $last); $page++)
                    <button type="button" wire:click="gotoPage({{ $page }})"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-medium {{ $page === $current ? 'bg-[#247BA0] text-white' : 'text-[#50514F]/70 hover:bg-[#50514F]/5' }}">
                        {{ $page }}
                    </button>
                @endfor

                @if ($current > 4)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-[#50514F]/40">...</span>
                @endif

                @for ($page = max(3, $current - $window); $page <= min($last - 2, $current + $window); $page++)
                    <button type="button" wire:click="gotoPage({{ $page }})"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-medium {{ $page === $current ? 'bg-[#247BA0] text-white' : 'text-[#50514F]/70 hover:bg-[#50514F]/5' }}">
                        {{ $page }}
                    </button>
                @endfor

                @if ($current < $last - 3)
                    <span class="w-8 h-8 flex items-center justify-center text-xs text-[#50514F]/40">...</span>
                @endif

                @for ($page = max($last - 1, 3); $page <= $last; $page++)
                    <button type="button" wire:click="gotoPage({{ $page }})"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-medium {{ $page === $current ? 'bg-[#247BA0] text-white' : 'text-[#50514F]/70 hover:bg-[#50514F]/5' }}">
                        {{ $page }}
                    </button>
                @endfor

                <button type="button" wire:click="nextPage" @disabled(! $equipos->hasMorePages())
                        class="w-8 h-8 flex items-center justify-center rounded-md text-[#50514F]/50 {{ $equipos->hasMorePages() ? 'hover:bg-[#50514F]/5' : 'opacity-40' }}">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 6l6 6-6 6"/>
                    </svg>
                </button>

            </div>
        @endif

    </div>

</div>