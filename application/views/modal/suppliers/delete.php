<h2>Delete Supplier</h2>
<p>Supplier: <?php echo html_escape($supplier->supplier_name); ?></p>
<?php if (!empty($delete_error)): ?>
    <p><?php echo html_escape($delete_error); ?></p>
    <button type="button" data-modal-close>Close</button>
<?php else: ?>
    <p>Are you sure you want to delete this supplier?</p>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
        <button type="submit">Delete Supplier</button>
        <button type="button" data-modal-close>Cancel</button>
    <?php echo form_close(); ?>
<?php endif; ?>
