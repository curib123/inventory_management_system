<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php if (!empty($item_error)): ?><p><?php echo html_escape($item_error); ?></p><?php endif; ?>

<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <?php if ($transaction_type === 'stock_in'): ?>
        <p>
            <label for="supplier_id">Supplier</label><br>
            <?php $selected_supplier = set_value('supplier_id'); ?>
            <select id="supplier_id" name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <?php if ((int) $supplier->status === 1): ?>
                        <option value="<?php echo (int) $supplier->id; ?>" <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?>><?php echo html_escape($supplier->supplier_name); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </p>
        <p>For stock in, each selected product must already be assigned to the selected supplier.</p>
    <?php endif; ?>

    <h3>Items</h3>
    <?php for ($row = 0; $row < 5; $row++): ?>
        <fieldset>
            <legend>Item <?php echo $row + 1; ?></legend>
            <p>
                <label for="product_id_<?php echo $row; ?>">Product</label><br>
                <select id="product_id_<?php echo $row; ?>" name="product_id[]">
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo (int) $product->id; ?>">
                            <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                            <?php if ($transaction_type === 'stock_in'): ?>
                                - Supplier: <?php echo html_escape($product->supplier_name ?: 'None'); ?>
                            <?php endif; ?>
                            - Stock: <?php echo (int) $product->stock; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="quantity_<?php echo $row; ?>">Quantity</label><br>
                <input type="number" id="quantity_<?php echo $row; ?>" name="quantity[]" min="1" step="1">
            </p>
        </fieldset>
    <?php endfor; ?>

    <p>
        <label for="remarks">Remarks</label><br>
        <input type="text" id="remarks" name="remarks" maxlength="255" value="<?php echo html_escape(set_value('remarks')); ?>">
    </p>

    <button type="submit">Save Transaction</button>
    <button type="button" data-modal-close>Cancel</button>
<?php echo form_close(); ?>
