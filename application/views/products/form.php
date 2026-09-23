<h2><?php echo html_escape($page_title); ?></h2>

<?php echo validation_errors(); ?>
<?php echo form_open(current_url()); ?>
    <p>
        <label for="product_name">Product Name</label><br>
        <input type="text" id="product_name" name="product_name" required maxlength="150" value="<?php echo html_escape(set_value('product_name', isset($product) ? $product->product_name : '')); ?>">
    </p>
    <p>
        <label for="product_code">Product Code</label><br>
        <input type="text" id="product_code" name="product_code" required maxlength="50" value="<?php echo html_escape(set_value('product_code', isset($product) ? $product->product_code : '')); ?>">
    </p>
    <p>
        <label for="category_id">Category</label><br>
        <?php $selected_category = set_value('category_id', isset($product) ? $product->category_id : ''); ?>
        <select id="category_id" name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int) $category->id; ?>" <?php echo ((string) $selected_category === (string) $category->id) ? 'selected' : ''; ?>><?php echo html_escape($category->category_name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="supplier_id">Supplier</label><br>
        <?php $selected_supplier = set_value('supplier_id', isset($product) ? $product->supplier_id : ''); ?>
        <select id="supplier_id" name="supplier_id">
            <option value="">No Supplier</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo (int) $supplier->id; ?>" <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?>><?php echo html_escape($supplier->supplier_name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="unit">Unit</label><br>
        <input type="text" id="unit" name="unit" maxlength="50" value="<?php echo html_escape(set_value('unit', isset($product) ? $product->unit : '')); ?>">
    </p>
    <p>
        <label for="cost_price">Cost Price</label><br>
        <input type="number" id="cost_price" name="cost_price" min="0" step="0.01" required value="<?php echo html_escape(set_value('cost_price', isset($product) ? $product->cost_price : '0.00')); ?>">
    </p>
    <p>
        <label for="selling_price">Selling Price</label><br>
        <input type="number" id="selling_price" name="selling_price" min="0" step="0.01" required value="<?php echo html_escape(set_value('selling_price', isset($product) ? $product->selling_price : '0.00')); ?>">
    </p>
    <p>
        <label for="reorder_level">Reorder Level</label><br>
        <input type="number" id="reorder_level" name="reorder_level" min="0" step="1" required value="<?php echo html_escape(set_value('reorder_level', isset($product) ? $product->reorder_level : '0')); ?>">
    </p>
    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($product) ? $product->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>
    <button type="submit">Save Product</button>
    <a href="<?php echo site_url('products'); ?>">Cancel</a>
<?php echo form_close(); ?>
