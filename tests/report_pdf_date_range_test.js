const assert = require('assert');
const fs = require('fs');

const reportService = fs.readFileSync('application/libraries/Report_service.php', 'utf8');
const pdfView = fs.readFileSync('application/views/reports/export_pdf.php', 'utf8');

assert.match(reportService, /'date_range'\s*=>/);
assert.match(reportService, /date_from/);
assert.match(reportService, /date_to/);
assert.match(pdfView, /report_meta\['date_range'\]/);
assert.match(pdfView, /Date range/);

console.log('PASS PDF report date range metadata');
