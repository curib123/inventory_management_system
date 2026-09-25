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

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <!-- Header -->
    <div class="card-header bg-white border-bottom px-4 py-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small mb-1">Role Details</div>
                <h4 class="mb-0 fw-semibold">
                    <?php echo html_escape($role->role_name); ?>
                </h4>
            </div>

            <span class="badge rounded-sm px-3 py-2 <?php echo $role->status ? 'text-bg-primary' : 'text-bg-secondary'; ?>">
                <i class="bi <?php echo $role->status ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'; ?> me-1"></i>
                <?php echo $role->status ? 'Active' : 'Inactive'; ?>
            </span>
        </div>
    </div>
    <!-- Details -->
    <div class="card-body p-2">
        <div class="row g-3">

            <!-- Users Assigned -->
            <div class="col-md-5">
                <div class="bg-light rounded-3 p-3 ">
                    <div class="text-muted small mb-1">
                        <i class="bi bi-people me-1"></i>
                        Users Assigned
                    </div>
                    <div class="fw-semibold">
                        <?php echo (int) $user_count; ?>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="col-6">
                <div class="bg-light rounded-3 p-3">
                    <div class="text-muted small mb-2">
                        <i class="bi bi-text-paragraph me-1"></i>
                        Description
                    </div>

                    <div class="text-body">
                        <?php echo html_escape($role->description ?: 'No description provided.'); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <h3 class="h6 mb-3">Assigned Permissions</h3>

    <?php if (!empty($permission_groups)): ?>
        <div class="vstack gap-3">
            <?php foreach ($permission_groups as $module_name => $module_permissions): ?>
                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-2"><?php echo html_escape($module_name); ?></div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($module_permissions as $permission): ?>
                            <span class="app-choice-chips app-choice-chip-fill   p-3 ">
                                <?php echo html_escape($permission->permission_name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border-none mb-0">No active permissions are assigned to this role.</div>
    <?php endif; ?>
</div>