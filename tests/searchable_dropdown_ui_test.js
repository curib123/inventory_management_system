const assert = require('assert');
const fs = require('fs');

const javascript = fs.readFileSync('assets/js/app.js', 'utf8');
const css = fs.readFileSync('assets/css/app.css', 'utf8');

assert.match(javascript, /app-searchable-select-trigger/);
assert.match(javascript, /app-searchable-select-panel/);
assert.match(javascript, /searchableOptionLimit\s*=\s*10/);
assert.match(javascript, /searchRemoteOptions\(['"]['"]\)/);
assert.doesNotMatch(javascript, /wrapper\.appendChild\(search\);\s*wrapper\.appendChild\(select\);/);

assert.match(css, /\.app-searchable-select-trigger/);
assert.match(css, /\.app-searchable-select-panel/);
assert.match(css, /\.app-searchable-select-option/);
assert.match(css, /\.app-searchable-select-native/);

console.log('PASS searchable dropdown UI');
