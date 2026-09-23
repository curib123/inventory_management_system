<dialog id="role-delete-<?php echo (int) $role->id; ?>">
    <h3>Delete Role</h3>
    <p>Delete <?php echo html_escape($role->role_name); ?>?</p>
    <a href="<?php echo site_url('roles/delete/' . $role->id); ?>">Delete</a>
    <button type="button" onclick="this.closest('dialog').close();">Cancel</button>
</dialog>
