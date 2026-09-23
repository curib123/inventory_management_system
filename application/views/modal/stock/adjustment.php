<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php if (!empty($form_error)): ?><p><?php echo html_escape($form_error); ?></p><?php endif; ?>

<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <p>
        <label for="product_id">Product</label><br>
        <?php $selected_product = set_value('product_id'); ?>
        <select id="product_id" name="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo (int) $product->id; ?>" <?php echo ((string) $selected_product === (string) $product->id) ? 'selected' : ''; ?>><?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?> (Current stock: <?php echo (int) $product->stock; ?>)</option>
            <?php endforeach; ?>
        </select>
    </p>
    <p><label for="actual_stock">Actual Stock</label><br><input type="number" id="actual_stock" name="actual_stock" min="0" step="1" required value="<?php echo html_escape(set_value('actual_stock')); ?>"></p>
    <p><label for="reason">Reason</label><br><input type="text" id="reason" name="reason" maxlength="255" required value="<?php echo html_escape(set_value('reason')); ?>"></p>
    <button type="submit">Save Adjustment</button>
    <button type="button" data-modal-close>Cancel</button>
<?php echo form_close(); ?>
