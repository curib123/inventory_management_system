<h2><?php echo $page_title; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open(); ?>
    <div class="form-group">
        <label for="role_name">Role Name</label>
        <input type="text" id="role_name" name="role_name" maxlength="50" required value="<?php echo set_value('role_name', isset($role->role_name) ? $role->role_name : ''); ?>">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <input type="text" id="description" name="description" maxlength="255" value="<?php echo set_value('description', isset($role->description) ? $role->description : ''); ?>">
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="1" <?php echo set_select('status', '1', !isset($role) || $role->status == 1); ?>>Active</option>
            <option value="0" <?php echo set_select('status', '0', isset($role) && $role->status == 0); ?>>Inactive</option>
        </select>
    </div>

    <fieldset>
        <legend>Permissions</legend>
        <?php if (!empty($permissions)): foreach ($permissions as $permission): ?>
            <label>
                <input type="checkbox" name="permissions[]" value="<?php echo $permission->id; ?>" <?php echo in_array((int) $permission->id, $selected_permissions, TRUE) ? 'checked' : ''; ?>>
                <?php echo html_escape($permission->permission_name); ?>
                (<?php echo html_escape($permission->module_name); ?>)
            </label>
        <?php endforeach; else: ?>
            <p>No active permissions found.</p>
        <?php endif; ?>
    </fieldset>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save Role</button>
        <a class="btn" href="<?php echo site_url('roles'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
