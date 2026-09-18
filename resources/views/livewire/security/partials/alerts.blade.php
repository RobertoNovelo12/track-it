        {{-- ====================================================
            ALERTAS
        ==================================================== --}}
        <section
            class="
                bg-[var(--theme-surface)]

                border
                border-[var(--theme-border)]

                rounded-lg

                p-4
            "
        >

            <h2
                class="
                    flex
                    items-center
                    gap-2

                    text-sm
                    font-semibold
                    text-[var(--theme-text-strong)]
                "
            >

                <svg
                    class="
                        w-5
                        h-5
                        text-[var(--theme-primary)]
                    "
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>

                Alertas y seguridad
            </h2>


            <div class="mt-4 space-y-2">

                @foreach ($alertas as $alerta)

                    @php
                        $alertaClasses = match ($alerta['type']) {
                            'danger' =>
                                'bg-[var(--theme-danger-soft)] text-[var(--theme-danger)]',

                            'warning' =>
                                'bg-[var(--theme-warning-soft)] text-[var(--theme-warning)]',

                            'success' =>
                                'bg-[var(--theme-success-soft)] text-[var(--theme-success)]',

                            default =>
                                'bg-[var(--theme-primary-soft)] text-[var(--theme-primary)]',
                        };
                    @endphp


                    <div
                        class="
                            flex
                            items-center
                            gap-3

                            px-3
                            py-2.5

                            rounded-lg

                            border
                            border-[var(--theme-border)]
                        "
                    >

                        <div
                            class="
                                w-8
                                h-8
                                shrink-0

                                rounded-full

                                flex
                                items-center
                                justify-center

                                {{ $alertaClasses }}
                            "
                        >
                            <svg
                                class="w-4 h-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6"/>
                                <path d="M12 17h.01"/>
                            </svg>
                        </div>


                        <p
                            class="
                                min-w-0
                                flex-1

                                text-[11px]
                                leading-relaxed

                                text-[var(--theme-text)]
                            "
                        >
                            {{ $alerta['title'] }}
                        </p>

                    </div>

                @endforeach

            </div>

        </section>
