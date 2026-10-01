# Issue 007 — Inventory snapshot date filter filters product creation date, not inventory history

**Severity:** Medium-High  
**Status:** Open  
**Area:** Reports / business correctness

## What is wrong

For Inventory and Valuation reports, `Report_model::build_datatable_query()` applies the report date range to:

`p.created_at`

Low Stock uses the same product-created-at concept.

File:

- `application/models/Report_model.php`
- UI: `application/views/reports/index.php`

But these reports display **current stock/current valuation/current low-stock state**, not the historical stock state at the selected date.

## Example

A product created six months ago with stock = 2 today:

- Current Low Stock report should include it if its reorder level is 5.
- Selecting **Today** currently excludes it because the product was not created today.

That does not mean “low stock today”; it means “products created today that are currently low stock.”

## Root cause

One shared Date Range UI was applied to both event reports and snapshot reports.

Event reports have a natural timestamp:

- Stock In
- Stock Out
- Movement

Snapshot reports do not have historical stock state unless it is reconstructed from movements.

## Fix guide

Choose one correct business behavior.

### Recommended simple fix

For current snapshot reports:

- Inventory
- Valuation
- Low Stock

remove the Date Range filter and do not call `apply_report_date_filter('p.created_at', ...)`.

Keep Date Range only on:

- Stock In
- Stock Out
- Movement

### If historical “as-of” reporting is required

Do not use `products.created_at`.

Instead:

1. choose an as-of timestamp;
2. reconstruct stock from stock transaction/adjustment history up to that timestamp;
3. calculate valuation/low-stock based on reconstructed stock;
4. clearly label the report **As of [date/time]**.

Historical valuation may also require deciding whether to use current cost price or transaction-time cost.

## Tests to add

For the simple current-snapshot behavior:

- old product with current low stock still appears regardless of date filters because those filters are absent.

For historical behavior:

- create product before range;
- perform movements before/after cutoff;
- assert reconstructed stock at cutoff.

## Definition of done

- Users cannot mistake product creation date for inventory report date.
- Snapshot report results match the report label.
- Event report date filtering continues to work normally.
- Export filters match the on-screen behavior.
