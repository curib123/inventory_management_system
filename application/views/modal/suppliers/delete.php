<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Supplier',
    'modal_subtitle' => 'Confirm this permanent action before continuing.',
    'modal_icon' => 'bi-trash3',
    'modal_variant' => 'danger',
    'modal_eyebrow' => 'Destructive action'
));
?>

<div class="modal-body">
    <div class="app-confirm-entity mb-3">
        <div class="app-confirm-entity-label">Supplier</div>
        <div class="app-confirm-entity-value"><?php echo html_escape($supplier->supplier_name); ?></div>
    </div>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bi bi-shield-exclamation me-1"></i>
                Deletion is not available
            </div>
            <div><?php echo html_escape($delete_error); ?></div>
        </div>
    <?php else: ?>
        <?php
        $this->load->view('components/modal/confirmation', array(
            'confirmation_variant' => 'danger',
            'confirmation_icon' => 'bi-trash3',
            'confirmation_title' => 'Delete this supplier?',
            'confirmation_message' => 'Deleting removes this supplier from future supplier and Stock In selection.',
            'confirmation_items' => array(
                'This action cannot be undone.',
                'Suppliers referenced by products or transactions may be protected from deletion.',
                'Confirm that this is the correct supplier before continuing.'
            )
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Keep Supplier',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Supplier',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
