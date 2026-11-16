const fs = require('node:fs');
const path = require('node:path');
module.exports = function crearEstado(window = {}) {
    const expression = fs.readFileSync(path.resolve(__dirname, '../../resources/views/livewire/equipos/estado-alpine.blade.php'), 'utf8')
        .replace(/@js\(route\('([^']+)'\)\)/g, (_, route) => JSON.stringify('/' + route));
    return new Function('window', `return (${expression})`)(window);
};
