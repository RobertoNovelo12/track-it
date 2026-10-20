{{-- ============================================================
    FUNCIONES DE LA VISTA GENERAL (Alpine)
    ------------------------------------------------------------
    Viven en el layout porque la vista general se carga de forma
    diferida (lazy): las etiquetas script que llegan dentro del HTML de un
    componente cargado después no se ejecutan, y sin estas
    funciones las tarjetas y gráficas se quedan sin números.
============================================================ --}}
    <script>

        /*
        |--------------------------------------------------------------------------
        | CONTADOR
        |--------------------------------------------------------------------------
        */

        /*
         * Tarjetas: se muestra el valor real desde el primer momento
         * (sin contar desde cero, que podría leerse como un dato real).
         */
        function dashboardCounter(target) {
            return {
                display: (Number(target) || 0).toLocaleString('es-MX'),
                init() {},
            };
        }


        /*
        |--------------------------------------------------------------------------
        | BARRAS, COLUMNAS Y ANILLO
        |--------------------------------------------------------------------------
        */

        function dashboardBar() {

            return {

                ready: false,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.ready =
                            true;

                        return;
                    }


                    requestAnimationFrame(
                        () => {

                            requestAnimationFrame(
                                () => {

                                    this.ready =
                                        true;
                                }
                            );
                        }
                    );
                }

            };
        }


        /*
        |--------------------------------------------------------------------------
        | COLUMNAS
        |--------------------------------------------------------------------------
        */

        function dashboardColumn(
            target
        ) {

            return {

                height: 0,

                target:
                    Number(
                        target
                    ) || 0,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.height =
                            this.target;

                        return;
                    }


                    requestAnimationFrame(
                        () => {

                            requestAnimationFrame(
                                () => {

                                    this.height =
                                        this.target;
                                }
                            );
                        }
                    );
                }

            };
        }


        /*
        |--------------------------------------------------------------------------
        | ANILLO
        |--------------------------------------------------------------------------
        */

        function dashboardRing(
            targetProgress,
            duration = 900
        ) {

            return {

                progress: 0,

                targetProgress:
                    Math.min(
                        Math.max(
                            Number(
                                targetProgress
                            ) || 0,
                            0
                        ),
                        100
                    ),

                duration,


                init() {

                    if (
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {

                        this.progress =
                            this.targetProgress;

                        return;
                    }


                    const start =
                        performance.now();


                    const animate = (
                        now
                    ) => {

                        const ratio =
                            Math.min(
                                (
                                    now
                                    - start
                                )
                                / this.duration,
                                1
                            );


                        const eased =
                            1
                            -
                            Math.pow(
                                1 - ratio,
                                3
                            );


                        this.progress =
                            Math.round(
                                this.targetProgress
                                * eased
                            );


                        if (
                            ratio
                            < 1
                        ) {

                            requestAnimationFrame(
                                animate
                            );

                        } else {

                            this.progress =
                                this.targetProgress;
                        }
                    };


                    requestAnimationFrame(
                        animate
                    );
                }

            };
        }

    </script>
