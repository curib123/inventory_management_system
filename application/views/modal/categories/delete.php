<h2>Delete Category</h2>
<p>Category: <?php echo html_escape($category->category_name); ?></p>
<?php if (!empty($delete_error)): ?>
    <p><?php echo html_escape($delete_error); ?></p>
    <button type="button" data-modal-close>Close</button>
<?php else: ?>
    <p>Are you sure you want to delete this category?</p>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
        <button type="submit">Delete Category</button>
        <button type="button" data-modal-close>Cancel</button>
    <?php echo form_close(); ?>
<?php endif; ?>
