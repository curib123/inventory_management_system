<h2><?php echo $page_title; ?></h2>
<?php echo validation_errors(); ?>
<?php if ($this->session->flashdata('error')): ?><p><?php echo html_escape($this->session->flashdata('error')); ?></p><?php endif; ?>

<?php echo form_open(); ?>
    <?php if ($transaction_type === 'stock_in'): ?>
        <div class="form-group">
            <label for="supplier_id">Supplier</label>
            <select id="supplier_id" name="supplier_id">
                <option value="">Select supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo $supplier->id; ?>"><?php echo html_escape($supplier->supplier_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="product_id">Product</label>
        <select id="product_id" name="product_id[]" required>
            <option value="">Select product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo $product->id; ?>">
                    <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                    (Stock: <?php echo (int) $product->stock; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity[]" min="1" required>
    </div>

    <div class="form-group">
        <label for="remarks">Remarks</label>
        <input type="text" id="remarks" name="remarks" maxlength="255">
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save Transaction</button>
        <a class="btn" href="<?php echo site_url('stock/history'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
