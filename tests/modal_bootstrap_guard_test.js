const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const source = fs.readFileSync('assets/js/app.js', 'utf8');
const modal = { addEventListener() {} };
const document = {
    addEventListener(type, handler) {
        if (type === 'DOMContentLoaded') {
            handler();
        }
    },
    getElementById(id) {
        return id === 'action-modal' ? modal : null;
    },
    querySelectorAll() {
        return [];
    },
    querySelector() {
        return null;
    }
};

assert.doesNotThrow(() => {
    vm.runInNewContext(source, {
        document,
        window: { requestAnimationFrame() {} },
        console
    });
}, 'app.js should initialize even when the Bootstrap CDN is unavailable');

console.log('PASS modal bootstrap guard');
