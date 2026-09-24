<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Role Details',
    'modal_subtitle' => 'Role information and permissions from the module-based authorization model.',
    'modal_icon' => 'bi-person-badge'
));

$permission_groups = array();

foreach ((array) $permissions as $permission) {
    $module_name = $permission->module_name;

    if (!isset($permission_groups[$module_name])) {
        $permission_groups[$module_name] = array();
    }

    $permission_groups[$module_name][] = $permission;
}
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-4">
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

    <h3 class="h6 mb-3">Assigned Permissions</h3>

    <?php if (!empty($permission_groups)): ?>
        <div class="vstack gap-3">
            <?php foreach ($permission_groups as $module_name => $module_permissions): ?>
                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-2"><?php echo html_escape($module_name); ?></div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($module_permissions as $permission): ?>
                            <span class="badge text-bg-light border">
                                <?php echo html_escape($permission->permission_name); ?>
                                <span class="text-body-secondary ms-1">
                                    <?php echo html_escape($permission->permission_key); ?>
                                </span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mb-0">No active permissions are assigned to this role.</div>
    <?php endif; ?>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>