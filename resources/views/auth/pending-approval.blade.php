@extends('layouts.guest')

@section('title', 'Registro enviado')
@section('page-label', 'ESPERA A ACEPTACIÓN')

@section('content')
<div class="w-full max-w-md bg-[#FFFCFF] border border-[#50514F]/15 rounded-lg shadow-sm px-10 py-10">

    <div class="flex flex-col items-center text-center">

        {{-- Ilustración: reemplaza el archivo en public/images/pendiente.png por la tuya --}}
        <div class="w-20 h-20 flex items-center justify-center mb-5">
            <img src="{{ asset('images/pendiente.png') }}"
                 alt="Registro pendiente de aprobación"
                 class="w-full h-full object-contain">
        </div>

        <h1 class="text-base font-semibold tracking-wide text-[#50514F] uppercase mb-2">
            Registro enviado
        </h1>

        <p class="text-sm text-[#50514F]/70 leading-relaxed mb-5">
            Tu información ha sido enviada correctamente. Un administrador revisará tus datos y te notificará cuando tu cuenta sea activada.
        </p>

        <div class="w-full flex gap-3 items-start bg-[#247BA0]/10 rounded-md px-4 py-3 mb-6 text-left">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="#247BA0" stroke-width="1.5">
                <circle cx="12" cy="12" r="9"/>
                <line x1="12" y1="10.5" x2="12" y2="16"/>
                <circle cx="12" cy="7.5" r="0.9" fill="#247BA0" stroke="none"/>
            </svg>
            <p class="text-xs text-[#50514F]/80 leading-relaxed">
                Este proceso puede tardar hasta 24–48 horas hábiles.
            </p>
        </div>

        <div class="w-full border-t border-[#50514F]/15 pt-6 mb-6">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="#247BA0" stroke-width="1.4">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="M3 6.5l9 6 9-6"/>
                </svg>
                <p class="text-xs text-[#50514F]/70 leading-relaxed">
                    Te enviaremos un correo electrónico cuando tu cuenta esté lista.
                </p>
            </div>
        </div>

        <a href="{{ route('login') }}"
           class="w-full flex items-center justify-center bg-[#247BA0] hover:bg-[#1d6688] text-white font-medium tracking-wide text-sm rounded-md py-3 transition-colors">
            ENTENDIDO
        </a>

    </div>

</div>
@endsection