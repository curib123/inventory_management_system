<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Define the role and select the permissions it can use.',
    'modal_icon' => 'bi-shield-lock'
));
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
                placeholder="e.g. Administrator"
                value="<?php echo html_escape(set_value('role_name', isset($role) && $role ? $role->role_name : '')); ?>"
            >
        </div>

        <div class="col-12 col-md-4">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', isset($role) && $role ? $role->status : 1); ?>
            <select class="form-select" id="status" name="status">
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
            ><?php echo html_escape(set_value('description', isset($role) && $role ? $role->description : '')); ?></textarea>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h3 class="h6 mb-1">Permissions</h3>
            <p class="text-body-secondary small mb-0">Select the permissions assigned to this role.</p>
        </div>
        <?php if (!empty($permissions)): ?>
            <span class="badge text-bg-primary"><?php echo count($permissions); ?> available</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($permissions)): ?>
        <div class="row g-2">
            <?php foreach ($permissions as $permission): ?>
                <div class="col-12 col-md-6">
                    <label for="permission_<?php echo (int) $permission->id; ?>" class="form-check border rounded-3 p-3 h-100 d-flex gap-2">
                        <input
                            class="form-check-input flex-shrink-0"
                            type="checkbox"
                            name="permissions[]"
                            value="<?php echo (int) $permission->id; ?>"
                            id="permission_<?php echo (int) $permission->id; ?>"
                            <?php echo in_array((int) $permission->id, $selected_permissions, TRUE) ? 'checked' : ''; ?>
                        >
                        <span>
                            <span class="fw-semibold d-block"><?php echo html_escape($permission->permission_name); ?></span>
                            <small class="text-body-secondary"><?php echo html_escape($permission->module_name); ?></small>
                        </span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border mb-0">No active permissions found.</div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => 'Save Role',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>