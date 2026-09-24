<div class="modal-header">
    <h2 class="modal-title fs-5">Product Details</h2>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>

<div class="modal-body">
    <dl class="row mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $product->id; ?></dd>
        <dt class="col-sm-4">Code</dt><dd class="col-sm-8"><?php echo html_escape($product->product_code); ?></dd>
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($product->product_name); ?></dd>
        <dt class="col-sm-4">Category</dt><dd class="col-sm-8"><?php echo html_escape($product->category_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Supplier</dt><dd class="col-sm-8"><?php echo html_escape($product->supplier_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Unit</dt><dd class="col-sm-8"><?php echo html_escape($product->unit); ?></dd>
        <dt class="col-sm-4">Stock</dt><dd class="col-sm-8"><?php echo (int) $product->stock; ?></dd>
        <dt class="col-sm-4">Cost Price</dt><dd class="col-sm-8"><?php echo number_format((float) $product->cost_price, 2); ?></dd>
        <dt class="col-sm-4">Selling Price</dt><dd class="col-sm-8"><?php echo number_format((float) $product->selling_price, 2); ?></dd>
        <dt class="col-sm-4">Reorder Level</dt><dd class="col-sm-8"><?php echo (int) $product->reorder_level; ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $product->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $product->status ? 'Active' : 'Inactive'; ?></span></dd>
    </dl>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
</div>
