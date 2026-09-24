<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Role information and module-based permissions from the current database design.',
    'modal_icon' => 'bi-shield-lock'
));

$permission_groups = array();

foreach ((array) $permissions as $permission) {
    $module_id = (int) $permission->module_id;

    if (!isset($permission_groups[$module_id])) {
        $permission_groups[$module_id] = array(
            'module_name' => $permission->module_name,
            'module_key' => $permission->module_key,
            'permissions' => array()
        );
    }

    $permission_groups[$module_id]['permissions'][] = $permission;
}
?>

<div class="modal-body">
    <?php if (validation_errors() || !empty($form_error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo validation_errors('', ' '); ?>
            <?php echo !empty($form_error) ? html_escape($form_error) : ''; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-8">
            <label for="role_name" class="form-label">Role Name</label>
            <input
                type="text"
                class="form-control"
                id="role_name"
                name="role_name"
                required
                maxlength="50"
                placeholder="e.g. warehouse_staff"
                value="<?php echo html_escape(set_value('role_name', isset($role) && $role ? $role->role_name : '')); ?>"
                <?php echo $can_edit_role ? '' : 'disabled'; ?>
            >
        </div>

        <div class="col-12 col-md-4">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', isset($role) && $role ? $role->status : 1); ?>
            <select class="form-select" id="status" name="status" <?php echo $can_edit_role ? '' : 'disabled'; ?>>
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea
                class="form-control"
                id="description"
                name="description"
                rows="3"
                maxlength="255"
                placeholder="Enter a short description for this role"
                <?php echo $can_edit_role ? '' : 'disabled'; ?>
            ><?php echo html_escape(set_value('description', isset($role) && $role ? $role->description : '')); ?></textarea>
        </div>
    </div>

    <?php if (!$can_edit_role): ?>
        <div class="alert alert-light border mt-3 mb-0">
            Role information is read-only. Your access only allows permission assignment.
        </div>
    <?php endif; ?>

    <hr class="my-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h3 class="h6 mb-1">Permissions by Module</h3>
            <p class="text-body-secondary small mb-0">
                Permission keys are the exact values used by controllers and navigation guards.
            </p>
        </div>
        <span class="badge text-bg-primary"><?php echo count((array) $permissions); ?> available</span>
    </div>

    <?php if (!$can_manage_permissions): ?>
        <div class="alert alert-warning">
            You can view assigned permissions, but you do not have <code>roles.permissions</code> access to change them.
        </div>
    <?php endif; ?>

    <?php if (!empty($permission_groups)): ?>
        <div class="vstack gap-3">
            <?php foreach ($permission_groups as $group): ?>
                <section class="border rounded-3 overflow-hidden">
                    <div class="bg-body-tertiary border-bottom px-3 py-2">
                        <div class="fw-semibold"><?php echo html_escape($group['module_name']); ?></div>
                        <div class="small text-body-secondary">
                            Module key: <code><?php echo html_escape($group['module_key']); ?></code>
                        </div>
                    </div>

                    <div class="p-3">
                        <div class="row g-2">
                            <?php foreach ($group['permissions'] as $permission): ?>
                                <div class="col-12 col-md-6">
                                    <label
                                        for="permission_<?php echo (int) $permission->id; ?>"
                                        class="form-check border rounded-3 p-3 h-100 d-flex align-items-start gap-2"
                                    >
                                        <input
                                            class="form-check-input flex-shrink-0"
                                            type="checkbox"
                                            name="permissions[]"
                                            value="<?php echo (int) $permission->id; ?>"
                                            id="permission_<?php echo (int) $permission->id; ?>"
                                            <?php echo in_array((int) $permission->id, $selected_permissions, TRUE) ? 'checked' : ''; ?>
                                            <?php echo $can_manage_permissions ? '' : 'disabled'; ?>
                                        >

                                        <span>
                                            <span class="fw-semibold d-block">
                                                <?php echo html_escape($permission->permission_name); ?>
                                            </span>
                                            <code class="small d-block mb-1">
                                                <?php echo html_escape($permission->permission_key); ?>
                                            </code>
                                            <?php if (!empty($permission->description)): ?>
                                                <small class="text-body-secondary">
                                                    <?php echo html_escape($permission->description); ?>
                                                </small>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mb-0">No active module permissions found.</div>
    <?php endif; ?>
</div>

<?php
$submit_label = '';

if ($can_edit_role || $can_manage_permissions) {
    $submit_label = (!$can_edit_role && $can_manage_permissions)
        ? 'Save Permissions'
        : 'Save Role';
}

$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => $submit_label,
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>