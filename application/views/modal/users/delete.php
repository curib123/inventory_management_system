<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete User?',
    'modal_icon' => 'bi-trash3',
    'modal_variant' => 'danger'
));
?>

<div class="app-modal-body">
    <div class="app-confirm-entity mb-3">
        <div class="app-confirm-entity-label">User</div>
        <div class="app-confirm-entity-value"><?php echo html_escape($user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')'); ?></div>
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
            'confirmation_message' => 'The user will lose access to the system.'
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Cancel',
    'submit_label' => !empty($delete_error) ? '' : 'Delete User',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
