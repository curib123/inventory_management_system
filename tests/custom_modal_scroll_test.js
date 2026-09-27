const assert = require('assert');
const fs = require('fs');

const css = fs.readFileSync('assets/css/modal.css', 'utf8');

const bodyRule = css.match(/#action-modal \.app-modal-body\s*\{([\s\S]*?)\}/);
assert.ok(bodyRule, 'custom modal body rule should exist');
assert.match(bodyRule[1], /max-height:\s*calc\(100vh\s*-\s*12rem\)/);
assert.match(bodyRule[1], /overflow-y:\s*auto/);
assert.match(css, /#action-modal \.app-modal-dialog\s*\{[\s\S]*?min-height:\s*0/);
assert.match(css, /#action-modal \.app-modal-content\s*\{[\s\S]*?min-height:\s*0/);

console.log('PASS custom modal scroll');
