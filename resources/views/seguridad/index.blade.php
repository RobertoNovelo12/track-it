@extends('layouts.app')

@section('title', 'Administración y seguridad')

@section('content')

    <div>

        {{-- ============================================================
        ENCABEZADO
    ============================================================ --}}
        <div
            class="
            mb-6

            flex
            items-start
            justify-between
            gap-4
        ">

            <div>

                <h1
                    class="
                    text-xl
                    font-semibold
                    text-[var(--theme-text-strong)]
                ">
                    Administración y seguridad
                </h1>


                <p
                    class="
                    mt-1
                    text-xs
                    text-[var(--theme-text-muted)]
                ">
                    Inicio

                    <span class="mx-1">
                        &gt;
                    </span>

                    Seguridad y Roles
                </p>

            </div>

        </div>


        {{-- ============================================================
        CONTENIDO LIVEWIRE
    ============================================================ --}}
        <livewire:seguridad-index lazy />

    </div>

@endsection
