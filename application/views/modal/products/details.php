<h2>Product Details</h2>
<dl>
    <dt>ID</dt><dd><?php echo (int) $product->id; ?></dd>
    <dt>Code</dt><dd><?php echo html_escape($product->product_code); ?></dd>
    <dt>Name</dt><dd><?php echo html_escape($product->product_name); ?></dd>
    <dt>Category</dt><dd><?php echo html_escape($product->category_name ?: 'N/A'); ?></dd>
    <dt>Supplier</dt><dd><?php echo html_escape($product->supplier_name ?: 'N/A'); ?></dd>
    <dt>Unit</dt><dd><?php echo html_escape($product->unit); ?></dd>
    <dt>Stock</dt><dd><?php echo (int) $product->stock; ?></dd>
    <dt>Cost Price</dt><dd><?php echo number_format((float) $product->cost_price, 2); ?></dd>
    <dt>Selling Price</dt><dd><?php echo number_format((float) $product->selling_price, 2); ?></dd>
    <dt>Reorder Level</dt><dd><?php echo (int) $product->reorder_level; ?></dd>
    <dt>Status</dt><dd><?php echo $product->status ? 'Active' : 'Inactive'; ?></dd>
</dl>
<button type="button" data-modal-close>Close</button>
