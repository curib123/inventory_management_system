<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Product',
    'modal_subtitle' => 'This action permanently removes the selected product.',
    'modal_icon' => 'bi-trash3'
));
?>

<div class="modal-body">
    <p>Product: <strong><?php echo html_escape($product->product_name); ?></strong></p>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0"><?php echo html_escape($delete_error); ?></div>
    <?php else: ?>
        <div class="alert alert-danger mb-0">Are you sure you want to delete this product?</div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Cancel',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Product',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>