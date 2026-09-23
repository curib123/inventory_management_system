<h2>Category Details</h2>
<dl>
    <dt>ID</dt><dd><?php echo (int) $category->id; ?></dd>
    <dt>Name</dt><dd><?php echo html_escape($category->category_name); ?></dd>
    <dt>Status</dt><dd><?php echo $category->status ? 'Active' : 'Inactive'; ?></dd>
    <dt>Products</dt><dd><?php echo (int) $product_count; ?></dd>
</dl>
<button type="button" data-modal-close>Close</button>
