<div class="modal-header">
    <h2 class="modal-title fs-5">Delete Supplier</h2>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>

<div class="modal-body">
    <p>Supplier: <strong><?php echo html_escape($supplier->supplier_name); ?></strong></p>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0"><?php echo html_escape($delete_error); ?></div>
    <?php else: ?>
        <div class="alert alert-danger mb-0">Are you sure you want to delete this supplier?</div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-modal-close><?php echo !empty($delete_error) ? 'Close' : 'Cancel'; ?></button>
    <?php if (empty($delete_error)): ?>
        <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
            <button type="submit" class="btn btn-danger">Delete Supplier</button>
        <?php echo form_close(); ?>
    <?php endif; ?>
</div>
