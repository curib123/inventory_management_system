<h2>Roles and Permissions</h2>
<p><a href="<?php echo site_url('roles/add'); ?>">Add Role</a></p>

<table>
    <thead>
        <tr><th>Role</th><th>Description</th><th>Status</th><th>Users</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (!empty($roles)): foreach ($roles as $role): ?>
            <tr>
                <td><?php echo html_escape($role->role_name); ?></td>
                <td><?php echo html_escape($role->description); ?></td>
                <td><?php echo $role->status ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo (int) $role->user_count; ?></td>
                <td>
                    <a href="<?php echo site_url('roles/edit/' . (int) $role->id); ?>">Edit</a>
                    <?php if ((int) $role->user_count === 0): ?>
                        <?php echo form_open('roles/delete/' . (int) $role->id); ?>
                            <button type="submit">Delete</button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No roles found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
