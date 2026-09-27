const assert = require('assert');
const fs = require('fs');

const template = fs.readFileSync('application/views/modal/stock/transaction_form.php', 'utf8');

const productsIndex = template.indexOf('<!-- Products -->');
const remarksIndex = template.indexOf('<!-- Remarks -->');
const inventoryNoteIndex = template.indexOf("'assist_title' => 'Inventory transaction'");

assert.notStrictEqual(productsIndex, -1, 'products section should exist');
assert.notStrictEqual(remarksIndex, -1, 'remarks section should exist');
assert.notStrictEqual(inventoryNoteIndex, -1, 'inventory guidance should exist');
assert.ok(remarksIndex < productsIndex, 'remarks should appear before the long product list');
assert.ok(inventoryNoteIndex < productsIndex, 'inventory guidance should appear before the long product list');

console.log('PASS stock transaction notes visibility');
