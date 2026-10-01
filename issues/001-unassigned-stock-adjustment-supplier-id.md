# Issue 001 — Unassigned stock adjustment writes a string into supplier_id

**Severity:** Critical  
**Status:** Open  
**Area:** Stock adjustment / data integrity

## What is wrong

`Stock_service::create_adjustment()` validates the special supplier scope `unassigned`, but later passes the original raw scope directly into `stock_transactions.supplier_id`:

- File: `application/libraries/Stock_service.php`
- Method: `create_adjustment()`
- Problematic field: `'supplier_id' => $supplier_scope`

The database column `stock_transactions.supplier_id` is an integer foreign key and allows `NULL`, not the literal string `unassigned`.

## Why this can break

For a product whose `supplier_id IS NULL`, the UI/service accepts `supplier_scope = 'unassigned'`. The transaction insert then receives that string for an integer FK column.

Depending on MySQL/MariaDB SQL mode, this can:

1. reject the insert,
2. coerce the value to `0` and then fail the foreign key,
3. roll back the entire adjustment.

This means the system can advertise support for adjusting Unassigned Products while the persistence step fails.

## How to reproduce

1. Create or use an active product with `supplier_id = NULL`.
2. Open Stock Adjustment.
3. Select **Unassigned Products**.
4. Select the unassigned product.
5. Enter an actual stock value different from current stock.
6. Enter a reason and submit.
7. Inspect the response/database transaction.

Expected: adjustment succeeds and `stock_transactions.supplier_id` is `NULL`.

Current code path: the insert receives `unassigned`.

## Root cause

The normal stock-in/out flow converts the supplier scope into a normalized database value, but `create_adjustment()` does not. Validation and persistence use two different representations.

## Fix guide

In `Stock_service::create_adjustment()`:

1. Normalize `$supplier_scope` immediately after validation.
2. Use a separate variable such as `$transaction_supplier_id`.
3. If the scope is `unassigned`, set that variable to `NULL`.
4. If the scope is a numeric supplier ID, cast it to `int`.
5. Pass only the normalized value to `Stock_model->insert_transaction()`.
6. Do not pass the display/input token `unassigned` to the model.

A good pattern already exists in `Stock_service::create_transaction()` with `$transaction_supplier_id`. Reuse the same idea instead of creating a second interpretation.

## Tests to add

Add a service/integration test covering both cases:

- assigned product adjustment → transaction stores the supplier ID;
- unassigned product adjustment → transaction stores `NULL`.

Also verify the stock update, `stock_adjustments` row, transaction item, and activity log all commit together.

## Definition of done

- Unassigned adjustment completes successfully.
- `stock_transactions.supplier_id IS NULL` for that adjustment.
- Assigned supplier adjustments still store the correct integer supplier ID.
- No FK error or numeric conversion warning occurs.
- Existing stock adjustment screens continue to work.
