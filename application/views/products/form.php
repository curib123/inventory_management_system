<h2><?php echo isset($product) ? 'Edit Product' : 'Add Product'; ?></h2>

<?php echo form_open(isset($form_action) ? $form_action : current_url()); ?>
    <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="product_name" value="<?php echo isset($product) ? $product->product_name : ''; ?>" required>
    </div>

    <div class="form-group">
        <label>Product Code</label>
        <input type="text" name="product_code" value="<?php echo isset($product) ? $product->product_code : ''; ?>" required>
    </div>

    <div class="form-group">
        <label>Supplier</label>
        <select name="supplier_id">
            <option value="">Select Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo $supplier->id; ?>" <?php echo (isset($product) && $product->supplier_id == $supplier->id) ? 'selected' : ''; ?>>
                    <?php echo $supplier->supplier_name; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Category ID</label>
        <input type="number" name="category_id" value="<?php echo isset($product) ? $product->category_id : ''; ?>" required>
    </div>

    <div class="form-group">
        <label>Unit</label>
        <input type="text" name="unit" value="<?php echo isset($product) ? $product->unit : ''; ?>">
    </div>

    <div class="form-group">
        <label>Cost Price</label>
        <input type="number" step="0.01" name="cost_price" value="<?php echo isset($product) ? $product->cost_price : ''; ?>">
    </div>

    <div class="form-group">
        <label>Selling Price</label>
        <input type="number" step="0.01" name="selling_price" value="<?php echo isset($product) ? $product->selling_price : ''; ?>">
    </div>

    <div class="form-group">
        <label>Reorder Level</label>
        <input type="number" name="reorder_level" value="<?php echo isset($product) ? $product->reorder_level : 0; ?>">
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="1" <?php echo (isset($product) && $product->status == 1) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo (isset($product) && $product->status == 0) ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('products'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
