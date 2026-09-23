<dialog id="product-delete-<?php echo (int) $product->id; ?>">
    <h3>Delete Product</h3>
    <p>Are you sure you wan to delete this  <?php echo html_escape($product->product_name); ?> product?</p>
    <a href="<?php echo site_url('products/delete/' . $product->id); ?>">Delete</a>
    <button type="button" onclick="this.closest('dialog').close();">Cancel</button>
</dialog>
