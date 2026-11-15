{{-- ============================================================
    Marco de las páginas legales (aviso de privacidad y términos).
    Públicas: se pueden abrir con o sin sesión.
    Cada página define: title, legal-titulo, legal-actualizado,
    legal-otra-ruta, legal-otra-texto y legal-cuerpo.
============================================================ --}}
@extends('layouts.guest')

@section('content')

@php
    $organizacionLegal = rescue(
        fn () => app(\App\Services\OrganizacionService::class)->nombre(),
        \App\Services\OrganizacionService::NOMBRE_PREDETERMINADO,
        false
    );
@endphp

<style>
    .legal { width: 100%; max-width: 48rem; }
    .legal h2 { margin-top: 1.75rem; font-size: 0.95rem; font-weight: 600; color: var(--theme-text-strong); }
    .legal p, .legal li { margin-top: 0.6rem; font-size: 0.875rem; line-height: 1.7; color: var(--theme-text); }
    .legal ul { margin-top: 0.4rem; padding-left: 1.25rem; list-style: disc; }
    .legal li { margin-top: 0.3rem; }
    .legal strong { color: var(--theme-text-strong); font-weight: 600; }
    .legal a { color: var(--theme-primary); }
    .legal a:hover { text-decoration: underline; }
</style>

<article class="legal rounded-lg border border-[var(--theme-border-strong)] bg-[var(--theme-surface)] px-6 py-8 shadow-sm sm:px-10 sm:py-10">
    <header class="border-b border-[var(--theme-border)] pb-5">
        <p class="text-xs font-medium uppercase tracking-wide text-[var(--theme-primary)]">Track IT · {{ $organizacionLegal }}</p>
        <h1 class="mt-2 text-xl font-semibold text-[var(--theme-text-strong)]">@yield('legal-titulo')</h1>
        <p class="mt-1 text-xs text-[var(--theme-text-muted)]">Última actualización: @yield('legal-actualizado')</p>
    </header>

    @yield('legal-cuerpo')

    <footer class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-[var(--theme-border)] pt-5 text-xs">
        <a href="{{ route(trim($__env->yieldContent('legal-otra-ruta'))) }}">@yield('legal-otra-texto')</a>
        <a href="{{ auth()->check() ? url('/') : route('login') }}">{{ auth()->check() ? 'Ir al sistema' : 'Ir a iniciar sesión' }}</a>
    </footer>
</article>

@endsection
