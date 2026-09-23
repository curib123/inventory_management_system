<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php if (!empty($form_error)): ?><p><?php echo html_escape($form_error); ?></p><?php endif; ?>

<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <p><label for="first_name">First Name</label><br><input type="text" id="first_name" name="first_name" required maxlength="100" value="<?php echo html_escape(set_value('first_name', isset($user) && $user ? $user->first_name : '')); ?>"></p>
    <p><label for="middle_name">Middle Name</label><br><input type="text" id="middle_name" name="middle_name" maxlength="100" value="<?php echo html_escape(set_value('middle_name', isset($user) && $user ? $user->middle_name : '')); ?>"></p>
    <p><label for="last_name">Last Name</label><br><input type="text" id="last_name" name="last_name" required maxlength="100" value="<?php echo html_escape(set_value('last_name', isset($user) && $user ? $user->last_name : '')); ?>"></p>
    <p><label for="username">Username</label><br><input type="text" id="username" name="username" required minlength="3" maxlength="50" value="<?php echo html_escape(set_value('username', isset($user) && $user ? $user->username : '')); ?>"></p>
    <p><label for="password">Password<?php echo isset($user) && $user ? ' (leave blank to keep current password)' : ''; ?></label><br><input type="password" id="password" name="password" <?php echo isset($user) && $user ? '' : 'required'; ?> minlength="8" maxlength="255" autocomplete="new-password"></p>
    <p>
        <label for="role_id">Role</label><br>
        <?php $selected_role = set_value('role_id', isset($user) && $user ? $user->role_id : ''); ?>
        <select id="role_id" name="role_id" required>
            <option value="">Select Role</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo (int) $role->id; ?>" <?php echo ((string) $selected_role === (string) $role->id) ? 'selected' : ''; ?>><?php echo html_escape($role->role_name); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($user) && $user ? $user->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>
    <button type="submit">Save User</button>
    <button type="button" data-modal-close>Cancel</button>
<?php echo form_close(); ?>
