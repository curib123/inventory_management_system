const assert = require('assert');
const fs = require('fs');

const javascript = fs.readFileSync('assets/js/app.js', 'utf8');
const css = fs.readFileSync('assets/css/modal.css', 'utf8');
const container = fs.readFileSync('application/views/modal/container.php', 'utf8');

assert.doesNotMatch(javascript, /bootstrap\.Modal|getOrCreateInstance|hidden\.bs\.modal/);
assert.doesNotMatch(javascript, /class="modal-(header|body|footer|title)/);
assert.match(javascript, /showActionModal/);
assert.match(javascript, /hideActionModal/);
assert.match(javascript, /app-modal-open/);

assert.doesNotMatch(container, /class="modal fade/);
assert.doesNotMatch(container, /class="modal-dialog/);
assert.doesNotMatch(container, /class="modal-(header|body|footer|content)/);
assert.match(container, /app-modal-dialog/);

assert.match(css, /#action-modal\.is-open/);
assert.match(css, /#action-modal \.app-modal-dialog/);
assert.match(css, /backdrop-filter:\s*blur/);
assert.doesNotMatch(css, /\.modal-(header|body|footer|dialog|content|backdrop)/);

console.log('PASS custom modal');
