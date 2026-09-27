const assert = require('assert');
const fs = require('fs');

const css = fs.readFileSync('assets/css/modal.css', 'utf8');
const backdropRule = css.match(/#action-modal\s*\{([\s\S]*?)\}/);

assert(backdropRule, 'the modal backdrop rule should exist');
assert.match(backdropRule[1], /backdrop-filter:\s*blur\(8px\)/);
assert.match(backdropRule[1], /-webkit-backdrop-filter:\s*blur\(8px\)/);

console.log('PASS modal backdrop design');
