@extends('layouts.app')

@section('title', 'Editar departamento')

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-[var(--theme-text-strong)]">
                    Editar departamento
                </h1>

                <p class="mt-1 text-xs text-[var(--theme-text-muted)]">
                    Administración · Departamentos · Editar
                </p>
            </div>

            <a
                href="{{ route('catalogos.index', ['section' => 'departamentos']) }}"
                class="rounded-lg border border-[var(--theme-border-strong)] px-4 py-2 text-sm text-[var(--theme-text)]"
            >
                Volver
            </a>
        </div>

        <livewire:catalogos.departamento-editor
            :departamento-id="$departamentoId"
        />
    </div>
@endsection