const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const test = require('node:test');
const assert = require('node:assert/strict');

function formulario(search = '', duplicado = null) {
    const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/livewire/equipo-create.blade.php'), 'utf8');
    const script = source.split('<script>')[1].split('</script>')[0].replace(/@js\([^\n]+\)/g, '"Edificio"');
    let factory;
    vm.runInNewContext(script, {Alpine: {data: (_, fn) => factory = fn}, URLSearchParams, window: {location: {search}}});
    const estado = factory({clasificacionPorTipo: {'1': 'global', '2': 'vehiculos'}, tiposEquipo: [{value: '1'}, {value: '2'}], duplicado});
    const watches = {};
    estado.$watch = (key, fn) => watches[key] = fn;
    estado.init();
    return {estado, watches};
}

test('preselección y duplicación conservan la clasificación correspondiente', () => {
    assert.equal(formulario('?tipo=2').estado.values.clasificacionBien, 'vehiculos');
    assert.equal(formulario('', {base: {idTipoEquipo: '1'}, childData: {}}).estado.values.clasificacionBien, 'global');
});
test('cambiar clasificación limpia el tipo incompatible y envía la clasificación', () => {
    const {estado, watches} = formulario('?tipo=2');
    estado.values.clasificacionBien = 'global';
    watches['values.clasificacionBien']('global');
    assert.equal(estado.values.idTipoEquipo, '');
    watches['values.idTipoEquipo']('', '2');
    assert.equal(estado.values.clasificacionBien, 'global');
    assert.equal(estado.captureSnapshot().clasificacionBien, 'global');
});
test('marca, serie y factura respetan la configuración sin alterar los tipos tecnológicos', () => {
    const {estado} = formulario('?tipo=1');
    assert.equal(estado.displayFields.find(f => f.key === 'numero_serie').required, true);
    estado.data.baseFieldsByType = {'1': {
        numero_serie: {obligatorio: false, visible: true},
        numero_factura: {obligatorio: false, visible: true},
        id_marca: {obligatorio: false, visible: false},
    }};
    assert.equal(estado.displayFields.find(f => f.key === 'numero_serie').required, false);
    assert.equal(estado.displayFields.find(f => f.key === 'numero_factura').required, false);
    assert.equal(estado.displayFields.some(f => f.key === 'id_marca'), false);
});
