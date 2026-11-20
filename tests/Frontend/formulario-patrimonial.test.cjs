const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const assert = require('node:assert/strict');
const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/livewire/partials/formulario-patrimonial-js.blade.php'), 'utf8');
function estado(edit = false) {
    const state = new Function(`${source}; return formularioPatrimonial;`)();
    state.displayFields = ['costo_unitario', 'iva', 'total'].map((key, i) => ({child: true, model: `campo${i}`, claveInterna: `institucion_${key}`}));
    state[edit ? 'childData' : 'values'] = {campo0: '', campo1: '', campo2: ''};
    return state;
}
for (const edit of [false, true]) {
    test(`calcula porcentaje y total en ${edit ? 'edición' : 'alta'}`, () => {
        const s = estado(edit), input = s.valoresEntrada(s.displayFields[0]);
        input.campo0 = '1000'; input.campo1 = '16';
        const values = s.values ?? s.childData;
        assert.equal(values.campo1, '160.00'); assert.equal(values.campo2, '1160.00');
        assert.equal(input.campo1, '16');
        input.campo0 = '2500';
        assert.equal(values.campo1, '400.00'); assert.equal(values.campo2, '2900.00');
        input.campo1 = '0'; assert.equal(values.campo2, '2500.00');
        input.campo0 = '19.99'; input.campo1 = '16';
        assert.equal(values.campo1, '3.20'); assert.equal(values.campo2, '23.19');
        input.campo0 = ''; assert.equal(values.campo2, '');
    });
}
test('conserva los importes históricos mientras no se editan', () => {
    const s = estado(true);
    s.childData = {campo0: '1000', campo1: '160', campo2: '1160'};
    assert.equal(s.porcentajeActual(), '16'); assert.deepEqual(s.payloadImportes(), {});
    assert.equal(s.childData.campo1, '160');
});
test('reordena campos generales y conserva especificaciones tecnológicas', () => {
    const s = estado();
    const fields = s.prepararCamposPatrimoniales([
        {key: 'id_modelo', sectionId: 99}, {key: 'fecha_compra'}, {key: 'id_proveedor'},
        {claveInterna: 'institucion_caracteristicas'}, {claveInterna: 'procesador'}, {claveInterna: 'ram'},
        ...s.displayFields,
    ]);
    assert.equal(fields.length, 8);
    assert.ok(fields.slice(0, 3).every(f => f.sectionId === null && f.sectionKey === 'general'));
    assert.equal(fields.find(f => f.claveInterna === 'institucion_iva').label, 'IVA (%)');
    assert.ok(fields.some(f => f.claveInterna === 'procesador'));
});
test('configuración sin los tres importes conserva su semántica', () => {
    const s = estado(); s.displayFields.pop();
    assert.equal(s.valoresEntrada(s.displayFields[0]), s.values);
    assert.equal(s.prepararCamposPatrimoniales(s.displayFields)[1].label, undefined);
});
