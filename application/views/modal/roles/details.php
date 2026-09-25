<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Role Details',
    'modal_subtitle' => 'Role identity, usage, status, and assigned permissions.',
    'modal_icon' => 'bi-person-badge',
    'modal_eyebrow' => 'Access control'
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
    <div class="app-role-summary">
        <div class="app-role-summary-main">
            <div class="app-role-summary-label">Role</div>
            <div class="app-role-summary-name"><?php echo html_escape($role->role_name); ?></div>
            <div class="app-role-summary-description">
                <?php echo html_escape($role->description ?: 'No description provided.'); ?>
            </div>
        </div>

        <div class="app-role-summary-meta">
            <div class="app-role-stat">
                <span class="app-role-stat-label">Users assigned</span>
                <strong><?php echo (int) $user_count; ?></strong>
            </div>

            <div class="app-role-stat">
                <span class="app-role-stat-label">Status</span>
                <span class="badge rounded-pill <?php echo $role->status ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <i class="bi <?php echo $role->status ? 'bi-check-circle' : 'bi-pause-circle'; ?> me-1"></i>
                    <?php echo $role->status ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="app-modal-section-header">
        <div>
            <h3>Assigned permissions</h3>
            <p>Permissions are grouped by module for easier review.</p>
        </div>
        <span class="badge text-bg-primary rounded-pill">
            <?php echo count((array) $permissions); ?> assigned
        </span>
    </div>

    <?php if (!empty($permission_groups)): ?>
        <div class="vstack gap-3">
            <?php foreach ($permission_groups as $module_name => $module_permissions): ?>
                <section class="app-permission-group">
                    <div class="app-permission-group-header">
                        <div class="fw-semibold"><?php echo html_escape($module_name); ?></div>
                        <span class="badge text-bg-light border">
                            <?php echo count($module_permissions); ?>
                        </span>
                    </div>

                    <div class="app-permission-group-body">
                        <div class="app-role-permission-list">
                            <?php foreach ($module_permissions as $permission): ?>
                                <span class="app-role-permission-pill">
                                    <i class="bi bi-check2" aria-hidden="true"></i>
                                    <?php echo html_escape($permission->permission_name); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="app-empty-state app-modal-empty-state">
            <i class="bi bi-shield-slash"></i>
            <div class="fw-semibold">No permissions assigned</div>
            <div class="small">This role currently has no active permissions.</div>
        </div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Close',
    'close_icon' => 'bi-x-lg'
));
?>
