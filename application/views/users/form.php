<h2><?php echo html_escape($page_title); ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open(current_url()); ?>
    <p>
        <label for="username">Username</label><br>
        <input type="text" id="username" name="username" required minlength="3" maxlength="50" value="<?php echo html_escape(set_value('username', isset($user) ? $user->username : '')); ?>">
    </p>

    <p>
        <label for="password">Password<?php echo isset($user) ? ' (leave blank to keep current password)' : ''; ?></label><br>
        <input type="password" id="password" name="password" <?php echo isset($user) ? '' : 'required'; ?> minlength="8" maxlength="255" autocomplete="new-password">
    </p>

    <p>
        <label for="role_id">Role</label><br>
        <select id="role_id" name="role_id" required>
            <option value="">Select Role</option>
            <?php foreach ($roles as $role): ?>
                <?php $selected_role = set_value('role_id', isset($user) ? $user->role_id : ''); ?>
                <option value="<?php echo (int) $role->id; ?>" <?php echo ((string) $selected_role === (string) $role->id) ? 'selected' : ''; ?>><?php echo html_escape($role->role_name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($user) ? $user->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>

    <button type="submit">Save User</button>
    <a href="<?php echo site_url('users'); ?>">Cancel</a>
<?php echo form_close(); ?>
