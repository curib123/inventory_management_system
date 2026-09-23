<h2>User Details</h2>
<dl>
    <dt>ID</dt><dd><?php echo (int) $user->id; ?></dd>
    <dt>First Name</dt><dd><?php echo html_escape($user->first_name); ?></dd>
    <dt>Middle Name</dt><dd><?php echo html_escape($user->middle_name ?: 'N/A'); ?></dd>
    <dt>Last Name</dt><dd><?php echo html_escape($user->last_name); ?></dd>
    <dt>Username</dt><dd><?php echo html_escape($user->username); ?></dd>
    <dt>Role</dt><dd><?php echo html_escape($user->role_name ?: 'N/A'); ?></dd>
    <dt>Status</dt><dd><?php echo $user->status ? 'Active' : 'Inactive'; ?></dd>
    <dt>Created</dt><dd><?php echo html_escape($user->created_at); ?></dd>
    <dt>Updated</dt><dd><?php echo html_escape($user->updated_at); ?></dd>
</dl>
<button type="button" data-modal-close>Close</button>
