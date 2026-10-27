const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const views = path.resolve(__dirname, '../../resources/views/livewire');
const shared = 'livewire.equipos.estado-alpine';
const expression = fs.readFileSync(path.join(views, 'equipos/estado-alpine.blade.php'), 'utf8');

test('el estado conservado desde el placeholder permite exportar tras la carga diferida', async () => {
    for (const file of ['placeholders/equipos-index.blade.php', 'equipos-index.blade.php']) {
        const html = fs.readFileSync(path.join(views, file), 'utf8');
        assert.ok(html.includes(`x-data="@include('${shared}')"`));
    }
    const state = new Function('window', `return (${expression})`)({});
    assert.equal(state.filtersOpen, false);
    assert.equal(state.exportando, false);
    const calls = [];
    let complete;
    state.$root = { querySelectorAll: () => [] };
    state.$wire = { exportarInventario: (...args) => {
        calls.push(args);
        return new Promise(resolve => { complete = resolve; });
    }};
    const download = state.exportar('pdf');
    assert.equal(state.exportando, true);
    await state.exportar('pdf');
    assert.equal(calls.length, 1);
    assert.deepEqual(calls[0], ['pdf', []]);
    complete();
    await download;
    assert.equal(state.exportando, false);
});

test('la exportación recupera su estado cuando falla la descarga', async () => {
    const messages = [];
    const state = new Function('window', `return (${expression})`)({
        trackitToast: (type, text) => {
            if (type === 'error') messages.push(text);
        },
        trackitMensajeError: error => error.message,
    });
    state.$root = { querySelectorAll: () => [] };
    state.$wire = { exportarInventario: async () => { throw new Error('network'); } };
    await state.exportar('xlsx');
    assert.equal(state.exportando, false);
    assert.equal(messages.length, 1);
});
