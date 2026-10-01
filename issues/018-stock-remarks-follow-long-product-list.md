# Issue 018 — Stock transaction remarks require scrolling past every product

**Severity:** Medium  
**Status:** Open; proposed fix only  
**Reviewed commit:** `9c7229442fa48a8edd66898d970ace71e9bea17a`

## Evidence and reproduction

`application/views/modal/stock/transaction_form.php` places `<!-- Remarks -->` after the entire `[data-stock-products]` section. Selecting a supplier with many products pushes the remarks input below the long quantity list.

This is shared by Stock In and Stock Out. The existing regression command reproduces the problem:

```bash
node tests/stock_transaction_notes_visibility_test.js
```

It fails with `remarks should appear before the long product list`. The inventory guidance already precedes the product list; only move the remarks block. None of guides 001–016 describes this layout issue.

## Fix with code

Remove the existing `<!-- Remarks -->` block from the bottom of the modal body. Insert the following immediately before `<!-- Products -->`, after the existing assist note:

```php
    <!-- Remarks -->
    <div class="mb-4">
        <label for="remarks" class="form-label">Remarks</label>
        <input
            type="text"
            id="remarks"
            name="remarks"
            class="form-control"
            maxlength="255"
            value="<?php echo html_escape(set_value('remarks')); ?>"
        >
        <div class="form-text">
            Add a short business reason or reference when it helps explain the movement later.
        </div>
    </div>
```

Move the block; do not leave a second remarks input behind. Keep `name="remarks"` and `set_value('remarks')` so submission and validation recovery still work. Do not move this input inside `[data-stock-products]`, because changing suppliers replaces that container's contents.

## Verify

```bash
php -l application/views/modal/stock/transaction_form.php
node tests/stock_transaction_notes_visibility_test.js
```

Open both transaction types, select a supplier with many products, and confirm remarks appear before product quantities. Enter remarks, change the supplier, and confirm the text remains. Submit invalid quantities and confirm the re-rendered form preserves remarks.

**Validation performed during analysis:** reproduced the existing failure; tested the relocation in a separate temporary copy, where the regression check passed. Application code is unchanged in this documentation change.
