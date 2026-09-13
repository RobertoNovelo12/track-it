@extends('layouts.app')

@section('title', 'Equipos Tecnológicos')

@section('content')

<div>

    {{-- Encabezado --}}
    <div class="flex items-start justify-between mb-6">

        <div>
            <h1 class="text-xl font-semibold text-[#50514F]">
                Equipos tecnológicos
            </h1>

            <p class="text-xs text-[#50514F]/60 mt-1">
                Equipos Tecnológicos
            </p>
        </div>

        <div class="flex items-center gap-3">

            {{-- Exportar --}}
            <button
                type="button"
                onclick="document.getElementById('exportarMenu').classList.toggle('hidden')"
                class="relative flex items-center gap-2 border border-[#50514F]/20 rounded-md px-4 py-2 text-sm font-medium text-[#50514F]/80 hover:bg-[#50514F]/5 transition-colors"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 3v12"/>
                    <path d="M7 10l5 5 5-5"/>
                    <path d="M4 21h16"/>
                </svg>
                Exportar

                <div id="exportarMenu" class="hidden absolute right-0 top-full mt-2 w-40 bg-white border border-[#50514F]/10 rounded-md shadow-md z-20 text-left">
                    <a href="{{ Route::has('equipos.export') ? route('equipos.export', ['format' => 'pdf']) : '#' }}"
                       class="block px-4 py-2 text-sm text-[#50514F]/80 hover:bg-[#50514F]/5">
                        Exportar a PDF
                    </a>
                    <a href="{{ Route::has('equipos.export') ? route('equipos.export', ['format' => 'xlsx']) : '#' }}"
                       class="block px-4 py-2 text-sm text-[#50514F]/80 hover:bg-[#50514F]/5">
                        Exportar a Excel
                    </a>
                </div>
            </button>

            {{-- Añadir --}}
            <a
                href="{{ Route::has('equipos.create') ? route('equipos.create') : '#' }}"
                class="flex items-center gap-2 bg-[#247BA0] rounded-md px-4 py-2 text-sm font-medium text-white hover:bg-[#1d6688] transition-colors"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
                Añadir
            </a>

        </div>

    </div>

    {{-- Filtros, tabla y paginación: todo esto ahora vive en el componente Livewire --}}
    <livewire:equipos-index defer />

</div>

@push('scripts')
<script>
    function toggleAllRows(source) {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked);
    }

    function toggleRowMenu(event, menuId) {
        event.stopPropagation();
        document.querySelectorAll('.row-menu').forEach(menu => {
            if (menu.id !== menuId) menu.classList.add('hidden');
        });
        document.getElementById(menuId).classList.toggle('hidden');
    }

    function confirmarBaja(equipoId) {
        if (confirm('¿Confirmas dar de baja este equipo?')) {
            // Envía la solicitud de baja al backend, por ejemplo mediante Livewire o fetch().
        }
    }

    document.addEventListener('click', function () {
        document.querySelectorAll('.row-menu').forEach(menu => menu.classList.add('hidden'));
        const exportMenu = document.getElementById('exportarMenu');
        if (exportMenu) exportMenu.classList.add('hidden');
    });
</script>
@endpush

@endsection