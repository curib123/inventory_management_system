<h2>Users</h2>
<p><a href="<?php echo site_url('users/add'); ?>">Add User</a></p>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users)): foreach ($users as $user): ?>
            <tr>
                <td><?php echo html_escape($user->username); ?></td>
                <td><?php echo html_escape($user->role_name ?: 'N/A'); ?></td>
                <td><?php echo $user->status ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo html_escape($user->created_at); ?></td>
                <td>
                    <a href="<?php echo site_url('users/edit/' . (int) $user->id); ?>">Edit</a>
                    <?php if ((int) $user->id !== (int) $this->session->userdata('user_id')): ?>
                        <?php echo form_open('users/delete/' . (int) $user->id); ?>
                            <button type="submit">Delete</button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
