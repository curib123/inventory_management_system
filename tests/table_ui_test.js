const assert = require('assert');
const fs = require('fs');

const component = fs.readFileSync('application/views/components/data_table.php', 'utf8');
const css = fs.readFileSync('assets/css/table.css', 'utf8');

assert.match(component, /<th\s+scope="col"/);
assert.match(component, /class="table-responsive app-table-scroll"/);

assert.match(css, /\.app-data-table thead\s*\{[\s\S]*?background:\s*linear-gradient/);
assert.match(css, /\.app-data-table thead th[\s\S]*?color:\s*#fff/);
assert.match(css, /\.app-data-table thead\s*\{[\s\S]*?background:/);
assert.match(css, /\.app-data-table\.table\s*>\s*thead\s*>\s*tr\s*>\s*th\s*\{[\s\S]*?background:\s*#101827/);
assert.match(css, /\.app-data-table\.table\s*>\s*thead\s*>\s*tr\s*>\s*th\s*\{[\s\S]*?color:\s*#d8e2f0/);
assert.match(css, /\.app-data-table\.table\s*>\s*thead\s*>\s*tr\s*>\s*th\s*\{[\s\S]*?border-right:/);
assert.match(css, /\.app-data-table\.table\s*>\s*thead\s*>\s*tr\s*>\s*th:last-child\s*\{[\s\S]*?border-right:\s*0/);
assert.match(css, /\.app-data-table tbody tr:nth-child\(even\)\s*>/);
assert.match(css, /\.app-data-table tbody tr:focus-within/);
assert.match(css, /\.app-table-scroll[\s\S]*?overflow-x:\s*auto/);
assert.match(css, /\.app-data-table[\s\S]*?min-width:/);

console.log('PASS modern table UI');
