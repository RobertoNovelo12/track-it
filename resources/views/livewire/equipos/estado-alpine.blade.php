{
        filtersOpen: false,
        exportando: false,

        /* Exportación: aviso «Generando…» → «Archivo listo» / error. */
        avisoExportacion: null,
        exportacionSinDatos: false,

        /*
         * Selección de la tabla (escritorio y tarjetas). Vive en el navegador
         * y se conserva al cambiar de página o de filtros; la barra de
         * selección aparece mientras haya al menos uno.
         */
        seleccion: [],
        seleccionBajas: [],
        menuSeleccion: null,

        estaSeleccionado(id) {
            return this.seleccion.includes(String(id));
        },

        alternarSeleccion(id, marcado, baja = false) {
            id = String(id);
            this.seleccion = this.seleccion.filter(x => x !== id);
            this.seleccionBajas = this.seleccionBajas.filter(x => x !== id);

            if (marcado) {
                this.seleccion.push(id);
                if (baja) this.seleccionBajas.push(id);
            }

            if (this.seleccion.length === 0) this.menuSeleccion = null;
        },

        paginaSeleccionada(ids) {
            return ids.length > 0 && ids.every(id => this.seleccion.includes(String(id)));
        },

        alternarPagina(ids, marcado, bajas = []) {
            const deBaja = bajas.map(String);
            ids.map(String).forEach(id => this.alternarSeleccion(id, marcado, deBaja.includes(id)));
        },

        limpiarSeleccion() {
            this.seleccion = [];
            this.seleccionBajas = [];
            this.menuSeleccion = null;
        },

        abrirMenuSeleccion(nombre) {
            this.menuSeleccion = this.menuSeleccion === nombre ? null : nombre;
        },

        /* Dar de baja: 1 = baja individual; 2 a 10 = conjunta o uno por uno. */
        darDeBajaSeleccion(modo = null) {
            const ids = [...this.seleccion];

            if (ids.length === 0) return;

            if (this.seleccionBajas.length > 0) {
                window.trackitToast?.('warning', 'La selección incluye equipos que ya están dados de baja. Desmárcalos para continuar.', 'No se puede dar de baja');
                return;
            }

            if (ids.length > 10) {
                window.trackitToast?.('warning', 'Puedes dar de baja hasta 10 equipos a la vez. Desmarca ' + (ids.length - 10) + '.', 'Demasiados equipos');
                return;
            }

            if (ids.length > 1 && modo === null) {
                this.abrirMenuSeleccion('baja');
                return;
            }

            this.menuSeleccion = null;

            const destino = new URL(@js(route('equipos.baja-multiple')), window.location.origin);
            destino.searchParams.set('equipos', ids.join(','));
            if (modo) destino.searchParams.set('modo', modo);

            window.Livewire?.navigate
                ? Livewire.navigate(destino.toString())
                : (window.location.href = destino.toString());
        },

        moverSeleccionAPapelera() {
            this.menuSeleccion = null;

            if (this.seleccion.length > 100) {
                window.trackitToast?.('warning', 'Puedes mover hasta 100 equipos a la vez. Desmarca ' + (this.seleccion.length - 100) + '.', 'Demasiados equipos');
                return;
            }

            this.$dispatch('papelera-mover-varios', { tipo: 'equipo', ids: this.seleccion.map(Number) });
        },

        exportarSeleccion(formato) {
            this.menuSeleccion = null;
            this.exportar(formato);
        },

        async exportar(formato) {
            if (this.exportando) return;
            // Lo seleccionado en cualquier página (vacío = con los filtros).
            const seleccion = [...this.seleccion];
            const nombre = formato === 'xlsx' ? 'Excel' : 'PDF';

            this.exportando = true;
            this.exportacionSinDatos = false;
            this.avisoExportacion = window.trackitToast?.(
                'loading',
                seleccion.length
                    ? 'Con ' + seleccion.length + (seleccion.length === 1 ? ' equipo seleccionado.' : ' equipos seleccionados.')
                    : 'Con los filtros actuales. Puede tardar unos segundos.',
                'Generando ' + nombre + '…'
            ) ?? null;

            try {
                await this.$wire.exportarInventario(formato, seleccion);

                if (! this.exportacionSinDatos) {
                    this.avisoExportacion = this.avisar('success', 'La descarga comenzó.', nombre + ' listo');
                }
            } catch (error) {
                this.avisoExportacion = this.avisar('error', window.trackitMensajeError(error, 'generar el documento'), 'Error al exportar');
            } finally {
                this.exportando = false;
            }
        },

        exportacionVacia() {
            this.exportacionSinDatos = true;
            this.avisoExportacion = this.avisar('warning', 'No hay equipos para exportar con estos filtros o selección.', 'Nada que exportar');
        },

        avisar(tipo, mensaje, titulo) {
            if (this.avisoExportacion && window.trackitToastActualizar) {
                return window.trackitToastActualizar(this.avisoExportacion, tipo, mensaje, titulo);
            }

            return window.trackitToast?.(tipo, mensaje, titulo) ?? null;
        },
    }