<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Role',
    'modal_subtitle' => 'This action permanently removes the selected role.',
    'modal_icon' => 'bi-trash3'
));
?>

<div class="modal-body">
    <p>Role: <strong><?php echo html_escape($role->role_name); ?></strong></p>

    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0"><?php echo html_escape($delete_error); ?></div>
    <?php else: ?>
        <div class="alert alert-danger mb-0">Are you sure you want to delete this role? This action cannot be undone.</div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Cancel',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Role',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>