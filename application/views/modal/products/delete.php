<h2>Delete Product</h2>
<p>Product: <?php echo html_escape($product->product_name); ?></p>
<?php if (!empty($delete_error)): ?>
    <p><?php echo html_escape($delete_error); ?></p>
    <button type="button" data-modal-close>Close</button>
<?php else: ?>
    <p>Are you sure you want to delete this product?</p>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
        <button type="submit">Delete Product</button>
        <button type="button" data-modal-close>Cancel</button>
    <?php echo form_close(); ?>
<?php endif; ?>
