@props(['heading', 'icon' => 'user'])

<div class="w-full max-w-md bg-[#FFFCFF] border border-[#50514F]/15 rounded-lg shadow-sm px-10 py-10">

    <div class="flex flex-col items-center mb-6">
        <span class="mb-3 text-[#50514F]">
            @switch($icon)
                @case('key')
                    <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                        <circle cx="8" cy="14" r="3.2"/>
                        <path d="M10.3 11.7L18 4m0 0h-3.5M18 4v3.5M15 7l2 2"/>
                    </svg>
                    @break

                @case('user-plus')
                    <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                        <circle cx="10" cy="8" r="3.3"/>
                        <path d="M4.5 20c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/>
                        <path d="M18.5 8.5v4.5M16.25 10.75h4.5"/>
                    </svg>
                    @break

                @default
                    <svg class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                        <circle cx="12" cy="8" r="3.3"/>
                        <path d="M5.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/>
                    </svg>
            @endswitch
        </span>

        <h1 class="text-base font-semibold tracking-wide text-[#50514F] uppercase">
            {{ $heading }}
        </h1>
    </div>

    {{ $slot }}

</div>