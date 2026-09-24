<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete User',
    'modal_subtitle' => 'Confirm this permanent action before continuing.',
    'modal_icon' => 'bi-trash3'
));
?>

<div class="modal-body">
    <div class="app-confirm-entity mb-3">
        <div class="app-confirm-entity-label">User</div>
        <div class="app-confirm-entity-value"><?php echo html_escape($user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')'); ?></div>
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
            'confirmation_title' => 'Delete User?',
            'confirmation_message' => 'Deleting permanently removes this user account from user management.',
            'confirmation_items' => array(
                'This action cannot be undone.',
                'The user will no longer be able to access the system.',
                'Historical records should remain attributable where the database keeps user references.'
            )
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Keep User',
    'submit_label' => !empty($delete_error) ? '' : 'Delete User',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
