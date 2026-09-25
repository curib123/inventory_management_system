<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Delete Role',
    'modal_subtitle' => 'Confirm this permanent action before continuing.',
    'modal_icon' => 'bi-trash3'
));
?>
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
            'confirmation_title' => 'Delete Role? ' . html_escape($role->role_name),
            'confirmation_message' => 'Deleting permanently removes this role and its permission assignment.',
            'confirmation_items' => array(
                'This action cannot be undone.',
                'Roles assigned to users are protected and must be unassigned before deletion.'
            )
        ));
        ?>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => !empty($delete_error) ? 'Close' : 'Keep Role',
    'submit_label' => !empty($delete_error) ? '' : 'Delete Role',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-trash3'
));
?>
<?php echo form_close(); ?>
