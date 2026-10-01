# Issue 002 — Product Movement misrepresents stock adjustments

**Severity:** High  
**Status:** Open  
**Area:** Product movement history

## What is wrong

`Product_service::movement_history()` includes transaction rows whose type can be `adjustment`, but it formats every non-`stock_in` type as **Stock Out**:

`type_label => $type === 'stock_in' ? 'Stock In' : 'Stock Out'`

It also sets:

- `system_stock => NULL`
- `actual_stock => NULL`
- `difference => NULL`

However, the movement modal has a dedicated adjustment UI that expects those fields.

Files involved:

- `application/libraries/Product_service.php`
- `application/models/Stock_model.php`
- `application/views/modal/products/movement.php`

## User-visible effect

An adjustment can:

- be labeled **Stock Out** instead of **Adjustment**;
- show the wrong signed quantity;
- render System stock / Actual stock as zero because the service passes `NULL`;
- lose the real positive/negative adjustment difference.

The summary counts `adjustment`, so the code clearly intends to support it, but the row payload is incomplete.

## Root cause

`get_product_transaction_movements()` reads only `stock_transactions` + `stock_transaction_items`. The detailed adjustment values live in `stock_adjustments`, but the previously intended adjustment-history merge is not present.

## Fix guide

Recommended approach:

1. Add/restore a `Stock_model` query that returns adjustment rows for one product:
   - adjustment ID
   - system_stock
   - actual_stock
   - difference
   - reason
   - created_at
   - username
2. In `Product_service::movement_history()`, build stock-in/out rows from transaction history.
3. Build adjustment rows from `stock_adjustments`.
4. Set adjustment fields explicitly:
   - `type = 'adjustment'`
   - `type_label = 'Adjustment'`
   - `signed_quantity = difference`
   - `system_stock = system_stock`
   - `actual_stock = actual_stock`
   - `difference = difference`
   - `remarks = reason`
5. Merge the two arrays and sort once by date.
6. Avoid double-counting adjustments. If adjustment transactions remain in `get_product_transaction_movements()`, either exclude `adjustment` there or de-duplicate by a reliable key.

## Tests to add

Create product movement tests for:

- stock in +10;
- stock out -3;
- upward adjustment from 7 to 12 → +5;
- downward adjustment from 12 to 4 → -8.

Assert labels, signed quantities, system stock, actual stock, difference, sort order, and summary counters.

## Definition of done

- Adjustment rows display **Adjustment**.
- System stock and Actual stock show their real values.
- Difference sign is correct.
- Stock In and Stock Out remain correct.
- No adjustment appears twice.
- Summary totals match the rendered timeline.
