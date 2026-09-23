<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php echo form_open(current_url()); ?>
    <p>
        <label for="product_id">Product</label><br>
        <select id="product_id" name="product_id" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo (int) $product->id; ?>"><?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?> (Current stock: <?php echo (int) $product->stock; ?>)</option>
            <?php endforeach; ?>
        </select>
    </p>
    <p><label for="actual_stock">Actual Stock</label><br><input type="number" id="actual_stock" name="actual_stock" min="0" step="1" required value="<?php echo html_escape(set_value('actual_stock')); ?>"></p>
    <p><label for="reason">Reason</label><br><input type="text" id="reason" name="reason" maxlength="255" required value="<?php echo html_escape(set_value('reason')); ?>"></p>
    <button type="submit">Save Adjustment</button>
    <a href="<?php echo site_url('stock/adjustments'); ?>">Cancel</a>
<?php echo form_close(); ?>
