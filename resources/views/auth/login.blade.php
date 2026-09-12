@extends('layouts.guest')

@section('title', 'Iniciar sesión')
@section('page-label', 'LOGIN')

@section('content')
<x-auth-card heading="Iniciar sesión" icon="user">

    {{-- Mensaje de sesión caducada / error general --}}
    @if (session('status'))
        <div class="mb-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-3 py-2">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Usuario o correo --}}
        <div>
            <input
                type="text"
                name="login"
                value="{{ old('login') }}"
                placeholder="Nombre de usuario o correo"
                autofocus
                autocomplete="username"
                class="w-full border border-[#50514F]/25 rounded-md px-4 py-2.5 text-sm text-[#50514F] placeholder-[#50514F]/40 focus:outline-none focus:ring-2 focus:ring-[#247BA0]/40 focus:border-[#247BA0]"
            >
            @error('login')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Contraseña --}}
        <div>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    class="w-full border border-[#50514F]/25 rounded-md px-4 py-2.5 pr-10 text-sm text-[#50514F] placeholder-[#50514F]/40 focus:outline-none focus:ring-2 focus:ring-[#247BA0]/40 focus:border-[#247BA0]"
                >
                <button type="button"
                        onclick="togglePassword('password', this)"
                        class="absolute inset-y-0 right-3 flex items-center text-[#50514F]/40 hover:text-[#50514F]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                        <line x1="3" y1="21" x2="21" y2="3"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="text-right -mt-1">
            <a href="{{ route('password.request') }}" class="text-xs text-[#247BA0] hover:underline">
                Olvidé la contraseña
            </a>
        </div>

        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 bg-[#247BA0] hover:bg-[#1d6688] text-white font-medium tracking-wide text-sm rounded-md py-3 transition-colors"
        >
            INGRESAR
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
        </button>

        <div class="text-center pt-1">
            <a href="{{ route('register') }}" class="text-xs text-[#247BA0] hover:underline">
                No tengo una cuenta
            </a>
        </div>
    </form>

</x-auth-card>

<script>
    function togglePassword(fieldId, btn) {
        const field = document.getElementById(fieldId);
        field.type = field.type === 'password' ? 'text' : 'password';
    }
</script>
@endsection