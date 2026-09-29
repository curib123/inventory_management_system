const assert = require('assert');
const fs = require('fs');

const dashboard = fs.readFileSync('application/views/dashboard/index.php', 'utf8');
const css = fs.readFileSync('assets/css/app.css', 'utf8');
const javascript = fs.readFileSync('assets/js/dashboard.js', 'utf8');

assert.match(dashboard, /app-dashboard-hero/);
assert.match(dashboard, /dashboard_greeting/);
assert.match(dashboard, /Good morning/);
assert.match(dashboard, /dashboard_user_name/);
assert.match(dashboard, /app-dashboard-hero-attention/);
assert.match(dashboard, /app-dashboard-stat-grid/);
assert.match(dashboard, /app-dashboard-stat-feature/);
assert.match(dashboard, /app-dashboard-chart-card-health/);
assert.match(dashboard, /app-dashboard-chart-card-category/);

assert.match(css, /\.app-dashboard-hero\s*\{[\s\S]*?background:/);
assert.match(css, /\.app-dashboard\s*\{[\s\S]*?width:\s*100%/);
assert.match(css, /\.app-dashboard-stat-grid\s*\{[\s\S]*?repeat\(7,\s*minmax/);
assert.match(css, /\.app-dashboard-stat-grid\s*\{[\s\S]*?display:\s*grid/);
assert.match(css, /\.app-dashboard-stat-feature\s*\{[\s\S]*?grid-column:/);
assert.match(css, /\.app-dashboard-hero-attention\s*\{[\s\S]*?border/);
assert.match(css, /\.app-dashboard-chart-card-health/);

assert.match(javascript, /titleColor:\s*['"]#fff['"]/);

console.log('PASS modern dashboard UI');
