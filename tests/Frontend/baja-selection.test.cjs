const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/equipos/index.blade.php'), 'utf8');
const body = source.split('contar() {')[1].split('                    },')[0];
const contar = new Function('document', body);
for (const mobile of [false, true]) {
    test(`selección exportable y bloqueo de baja en ${mobile ? 'móvil' : 'escritorio'}`, () => {
        const row = (id, baja) => ({ value: id, dataset: { exportSelected: id, dadoDeBaja: baja ? '1' : '0' }, getClientRects: () => [1] });
        let selected = [row('1', false), row('2', true)];
        const document = { querySelectorAll: selector => selector.includes(',') ? selected : (selector.includes('data-export-selected') === mobile ? selected : []) };
        const state = { ids: [], menu: true };
        contar.call(state, document);
        assert.deepEqual(state.ids, ['1', '2']);
        assert.equal(state.incluyeBajas, true);
        assert.equal(state.menu, false);
        selected = [row('1', false)];
        contar.call(state, document);
        assert.deepEqual(state.ids, ['1']);
        assert.equal(state.incluyeBajas, false);
        selected = [];
        contar.call(state, document);
        assert.deepEqual(state.ids, []);
    });
}
