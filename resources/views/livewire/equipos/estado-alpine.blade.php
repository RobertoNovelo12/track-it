{
        filtersOpen: false,
        exportando: false,
        async exportar(formato) {
            if (this.exportando) return;
            const escritorio = [...this.$root.querySelectorAll('.row-checkbox:checked')]
                .filter(el => el.getClientRects().length).map(el => el.value);
            const movil = [...this.$root.querySelectorAll('[data-export-selected]')]
                .filter(el => el.getClientRects().length).map(el => el.dataset.exportSelected);
            this.exportando = true;
            try {
                await this.$wire.exportarInventario(formato, [...new Set([...escritorio, ...movil])]);
            } catch (error) {
                alert('No se pudo generar el documento. Intenta nuevamente.');
            } finally {
                this.exportando = false;
            }
        }
    }
