const assert = require('assert');
const fs = require('fs');

const template = fs.readFileSync('application/views/templates/header.php', 'utf8');
const css = fs.readFileSync('assets/css/sidebar.css', 'utf8');

assert.match(template, /class="sidebar-brand-mark"/);
assert.match(template, /class="sidebar-account"/);
assert.match(template, /class="nav-link-label"/);
assert.match(template, />Workspace</);
assert.match(template, />Stock movements</);
assert.match(template, />Sign out</);

assert.match(css, /width:\s*292px/);
assert.match(css, /\.sidebar-section/);
assert.match(css, /\.sidebar-account/);
assert.match(css, /\.sidebar\s*>\s*\.nav[\s\S]*?flex-wrap:\s*nowrap/);
assert.match(css, /\.sidebar\s+\.nav-link[\s\S]*?width:\s*100%/);
assert.match(css, /transform:\s*translateX\(-100%\)/);

console.log('PASS modern sidebar UI');
