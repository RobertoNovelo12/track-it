@props([
    'title',
    'description',
    'action',
    'route',
    'icon' => null,
])

<div
    class="
        bg-white
        border border-[#50514F]/10
        rounded-lg
        px-6 py-6
        min-h-44
        flex flex-col
        hover:shadow-sm
        transition-shadow
    "
>

    <div class="flex items-start justify-between">

        <h3 class="text-base font-semibold text-[#50514F]">
            {{ $title }}
        </h3>

        <div class="text-[#50514F]/40">
            @switch($icon)
                @case('laptop')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="13" rx="1"/>
                        <path d="M2 20h20"/>
                    </svg>
                    @break

                @case('swap')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 7h15"/>
                        <path d="M16 4l3 3-3 3"/>
                        <path d="M20 17H5"/>
                        <path d="M8 14l-3 3 3 3"/>
                    </svg>
                    @break

                @case('tools')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18v3h3l6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2z"/>
                    </svg>
                    @break

                @case('report')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 2h9l5 5v15H6z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>
                    @break

                @case('users')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20c0-4 2-7 6-7s6 3 6 7"/>
                        <circle cx="17" cy="9" r="2"/>
                    </svg>
                    @break

                @case('list')
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M8 6h13"/>
                        <path d="M8 12h13"/>
                        <path d="M8 18h13"/>
                        <path d="M3 6l1 1 2-2"/>
                        <path d="M3 12l1 1 2-2"/>
                        <path d="M3 18l1 1 2-2"/>
                    </svg>
                    @break
            @endswitch
        </div>

    </div>

    <p class="text-xs text-[#50514F]/70 leading-relaxed mt-4 flex-1">
        {{ $description }}
    </p>

    <div class="border-t border-[#50514F]/10 pt-4 mt-5">

        <a
            href="{{ Route::has($route) ? route($route) : '#' }}"
            class="
                flex justify-between items-center
                text-xs font-medium text-[#247BA0]
                hover:text-[#1d6688]
            "
        >
            <span>{{ $action }}</span>

            <svg
                class="w-4 h-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path d="M9 5l7 7-7 7"/>
            </svg>
        </a>

    </div>

</div>