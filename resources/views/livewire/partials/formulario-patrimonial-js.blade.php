const formularioPatrimonial = {
    porcentajeCapturado: null,
    importesModificados: false,
    prepararCamposPatrimoniales(fields) {
        const principales = ['nombre_equipo', 'id_estado_activo', 'id_marca', 'id_modelo', 'fecha_compra', 'id_proveedor'];
        const claves = new Set(fields.map(field => field.claveInterna?.replace(/^institucion_/, '')));
        const calculable = ['costo_unitario', 'iva', 'total'].every(clave => claves.has(clave));
        return fields.filter(field => field.claveInterna !== 'institucion_caracteristicas' && field.claveInterna !== 'caracteristicas_equipo').map(field => {
            const prioridad = principales.indexOf(field.key);
            if (prioridad >= 0) {
                field.sectionId = null;
                field.sectionKey = 'general';
                field.order = -100 + prioridad;
            }
            if (field.key === 'fecha_compra') field.label = 'Fecha de adquisición';
            if (calculable && field.claveInterna?.replace(/^institucion_/, '') === 'iva') {
                field.label = 'IVA (%)';
                field.placeholder = 'Ej. 16';
            }
            return field;
        });
    },
    camposImportes() {
        const campos = {};
        this.displayFields.filter(field => field.child).forEach(field => {
            const clave = field.claveInterna?.replace(/^institucion_/, '');
            if (['costo_unitario', 'iva', 'total'].includes(clave)) campos[clave] = field.model;
        });
        return campos;
    },
    porcentajeActual() {
        if (this.porcentajeCapturado !== null) return this.porcentajeCapturado;
        const campos = this.camposImportes();
        const valores = this.values ?? this.childData;
        const costo = valores[campos.costo_unitario];
        const iva = valores[campos.iva];
        if (costo === '' || costo == null || iva === '' || iva == null) return '';
        return Number(costo) > 0 ? String(Number((Number(iva) * 100 / Number(costo)).toFixed(4))) : (Number(iva) === 0 ? '0' : '');
    },
    valoresEntrada(field) {
        const valores = this.values ?? (field.child ? this.childData : this);
        const campos = this.camposImportes();
        if (!campos.costo_unitario || !campos.iva || !campos.total || !field.child) return valores;
        const componente = this;
        return new Proxy(valores, {
            get(obj, key) { return key === campos.iva ? componente.porcentajeActual() : obj[key]; },
            set(obj, key, value) {
                if (key !== campos.costo_unitario && key !== campos.iva) { obj[key] = value; return true; }
                const porcentaje = key === campos.iva ? value : componente.porcentajeActual();
                componente.porcentajeCapturado = porcentaje;
                componente.importesModificados = true;
                if (key === campos.costo_unitario) obj[key] = value;
                const costo = obj[campos.costo_unitario];
                if (costo === '' || costo == null || porcentaje === '' || !Number.isFinite(Number(costo)) || !Number.isFinite(Number(porcentaje))) {
                    obj[campos.iva] = ''; obj[campos.total] = ''; return true;
                }
                const redondear = n => Math.round((n + Number.EPSILON) * 100) / 100;
                const base = redondear(Number(costo));
                const importe = redondear(base * Number(porcentaje) / 100);
                obj[campos.iva] = importe.toFixed(2);
                obj[campos.total] = redondear(base + importe).toFixed(2);
                return true;
            },
        });
    },
    totalAutomatico(field) {
        const campos = this.camposImportes();
        return field.child && !!campos.costo_unitario && !!campos.iva && field.model === campos.total;
    },
    payloadImportes() {
        return this.importesModificados ? {ivaPorcentaje: this.porcentajeActual()} : {};
    },
};
