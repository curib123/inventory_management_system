# Issue 009 — Stock adjustments are not linked to their stock transaction

**Severity:** High  
**Status:** Open  
**Area:** Stock adjustment / data model

## What is wrong

The stock adjustment flow creates two related records:

1. a `stock_transactions` row with `type = 'adjustment'`;
2. a `stock_adjustments` row with system stock, actual stock, difference, and reason.

However, the `stock_adjustments` table has no `transaction_id` column, and `Stock_service::create_adjustment()` does not store a relationship between those two records.

Files involved:

- `application/libraries/Stock_service.php`
- `application/models/Stock_model.php`
- `database/inventory_management_db.sql`
- `database/inventory_management_db_seeded.sql`

## Why this matters

The transaction row contains the official transaction number and supplier snapshot, while the adjustment row contains the actual reconciliation details.

Without a foreign-key link, the system cannot reliably answer:

- which adjustment row belongs to a specific adjustment transaction;
- which system/actual stock values belong in Stock Transaction Details;
- which adjustment difference belongs in the Movement report;
- how to reconstruct the exact adjustment event later.

Matching by product + user + timestamp is not reliable.

## Current downstream symptom

This missing relationship contributes to the Product Movement issue where adjustment rows do not have system stock, actual stock, or a reliable signed difference.

## Fix guide

1. Add `transaction_id INT NOT NULL` to `stock_adjustments`.
2. Add an index on `transaction_id`.
3. Add a foreign key to `stock_transactions(id)`.
4. Consider a unique key on `transaction_id` if one adjustment transaction is guaranteed to contain exactly one adjusted product.
5. Update `Stock_model::insert_adjustment()` callers to include the newly created transaction ID.
6. Update adjustment queries to join through the real transaction relationship.
7. Update seeded/demo data.
8. Create a migration for existing databases.

For existing historical data, do not guess links silently. Backfill only where a match is unambiguous; otherwise leave a documented migration exception or make the field nullable during transition.

## Tests to add

- Creating an adjustment stores the same transaction ID in both records.
- Deleting or invalidating the transaction cannot leave an orphan adjustment.
- Transaction details can retrieve system stock, actual stock, and difference using the FK.

## Definition of done

- Every new stock adjustment has a deterministic transaction relationship.
- Adjustment details no longer depend on timestamp matching.
- Foreign-key integrity is enforced.
- Demo/seed/schema files remain consistent.
