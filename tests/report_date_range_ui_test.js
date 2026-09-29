const assert = require('assert');
const fs = require('fs');

const reportView = fs.readFileSync('application/views/reports/index.php', 'utf8');
const tableComponent = fs.readFileSync('application/views/components/data_table.php', 'utf8');
const javascript = fs.readFileSync('assets/js/app.js', 'utf8');
const reportModel = fs.readFileSync('application/models/Report_model.php', 'utf8');
const tableCss = fs.readFileSync('assets/css/table.css', 'utf8');

assert.match(reportView, /'name'\s*=>\s*'period'/);
assert.match(reportView, /'custom_range'\s*=>\s*TRUE/);
assert.match(reportView, /'custom'\s*=>\s*'Custom range'/);

assert.match(tableComponent, /data-table-custom-range/);
assert.match(tableComponent, /data-table-date-filter="date_from"/);
assert.match(tableComponent, /data-table-date-filter="date_to"/);
assert.match(tableComponent, /type="date"/);
assert.match(tableCss, /\.app-table-filter-with-date-range\s*\{[\s\S]*?order:\s*-1/);

assert.match(javascript, /data-table-date-filter/);
assert.match(javascript, /data-table-custom-range/);
assert.match(javascript, /date_from/);
assert.match(javascript, /date_to/);

assert.match(reportModel, /apply_report_date_filter/);
assert.match(reportModel, /date_from/);
assert.match(reportModel, /date_to/);
assert.match(reportModel, /created_at/);

console.log('PASS report custom date range');
