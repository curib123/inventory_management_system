<h2>Role Details</h2>
<dl>
    <dt>ID</dt><dd><?php echo (int) $role->id; ?></dd>
    <dt>Name</dt><dd><?php echo html_escape($role->role_name); ?></dd>
    <dt>Description</dt><dd><?php echo html_escape($role->description ?: 'N/A'); ?></dd>
    <dt>Status</dt><dd><?php echo $role->status ? 'Active' : 'Inactive'; ?></dd>
    <dt>Users</dt><dd><?php echo (int) $user_count; ?></dd>
</dl>
<button type="button" data-modal-close>Close</button>
