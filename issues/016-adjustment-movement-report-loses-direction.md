# Issue 016 — Movement report loses the direction of stock adjustments

**Severity:** Medium-High  
**Status:** Open  
**Area:** Reports / stock adjustments

## What is wrong

When an adjustment is created, the transaction item stores:

`quantity = abs($difference)`

The movement report reads `stock_transaction_items.quantity` and shows it as a positive quantity for all movement types.

For an adjustment, that means:

- +5 adjustment → quantity 5
- -5 adjustment → quantity 5

The report cannot tell whether stock increased or decreased.

Files involved:

- `application/libraries/Stock_service.php`
- `application/models/Report_model.php`
- `application/libraries/Report_service.php`

## Impact

A movement report containing adjustments can be materially misleading.

The export summary also sums all quantities positively, so adjustment decreases can increase “Total Quantity” and “Movement Value” instead of reducing/netting them.

## Root cause

Direction is stored only in `stock_adjustments.difference`, while the movement report reads only the absolute transaction-item quantity. There is also no direct transaction_id FK between the two adjustment records.

## Fix guide

Fix Issue 009 first so adjustment rows are linked to their transaction.

Then:

1. join the adjustment detail for `t.type = 'adjustment'`;
2. expose a signed movement quantity:
   - Stock In → +quantity
   - Stock Out → -quantity
   - Adjustment → difference
3. Decide whether report summaries mean:
   - gross movement volume, or
   - net stock change.
4. Label the summary accordingly.
5. For a net summary, use signed quantities.
6. Keep the original absolute transaction quantity only if it is still useful as a separate field.

## Tests to add

Movement report containing:

- Stock In 10;
- Stock Out 3;
- Adjustment -2;
- Adjustment +5.

Assert the displayed direction and whichever documented summary semantics you choose.

## Definition of done

- Adjustment decreases and increases are distinguishable in table and export.
- Summary labels accurately describe gross vs net values.
