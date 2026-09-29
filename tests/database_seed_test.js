const assert = require('assert');
const fs = require('fs');
const path = require('path');

const seedPath = path.join(__dirname, '..', 'database', 'seed_demo_data.sql');
const seedSql = fs.readFileSync(seedPath, 'utf8');
const exportPath = path.join(__dirname, '..', 'database', 'inventory_management_db_seeded.sql');
assert.ok(fs.existsSync(exportPath), 'checked-in database export must exist');
const exportSql = fs.readFileSync(exportPath, 'utf8');

const tables = [
  'roles',
  'modules',
  'permissions',
  'role_permissions',
  'users',
  'categories',
  'suppliers',
  'products',
  'stock_transactions',
  'stock_transaction_items',
  'stock_adjustments',
  'activity_logs'
];

for (const table of tables) {
  assert.match(
    seedSql,
    new RegExp(`INSERT INTO\\s+${table}\\b`, 'i'),
    `seed must populate ${table}`
  );
}

assert.match(seedSql, /stock_in/i, 'seed must include stock-in movement');
assert.match(seedSql, /stock_out/i, 'seed must include stock-out movement');
assert.match(seedSql, /adjustment/i, 'seed must include adjustment movement');
assert.match(seedSql, /Unassigned Products/i, 'seed must cover unassigned supplier flow');
assert.match(seedSql, /must_change_password/i, 'seed must cover password-change state');
assert.match(seedSql, /2026-09-29/, 'seed must include a current-day movement for dashboard filters');
assert.match(seedSql, /2026-0[1-8]-/, 'seed must cover multiple historical months');
assert.match(seedSql, /NOT EXISTS/i, 'seed must be safe to re-run for relationship rows');
assert.match(exportSql, /CREATE TABLE `stock_transactions`/i, 'export must contain the stock transaction schema');
assert.match(exportSql, /SIN-2026-0929/i, 'export must contain the seeded current-day movement');

console.log('database seed coverage tests passed');
