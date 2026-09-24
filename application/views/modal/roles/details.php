<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Role Details',
    'modal_subtitle' => 'Role information, status, and current user count.',
    'modal_icon' => 'bi-person-badge'
));
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">Role ID</dt><dd class="col-sm-8"><?php echo (int) $role->id; ?></dd>
        <dt class="col-sm-4">Role Name</dt><dd class="col-sm-8"><?php echo html_escape($role->role_name); ?></dd>
        <dt class="col-sm-4">Description</dt><dd class="col-sm-8"><?php echo html_escape($role->description ?: 'No description provided.'); ?></dd>
        <dt class="col-sm-4">Users</dt><dd class="col-sm-8"><?php echo (int) $user_count; ?></dd>
        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8">
            <span class="badge <?php echo $role->status ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                <?php echo $role->status ? 'Active' : 'Inactive'; ?>
            </span>
        </dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>