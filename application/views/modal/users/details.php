<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'User Details',
    'modal_subtitle' => 'Account identity, role, status, and timestamps.',
    'modal_icon' => 'bi-person-vcard'
));
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $user->id; ?></dd>
        <dt class="col-sm-4">First Name</dt><dd class="col-sm-8"><?php echo html_escape($user->first_name); ?></dd>
        <dt class="col-sm-4">Middle Name</dt><dd class="col-sm-8"><?php echo html_escape($user->middle_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Last Name</dt><dd class="col-sm-8"><?php echo html_escape($user->last_name); ?></dd>
        <dt class="col-sm-4">Username</dt><dd class="col-sm-8"><?php echo html_escape($user->username); ?></dd>
        <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><?php echo html_escape($user->role_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8"><span class="badge <?php echo $user->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $user->status ? 'Active' : 'Inactive'; ?></span></dd>
        <dt class="col-sm-4">Created</dt><dd class="col-sm-8"><?php echo html_escape($user->created_at); ?></dd>
        <dt class="col-sm-4">Updated</dt><dd class="col-sm-8"><?php echo html_escape($user->updated_at); ?></dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>