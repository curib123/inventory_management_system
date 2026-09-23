<dialog id="product-details-<?php echo (int) $product->id; ?>">
    <h3>Product Details</h3>
    <p>Code: <?php echo html_escape($product->product_code); ?></p>
    <p>Name: <?php echo html_escape($product->product_name); ?></p>
    <p>Supplier: <?php echo html_escape($product->supplier_name ?: 'N/A'); ?></p>
    <p>Stock: <?php echo (int) $product->stock; ?></p>
    <p>Unit: <?php echo html_escape($product->unit); ?></p>
    <p>Cost Price: <?php echo html_escape($product->cost_price); ?></p>
    <p>Selling Price: <?php echo html_escape($product->selling_price); ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
