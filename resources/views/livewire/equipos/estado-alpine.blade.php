{
        filtersOpen: false,
        exportando: false,

        /* Exportación: aviso «Generando…» → «Archivo listo» / error. */
        avisoExportacion: null,
        exportacionSinDatos: false,

        async exportar(formato) {
            if (this.exportando) return;
            const escritorio = [...this.$root.querySelectorAll('.row-checkbox:checked')]
                .filter(el => el.getClientRects().length).map(el => el.value);
            const movil = [...this.$root.querySelectorAll('[data-export-selected]')]
                .filter(el => el.getClientRects().length).map(el => el.dataset.exportSelected);
            const seleccion = [...new Set([...escritorio, ...movil])];
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
                this.avisoExportacion = this.avisar('error', 'No se pudo generar el documento. Intenta nuevamente.', 'Error al exportar');
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