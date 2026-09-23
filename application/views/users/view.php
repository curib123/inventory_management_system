<h2>View User</h2>

<dl>
    <dt>ID</dt>
    <dd><?php echo (int) $user->id; ?></dd>

    <dt>First Name</dt>
    <dd><?php echo html_escape($user->first_name); ?></dd>

    <dt>Middle Name</dt>
    <dd><?php echo html_escape($user->middle_name ?: 'N/A'); ?></dd>

    <dt>Last Name</dt>
    <dd><?php echo html_escape($user->last_name); ?></dd>

    <dt>Username</dt>
    <dd><?php echo html_escape($user->username); ?></dd>

    <dt>Role</dt>
    <dd><?php echo html_escape($user->role_name ?: 'N/A'); ?></dd>

    <dt>Status</dt>
    <dd><?php echo $user->status ? 'Active' : 'Inactive'; ?></dd>

    <dt>Created</dt>
    <dd><?php echo html_escape($user->created_at); ?></dd>

    <dt>Updated</dt>
    <dd><?php echo html_escape($user->updated_at); ?></dd>
</dl>

<p>
    <a href="<?php echo site_url('users/edit/' . (int) $user->id); ?>">Edit User</a>
    |
    <a href="<?php echo site_url('users'); ?>">Back to Users</a>
</p>

<?php if ((int) $user->id !== (int) $this->session->userdata('user_id')): ?>
    <?php echo form_open('users/delete/' . (int) $user->id); ?>
        <button type="submit">Delete User</button>
    <?php echo form_close(); ?>
<?php endif; ?>
