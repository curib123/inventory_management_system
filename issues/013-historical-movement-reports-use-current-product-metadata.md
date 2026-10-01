# Issue 013 — Historical movement reports use current product metadata

**Severity:** Medium-High  
**Status:** Open  
**Area:** Reporting / audit history

## What is wrong

Stock transaction items store:

- transaction_id
- product_id
- quantity
- cost_price

Historical report queries then join the current `products` row to display:

- product_code
- product_name
- category_id/category name

Files involved:

- `application/models/Report_model.php`
- `database/inventory_management_db.sql`
- stock transaction creation in `Stock_service.php`

The transaction supplier is snapshotted in `stock_transactions.supplier_id`, but product identity/category is not snapshotted.

## Failure scenario

1. Product is `USB Cable`, category `Accessories`.
2. A Stock Out transaction occurs.
3. Later the product is renamed and moved to `Clearance`.
4. Open the old Stock Out/Movement report.
5. The historical transaction now displays the new product name/category.

The historical event has effectively changed after the fact.

The Category report filter is also applied to current `p.category_id`, so old transactions can move between category-filter results after a product edit.

## Fix guide

Decide which fields must be historically immutable and snapshot them at transaction time.

Recommended transaction-item snapshot fields:

- product_code_snapshot
- product_name_snapshot
- category_id_snapshot
- optionally unit_snapshot

Then:

1. populate snapshots inside the same stock transaction DB transaction;
2. use snapshot values for Stock In/Out/Movement reports;
3. use `category_id_snapshot` for historical category filters;
4. keep current `product_id` for navigation/reconciliation;
5. add a migration and update seed/demo data.

For old rows, backfill from current products but document that those old snapshots are reconstructed, not guaranteed historical truth.

## Tests to add

- create transaction;
- rename product/change category;
- historical report must still show original snapshot;
- current product screen must show new metadata.

## Definition of done

- Editing a product no longer rewrites the apparent history of past stock movements.
- Category filtering on historical reports uses transaction-time category.
