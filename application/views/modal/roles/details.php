<dialog id="role-details-<?php echo (int) $role->id; ?>">
    <h3>Role Details</h3>
    <p>Name: <?php echo html_escape($role->role_name); ?></p>
    <p>Description: <?php echo html_escape($role->description); ?></p>
    <p>Status: <?php echo $role->status ? 'Active' : 'Inactive'; ?></p>
    <p>Users: <?php echo (int) $role->user_count; ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
