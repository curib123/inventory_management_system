<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Category',
    'modal_subtitle' => 'Confirm this permanent action before continuing.',
    'modal_icon' => 'bi-trash3'
));
?>

<div class="modal-body">
    <div class="app-confirm-entity mb-3">
        <div class="app-confirm-entity-label">Category</div>
        <div class="app-confirm-entity-value"><?php echo html_escape($category->category_name); ?></div>
    </div>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0" role="alert">
            <div class="fw-semibold mb-1">Deletion is not available</div>
            <div><?php echo html_escape($delete_error); ?></div>
        </div>
    <?php else: ?>
        <?php
        $this->load->view('components/modal/confirmation', array(
            'confirmation_variant' => 'danger',
            'confirmation_icon' => 'bi-trash3',
            'confirmation_title' => 'Delete Category?',
            'confirmation_message' => 'Deleting removes this category from future inventory use.',
            'confirmation_items' => array(
                'This action cannot be undone.',
                'Categories still used by products are protected from deletion.'
            )
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Keep Category',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Category',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
