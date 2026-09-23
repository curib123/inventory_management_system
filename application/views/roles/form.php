<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php echo form_open(current_url()); ?>
    <p><label for="role_name">Role Name</label><br><input type="text" id="role_name" name="role_name" required maxlength="50" value="<?php echo html_escape(set_value('role_name', isset($role) ? $role->role_name : '')); ?>"></p>
    <p><label for="description">Description</label><br><input type="text" id="description" name="description" maxlength="255" value="<?php echo html_escape(set_value('description', isset($role) ? $role->description : '')); ?>"></p>
    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($role) ? $role->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>
    <fieldset>
        <legend>Permissions</legend>
        <?php if (!empty($permissions)): foreach ($permissions as $permission): ?>
            <label>
                <input type="checkbox" name="permissions[]" value="<?php echo (int) $permission->id; ?>" <?php echo in_array((int) $permission->id, $selected_permissions, TRUE) ? 'checked' : ''; ?>>
                <?php echo html_escape($permission->permission_name); ?> (<?php echo html_escape($permission->module_name); ?>)
            </label><br>
        <?php endforeach; else: ?>
            <p>No active permissions found.</p>
        <?php endif; ?>
    </fieldset>
    <p><button type="submit">Save Role</button> <a href="<?php echo site_url('roles'); ?>">Cancel</a></p>
<?php echo form_close(); ?>
