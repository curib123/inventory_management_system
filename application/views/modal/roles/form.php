<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Set role information and assign system permissions.',
    'modal_icon' => 'bi-shield-lock'
));
?>
<div class="modal-body">
    <?php $this->load->view('components/modal/messages'); ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-8">
            <label for="role_name" class="form-label">Role Name</label>
            <input type="text" id="role_name" name="role_name" class="form-control" required maxlength="50" value="<?php echo html_escape(set_value('role_name', isset($role) && $role ? $role->role_name : '')); ?>">
        </div>

        <div class="col-12 col-md-4">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', isset($role) && $role ? $role->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3" maxlength="255"><?php echo html_escape(set_value('description', isset($role) && $role ? $role->description : '')); ?></textarea>
        </div>
    </div>

    <fieldset>
        <legend class="h6">Permissions</legend>
        <?php if (!empty($permissions)): ?>
            <div class="row g-2">
                <?php foreach ($permissions as $permission): ?>
                    <div class="col-12 col-md-6">
                        <label class="border rounded-3 p-3 w-100 h-100">
                            <input class="form-check-input me-2" type="checkbox" name="permissions[]" value="<?php echo (int) $permission->id; ?>" <?php echo in_array((int) $permission->id, $selected_permissions, TRUE) ? 'checked' : ''; ?>>
                            <span class="fw-semibold"><?php echo html_escape($permission->permission_name); ?></span>
                            <small class="d-block text-body-secondary ms-4"><?php echo html_escape($permission->module_name); ?></small>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border mb-0">No active permissions found.</div>
        <?php endif; ?>
    </fieldset>
</div>
<?php
$this->load->view('components/modal/footer', array(
    'submit_label' => 'Save Role',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
