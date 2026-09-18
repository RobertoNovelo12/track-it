        {{-- ====================================================
            RESUMEN SEMANAL
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
                    stroke-width="1.6"
                >
                    <path d="M4 20V10"/>
                    <path d="M10 20V4"/>
                    <path d="M16 20v-7"/>
                    <path d="M22 20V7"/>
                </svg>

                Resumen de la semana
            </h2>


            @php
                $maxActividad = max(
                    1,
                    (int) $resumenSemanal->max(
                        fn ($dia) => max(
                            $dia['actividad'],
                            $dia['registros']
                        )
                    )
                );
            @endphp


            <div
                class="
                    mt-6

                    h-44

                    flex
                    items-end
                    justify-between
                    gap-2
                "
            >

                @foreach ($resumenSemanal as $dia)

                    @php
                        $actividadHeight =
                            max(
                                4,
                                ($dia['actividad'] / $maxActividad) * 100
                            );

                        $registroHeight =
                            max(
                                4,
                                ($dia['registros'] / $maxActividad) * 100
                            );
                    @endphp


                    <div
                        class="
                            flex-1
                            h-full

                            flex
                            flex-col
                            justify-end
                            items-center
                        "
                    >

                        <div
                            class="
                                flex
                                items-end
                                justify-center
                                gap-1

                                w-full
                                h-full
                            "
                        >

                            <div
                                class="
                                    w-2.5
                                    max-w-full

                                    rounded-t

                                    bg-[var(--theme-primary)]

                                    opacity-80
                                "
                                style="height: {{ $actividadHeight }}%"
                                title="{{ $dia['actividad'] }} eventos"
                            ></div>


                            <div
                                class="
                                    w-2.5
                                    max-w-full

                                    rounded-t

                                    bg-[var(--theme-success)]

                                    opacity-75
                                "
                                style="height: {{ $registroHeight }}%"
                                title="{{ $dia['registros'] }} registros"
                            ></div>

                        </div>


                        <span
                            class="
                                mt-2
                                text-[9px]
                                text-[var(--theme-text-muted)]
                            "
                        >
                            {{ $dia['label'] }}
                        </span>

                    </div>

                @endforeach

            </div>


            <div
                class="
                    mt-4

                    flex
                    flex-wrap
                    items-center
                    gap-4

                    text-[10px]
                    text-[var(--theme-text-muted)]
                "
            >

                <span class="flex items-center gap-1.5">

                    <span
                        class="
                            w-2
                            h-2

                            rounded-full

                            bg-[var(--theme-primary)]
                        "
                    ></span>

                    Actividad

                </span>


                <span class="flex items-center gap-1.5">

                    <span
                        class="
                            w-2
                            h-2

                            rounded-full

                            bg-[var(--theme-success)]
                        "
                    ></span>

                    Nuevos usuarios

                </span>

            </div>

        </section>
