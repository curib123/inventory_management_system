const assert = require('assert');
const fs = require('fs');

const template = fs.readFileSync('application/views/templates/header.php', 'utf8');
const appCss = fs.readFileSync('assets/css/app.css', 'utf8');
const sidebarCss = fs.readFileSync('assets/css/sidebar.css', 'utf8');

assert.match(template, /class="topbar-context\b/);
assert.match(template, /class="topbar-eyebrow"/);
assert.match(template, /class="topbar-title/);
assert.match(template, /class="topbar-actions\b/);
assert.match(template, /class="topbar-security-label"/);
assert.match(template, /class="app-user-profile-name"/);
assert.match(template, /class="app-user-profile-role"/);
assert.match(template, /class="topbar-user-status"/);
assert.match(template, /data-modal-url="<\?php echo site_url\('account\/change-password'\); \?>"/);
assert.match(template, /id="sidebar-toggle"/);

assert.match(sidebarCss, /\.topbar\s*\{[\s\S]*?height:\s*72px/);
assert.match(appCss, /\.topbar-security-label/);
assert.match(appCss, /\.app-user-profile-name/);
assert.match(appCss, /\.topbar-user-status/);

console.log('PASS modern topbar UI');
