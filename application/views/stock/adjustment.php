<h2><?php echo $page_title; ?></h2>
<?php echo validation_errors(); ?>
<?php echo form_open(isset($form_action) ? $form_action : current_url()); ?>
    <div class="form-group">
        <label for="product_id">Product</label>
        <select id="product_id" name="product_id" required>
            <option value="">Select product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo $product->id; ?>">
                    <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                    (Current stock: <?php echo (int) $product->stock; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="actual_stock">Actual Stock</label>
        <input type="number" id="actual_stock" name="actual_stock" min="0" required>
    </div>

    <div class="form-group">
        <label for="reason">Reason</label>
        <input type="text" id="reason" name="reason" maxlength="255" required>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save Adjustment</button>
        <a class="btn" href="<?php echo site_url('stock/adjustments'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
