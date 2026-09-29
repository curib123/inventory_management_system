const assert = require('assert');
const fs = require('fs');

const confirmation = fs.readFileSync('application/views/components/modal/confirmation.php', 'utf8');
const alert = fs.readFileSync('application/views/modal/alerts/message.php', 'utf8');
const javascript = fs.readFileSync('assets/js/app.js', 'utf8');
const css = fs.readFileSync('assets/css/modal.css', 'utf8');

assert.match(confirmation, /app-confirmation-card-copy/);
assert.match(confirmation, /app-confirmation-card-kicker/);
assert.match(alert, /app-modal-message-card-icon/);
assert.match(alert, /app-modal-message-card-icon-/);

assert.match(javascript, /app-confirmation-review-icon/);
assert.match(javascript, /app-confirmation-impact/);
assert.match(javascript, /app-confirmation-assist/);

assert.match(css, /app-confirmation-card-copy/);
assert.match(css, /app-confirmation-card-kicker/);
assert.match(css, /app-modal-message-card-icon/);
assert.match(css, /app-modal-message-card-danger/);

console.log('PASS confirmation and alert modal UI');
