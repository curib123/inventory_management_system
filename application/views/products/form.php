<h2><?php echo isset($product) ? 'Edit Product' : 'Add Product'; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open(isset($form_action) ? $form_action : current_url()); ?>
    <div class="form-group">
        <label for="product_name">Product Name</label>
        <input type="text" id="product_name" name="product_name" value="<?php echo html_escape(isset($product) ? $product->product_name : ''); ?>" required  autocomplete="off" maxlength="20">
    </div>

    <div class="form-group">
        <label for="product_code">Product Code</label>
        <input type="text" id="product_code" name="product_code" value="<?php echo html_escape(isset($product) ? $product->product_code : ''); ?>" required autocomplete="off" maxlength="20">
    </div>

    <div class="form-group">
        <label for="supplier_id">Supplier</label>
        <select id="supplier_id" name="supplier_id">
            <option value="">Select Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo $supplier->id; ?>" <?php echo (isset($product) && $product->supplier_id == $supplier->id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($supplier->supplier_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

   
     <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>" <?php echo (isset($product) && $product->category_id == $category->id) ? 'selected' : ''; ?>>
                    <?php echo html_escape($category->category_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="unit">Unit</label>
        <input type="text" id="unit" name="unit" maxlength="50" value="<?php echo html_escape(isset($product) ? $product->unit : ''); ?>" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="cost_price">Cost Price</label>
        <input type="number" id="cost_price" step="0.01" min="0" name="cost_price" value="<?php echo html_escape(isset($product) ? $product->cost_price : ''); ?>" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="selling_price">Selling Price</label>
        <input type="number" id="selling_price" step="0.01" min="0" name="selling_price" value="<?php echo html_escape(isset($product) ? $product->selling_price : ''); ?>" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="reorder_level">Reorder Level</label>
        <input type="number" id="reorder_level" name="reorder_level" min="0" step="1" value="<?php echo isset($product) ? (int) $product->reorder_level : 0; ?>" autocomplete="off">
    </div>

    <div class="form-group">
        <label for="product_status">Status</label>
        <select id="product_status" name="status">
            <option value="1" <?php echo (isset($product) && $product->status == 1) ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo (isset($product) && $product->status == 0) ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('products'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
