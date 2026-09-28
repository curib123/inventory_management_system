<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Supplier?',
    'modal_icon' => 'bi-trash3',
    'modal_variant' => 'danger'
));
?>

<div class="app-modal-body">
    <div class="app-confirm-entity mb-3">
        <div class="app-confirm-entity-label">Supplier</div>
        <div class="app-confirm-entity-value"><?php echo html_escape($supplier->supplier_name); ?></div>
    </div>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0" role="alert">
            <?php echo html_escape($delete_error); ?>
        </div>
    <?php else: ?>
        <?php
        $this->load->view('components/modal/confirmation', array(
            'confirmation_variant' => 'danger',
            'confirmation_icon' => 'bi-trash3',
            'confirmation_title' => 'This cannot be undone.',
            'confirmation_message' => 'Suppliers used by products or transactions cannot be deleted.'
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Cancel',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Supplier',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
